<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Metric;
use App\Models\MonthlyResult;
use App\Models\EvidenceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyResultController extends Controller
{
    /**
     * 月次入力グリッド表示
     */
    public function index(Request $request)
    {
        // リクエストから年度IDを取得、なければアクティブな年度を取得
        if ($request->has('fiscal_year_id')) {
            $fiscalYear = FiscalYear::find($request->fiscal_year_id);
        } else {
            $fiscalYear = FiscalYear::getActive();
        }
        
        if (!$fiscalYear) {
            return redirect()->route('dashboard')
                ->with('error', '年度が設定されていません。');
        }

        // 指標をカテゴリごとに取得（指定順序で）
        $categoryOrder = ['全体', '新規開業', 'ランニング', '商品卸', 'その他売上', 'その他指標'];
        
        $allMetrics = Metric::with(['category.parent'])
            ->orderBy('sort_order')
            ->get();
        
        // ダッシュボードと同じ表示名マッピングを適用
        $this->applyMetricDisplayNameMapping($allMetrics);
        
        $metrics = [];
        foreach ($categoryOrder as $categoryName) {
            $categoryMetrics = $allMetrics->filter(function ($metric) use ($categoryName) {
                $category = $metric->category;
                $rootCategory = $category->parent ?? $category;
                return $rootCategory->name === $categoryName;
            });
            
            if ($categoryMetrics->count() > 0) {
                $metrics[$categoryName] = $categoryMetrics->values();
            }
        }

        // 月次データを取得
        $monthlyResults = MonthlyResult::where('fiscal_year_id', $fiscalYear->id)
            ->get()
            ->keyBy(function ($result) {
                return $result->metric_id . '_' . $result->target_month->format('Y-m-d');
            });

        $fiscalYears = FiscalYear::orderBy('start_date')->get();

        return view('monthly-results.index', [
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => $fiscalYears,
            'metrics' => $metrics,
            'monthlyResults' => $monthlyResults,
        ]);
    }

    /**
     * 月次実績値を更新
     */
    public function update(Request $request)
    {
        $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'metric_id' => 'required|exists:metrics,id',
            'target_month' => 'required|date',
            'value' => 'nullable|numeric|between:-9999999999,9999999999',
            'comment' => 'nullable|string|max:1000',
        ]);

        $fiscalYear = FiscalYear::findOrFail($request->fiscal_year_id);
        $targetMonth = Carbon::parse($request->target_month);
        $start = Carbon::parse($fiscalYear->start_date)->startOfMonth();
        $end = Carbon::parse($fiscalYear->end_date)->endOfMonth();
        if ($targetMonth->lt($start) || $targetMonth->gt($end)) {
            return response()->json(['success' => false, 'message' => '対象月が年度の範囲外です。'], 422);
        }

        $result = MonthlyResult::updateOrCreate(
            [
                'fiscal_year_id' => $request->fiscal_year_id,
                'metric_id' => $request->metric_id,
                'target_month' => $request->target_month,
            ],
            [
                'value' => $request->value,
                'comment' => $request->comment,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => '実績値を更新しました。',
            'result' => $result,
        ]);
    }

    /**
     * 月次実績レコードを取得または新規作成（値を上書きしない）
     * 詳細モーダルを開く際に使用する。既存レコードの value/comment は変更しない。
     */
    public function findOrCreate(Request $request)
    {
        $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'metric_id'      => 'required|exists:metrics,id',
            'target_month'   => 'required|date',
        ]);

        $result = MonthlyResult::firstOrCreate([
            'fiscal_year_id' => $request->fiscal_year_id,
            'metric_id'      => $request->metric_id,
            'target_month'   => $request->target_month,
        ]);

        return response()->json([
            'success' => true,
            'result'  => $result,
        ]);
    }

    /**
     * 根拠資料をアップロード
     */
    public function uploadEvidence(Request $request)
    {
        $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'metric_id' => 'required|exists:metrics,id',
            'target_month' => 'required|date',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,gif,xlsx,xls,csv,doc,docx,txt',
        ]);

        $file = $request->file('file');
        $path = $file->store('evidence', 'public');

        $result = MonthlyResult::updateOrCreate(
            [
                'fiscal_year_id' => $request->fiscal_year_id,
                'metric_id' => $request->metric_id,
                'target_month' => $request->target_month,
            ],
            [
                'evidence_file_path' => $path,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'ファイルをアップロードしました。',
            'file_path' => $path,
            'file_url' => asset('storage/' . str_replace('public/', '', $path)),
        ]);
    }

    /**
     * 根拠資料をダウンロード
     */
    public function downloadEvidence($id)
    {
        $result = MonthlyResult::findOrFail($id);
        
        if (!$result->evidence_file_path || !Storage::disk('public')->exists($result->evidence_file_path)) {
            abort(404, 'ファイルが見つかりません。');
        }

        return Storage::disk('public')->download($result->evidence_file_path);
    }

    /**
     * 根拠実績詳細を取得
     */
    public function getDetails($id)
    {
        $result = MonthlyResult::findOrFail($id);
        $details = $result->evidenceDetails;
        
        return response()->json([
            'success' => true,
            'details' => $details,
            'comment' => $result->comment,
        ]);
    }

    /**
     * 根拠実績詳細を保存
     * 
     * 注意: このメソッドは詳細データのみを保存し、MonthlyResultのvalueは更新しません。
     * 詳細の合計値と入力ページの表示値は独立して管理され、一致しなくても問題ありません。
     * これにより、詳細が入力できない項目でも入力ページに値を入力でき、
     * 詳細の内容と入力表の合計値が合っているか目視で確認できる利点があります。
     */
    public function saveDetails(Request $request, $id)
    {
        $result = MonthlyResult::findOrFail($id);
        
        $request->validate([
            'details' => 'nullable|array|max:100',
            'details.*.detail' => 'required|string|max:500',
            'details.*.amount' => 'required|numeric|between:-9999999999,9999999999',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        // 既存の詳細を削除
        $result->evidenceDetails()->delete();
        
        // 新しい詳細を保存（詳細がある場合のみ）
        $details = $request->details ?? [];
        $sortOrder = 0;
        foreach ($details as $detailData) {
            EvidenceDetail::create([
                'monthly_result_id' => $result->id,
                'detail' => $detailData['detail'],
                'amount' => $detailData['amount'],
                'sort_order' => $sortOrder++,
            ]);
        }
        
        // 備考を保存
        $result->comment = $request->comment;
        $result->save();
        
        // 注意: MonthlyResultのvalueは更新しません
        // 詳細の合計値と入力ページの表示値は独立して管理されます
        
        return response()->json([
            'success' => true,
            'message' => '詳細を保存しました。',
        ]);
    }

    /**
     * 指標の月次データを取得（グラフ用）
     */
    public function getMetricChartData(Request $request, $metricId)
    {
        $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ]);

        $fiscalYear = FiscalYear::findOrFail($request->fiscal_year_id);
        $metric = Metric::findOrFail($metricId);

        // 年度の全月を生成（7月〜6月）
        $currentMonth = $fiscalYear->start_date->copy()->startOfMonth();
        $endMonth = $fiscalYear->end_date->copy()->endOfMonth();
        $allFiscalMonths = [];
        
        while ($currentMonth <= $endMonth) {
            $allFiscalMonths[] = $currentMonth->copy();
            $currentMonth->addMonth();
        }

        // 月次データを取得
        $monthlyResults = MonthlyResult::where('fiscal_year_id', $fiscalYear->id)
            ->where('metric_id', $metricId)
            ->get()
            ->keyBy(function ($result) {
                return $result->target_month->format('Y-m-d');
            });

        // グラフ用データを準備
        $labels = [];
        $data = [];

        foreach ($allFiscalMonths as $month) {
            $monthStr = $month->format('Y-m-d');
            $labels[] = $month->format('n月');
            
            $result = $monthlyResults->get($monthStr);
            $data[] = $result && $result->value !== null ? (float)$result->value : null;
        }

        return response()->json([
            'success' => true,
            'metric' => [
                'id' => $metric->id,
                'name' => $metric->name,
                'type' => $metric->type,
                'unit' => $metric->unit,
            ],
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * CSVダウンロード（シンプル形式）
     */
    public function downloadCsvSimple(Request $request)
    {
        $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'category' => 'required|string',
        ]);

        $fiscalYear = FiscalYear::findOrFail($request->fiscal_year_id);
        
        // カテゴリに属する指標を取得
        $category = \App\Models\Category::where('name', $request->category)
            ->whereNull('parent_id')
            ->first();
        
        if (!$category) {
            return response()->json(['error' => 'カテゴリが見つかりません'], 404);
        }

        $metrics = Metric::whereHas('category', function($query) use ($category) {
            $query->where('id', $category->id)
                  ->orWhere('parent_id', $category->id);
        })->get();

        // 年度の全月を取得
        $months = [];
        $current = Carbon::parse($fiscalYear->start_date)->startOfMonth();
        $end = Carbon::parse($fiscalYear->end_date)->endOfMonth();
        
        while ($current <= $end) {
            $months[] = $current->format('Y-m-01');
            $current->addMonth();
        }

        // CSVデータを生成
        $csvData = [];
        $csvData[] = ['指標名', '年月', '値'];

        foreach ($metrics as $metric) {
            foreach ($months as $month) {
                $result = MonthlyResult::where('fiscal_year_id', $fiscalYear->id)
                    ->where('metric_id', $metric->id)
                    ->where('target_month', $month)
                    ->first();

                $value = $result && $result->value !== null ? number_format($result->value, 0) : '';
                $monthDisplay = Carbon::parse($month)->format('Y年n月');
                
                $csvData[] = [
                    $metric->name,
                    $monthDisplay,
                    $value,
                ];
            }
        }

        // CSVファイルを生成
        $filename = $fiscalYear->name . '_' . $request->category . '_' . date('YmdHis') . '.csv';
        $file = fopen('php://temp', 'r+');
        
        // BOM付きUTF-8で出力（Excel対応）
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        
        rewind($file);
        $csv = stream_get_contents($file);
        fclose($file);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * CSVダウンロード（詳細形式）
     */
    public function downloadCsvDetail(Request $request)
    {
        $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'category' => 'required|string',
        ]);

        $fiscalYear = FiscalYear::findOrFail($request->fiscal_year_id);
        
        // カテゴリに属する指標を取得
        $category = \App\Models\Category::where('name', $request->category)
            ->whereNull('parent_id')
            ->first();
        
        if (!$category) {
            return response()->json(['error' => 'カテゴリが見つかりません'], 404);
        }

        $metrics = Metric::whereHas('category', function($query) use ($category) {
            $query->where('id', $category->id)
                  ->orWhere('parent_id', $category->id);
        })->get();

        // 年度の全月を取得
        $months = [];
        $current = Carbon::parse($fiscalYear->start_date)->startOfMonth();
        $end = Carbon::parse($fiscalYear->end_date)->endOfMonth();
        
        while ($current <= $end) {
            $months[] = $current->format('Y-m-01');
            $current->addMonth();
        }

        // CSVデータを生成
        $csvData = [];
        $csvData[] = ['指標名', '年月', '詳細', '金額'];

        foreach ($metrics as $metric) {
            foreach ($months as $month) {
                $result = MonthlyResult::where('fiscal_year_id', $fiscalYear->id)
                    ->where('metric_id', $metric->id)
                    ->where('target_month', $month)
                    ->first();

                $monthDisplay = Carbon::parse($month)->format('Y年n月');
                
                if ($result && $result->evidenceDetails()->count() > 0) {
                    // 詳細がある場合
                    foreach ($result->evidenceDetails as $detail) {
                        $csvData[] = [
                            $metric->name,
                            $monthDisplay,
                            $detail->detail,
                            number_format($detail->amount, 0),
                        ];
                    }
                } else {
                    // 詳細がない場合、値のみ
                    $value = $result && $result->value !== null ? number_format($result->value, 0) : '';
                    $csvData[] = [
                        $metric->name,
                        $monthDisplay,
                        '',
                        $value,
                    ];
                }
            }
        }

        // CSVファイルを生成
        $filename = $fiscalYear->name . '_' . $request->category . '_詳細_' . date('YmdHis') . '.csv';
        $file = fopen('php://temp', 'r+');
        
        // BOM付きUTF-8で出力（Excel対応）
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        
        rewind($file);
        $csv = stream_get_contents($file);
        fclose($file);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * CSVインポート
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'category' => 'required|string',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $fiscalYear = FiscalYear::findOrFail($request->fiscal_year_id);
        $file = $request->file('file');
        $validateOnly = $request->has('validate_only') && $request->validate_only === '1';
        
        // CSVファイルを読み込み
        $csvData = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // BOMをスキップ
            $firstLine = fgets($handle);
            if (substr($firstLine, 0, 3) === chr(0xEF).chr(0xBB).chr(0xBF)) {
                $firstLine = substr($firstLine, 3);
            }
            
            // ヘッダー行をスキップ
            $header = str_getcsv($firstLine);
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 2) {
                    $csvData[] = $row;
                }
            }
            fclose($handle);
        }

        // カテゴリに属する指標を取得
        $category = \App\Models\Category::where('name', $request->category)
            ->whereNull('parent_id')
            ->first();
        
        if (!$category) {
            return response()->json(['error' => 'カテゴリが見つかりません'], 404);
        }

        // keyBy('name') は同一名の指標が複数あると最後の1件だけ残る。
        // category_id を含む複合キーで区別し、検索時は指標名で照合する。
        $metrics = Metric::whereHas('category', function($query) use ($category) {
            $query->where('id', $category->id)
                  ->orWhere('parent_id', $category->id);
        })->get()->groupBy('name');

        $imported = 0;
        $errors = [];
        $preview = [];

        // バリデーション処理
        foreach ($csvData as $index => $row) {
            $rowNum = $index + 2; // ヘッダー行を考慮
            $rowError = null;
            
            if (count($row) < 2) {
                $rowError = "列数が不足しています（最低2列必要）";
                $preview[] = [
                    'metric_name' => '',
                    'month' => '',
                    'detail' => '',
                    'amount' => null,
                    'error' => $rowError,
                ];
                $errors[] = "{$rowNum}行目: {$rowError}";
                continue;
            }

            $metricName = trim($row[0]);
            $monthStr = trim($row[1]);
            
            // 指標を検索（同名指標が複数ある場合はエラーとして報告）
            $matched = $metrics->get($metricName);
            if (!$matched || $matched->isEmpty()) {
                $metric = null;
                $rowError = "指標「{$metricName}」が見つかりません。正しい指標名を入力してください。";
            } elseif ($matched->count() > 1) {
                $metric = null;
                $rowError = "指標「{$metricName}」が複数のカテゴリに存在するため特定できません。指標名を確認してください。";
            } else {
                $metric = $matched->first();
            }
            if (!$metric) {
                $preview[] = [
                    'metric_name' => $metricName,
                    'month' => $monthStr,
                    'detail' => count($row) >= 3 ? trim($row[2]) : '',
                    'amount' => count($row) >= 4 ? $this->parseAmount(trim($row[3])) : (count($row) >= 3 ? $this->parseAmount(trim($row[2])) : null),
                    'error' => $rowError,
                ];
                $errors[] = "{$rowNum}行目: {$rowError}";
                continue;
            }

            // 年月をパース（「2025年7月」形式を想定）
            $month = null;
            if (preg_match('/(\d{4})年(\d{1,2})月/', $monthStr, $matches)) {
                $year = (int)$matches[1];
                $monthNum = (int)$matches[2];
                $month = Carbon::create($year, $monthNum, 1)->format('Y-m-01');
            } else {
                $rowError = "年月の形式が不正です（{$monthStr}）。「YYYY年M月」形式で入力してください（例: 2025年7月）。";
                $preview[] = [
                    'metric_name' => $metricName,
                    'month' => $monthStr,
                    'detail' => count($row) >= 3 ? trim($row[2]) : '',
                    'amount' => count($row) >= 4 ? $this->parseAmount(trim($row[3])) : (count($row) >= 3 ? $this->parseAmount(trim($row[2])) : null),
                    'error' => $rowError,
                ];
                $errors[] = "{$rowNum}行目: {$rowError}";
                continue;
            }

            // 年度内かチェック
            $monthDate = Carbon::parse($month);
            if ($monthDate < Carbon::parse($fiscalYear->start_date) || $monthDate > Carbon::parse($fiscalYear->end_date)) {
                $rowError = "年月が年度範囲外です（{$monthStr}）。年度範囲: " . $fiscalYear->start_date->format('Y年n月') . '〜' . $fiscalYear->end_date->format('Y年n月');
                $preview[] = [
                    'metric_name' => $metricName,
                    'month' => $monthStr,
                    'detail' => count($row) >= 3 ? trim($row[2]) : '',
                    'amount' => count($row) >= 4 ? $this->parseAmount(trim($row[3])) : (count($row) >= 3 ? $this->parseAmount(trim($row[2])) : null),
                    'error' => $rowError,
                ];
                $errors[] = "{$rowNum}行目: {$rowError}";
                continue;
            }

            if (count($row) >= 4 && !empty(trim($row[2])) && !empty(trim($row[3]))) {
                // 詳細形式（指標名,年月,詳細,金額）
                $detail = trim($row[2]);
                $amount = $this->parseAmount(trim($row[3]));
                
                if ($amount === null) {
                    $rowError = "金額が不正です（{$row[3]}）。数値で入力してください（カンマ区切り可）。";
                    $preview[] = [
                        'metric_name' => $metricName,
                        'month' => $monthStr,
                        'detail' => $detail,
                        'amount' => null,
                        'error' => $rowError,
                    ];
                    $errors[] = "{$rowNum}行目: {$rowError}";
                    continue;
                }

                if (!$validateOnly) {
                    $monthlyResult = MonthlyResult::firstOrCreate(
                        [
                            'fiscal_year_id' => $fiscalYear->id,
                            'metric_id' => $metric->id,
                            'target_month' => $month,
                        ]
                    );

                    // 詳細を追加
                    EvidenceDetail::create([
                        'monthly_result_id' => $monthlyResult->id,
                        'detail' => $detail,
                        'amount' => $amount,
                        'sort_order' => $monthlyResult->evidenceDetails()->count(),
                    ]);
                }

                $preview[] = [
                    'metric_name' => $metricName,
                    'month' => $monthStr,
                    'detail' => $detail,
                    'amount' => $amount,
                    'error' => null,
                ];
                $imported++;
            } else if (count($row) >= 3) {
                // シンプル形式（指標名,年月,値）
                $valueStr = trim($row[2]);
                $value = $this->parseAmount($valueStr);
                
                // 値が空白でない場合のみエラーチェック
                if (!empty($valueStr) && $value === null) {
                    $rowError = "値が不正です（{$row[2]}）。数値で入力してください（カンマ区切り可）。";
                    $preview[] = [
                        'metric_name' => $metricName,
                        'month' => $monthStr,
                        'detail' => '',
                        'amount' => null,
                        'error' => $rowError,
                    ];
                    $errors[] = "{$rowNum}行目: {$rowError}";
                    continue;
                }

                if (!$validateOnly) {
                    // 既存のレコードを取得
                    $existingResult = MonthlyResult::where('fiscal_year_id', $fiscalYear->id)
                        ->where('metric_id', $metric->id)
                        ->where('target_month', $month)
                        ->first();
                    
                    if ($existingResult) {
                        // 既存レコードがある場合
                        if (empty($valueStr)) {
                            // 空白セルの場合は既存の値を保持（更新しない）
                            $value = $existingResult->value;
                        } else {
                            // 値がある場合は更新
                            $existingResult->value = $value;
                            $existingResult->save();
                        }
                    } else {
                        // 新規レコードの場合
                        if (!empty($valueStr)) {
                            // 値がある場合のみ作成
                            MonthlyResult::create([
                                'fiscal_year_id' => $fiscalYear->id,
                                'metric_id' => $metric->id,
                                'target_month' => $month,
                                'value' => $value,
                            ]);
                        }
                    }
                }

                // プレビュー用の値（既存値がある場合はそれを使用）
                $previewValue = $value;
                if ($validateOnly && empty($valueStr)) {
                    // バリデーション時は既存値を取得して表示
                    $existingResult = MonthlyResult::where('fiscal_year_id', $fiscalYear->id)
                        ->where('metric_id', $metric->id)
                        ->where('target_month', $month)
                        ->first();
                    if ($existingResult && $existingResult->value !== null) {
                        $previewValue = $existingResult->value;
                    }
                }

                $preview[] = [
                    'metric_name' => $metricName,
                    'month' => $monthStr,
                    'detail' => '',
                    'amount' => $previewValue,
                    'error' => null,
                ];
                $imported++;
            } else {
                $rowError = "列数が不足しています。シンプル形式（指標名,年月,値）または詳細形式（指標名,年月,詳細,金額）で入力してください。";
                $preview[] = [
                    'metric_name' => $metricName,
                    'month' => $monthStr,
                    'detail' => '',
                    'amount' => null,
                    'error' => $rowError,
                ];
                $errors[] = "{$rowNum}行目: {$rowError}";
                continue;
            }
        }

        if ($validateOnly) {
            // バリデーションのみの場合はプレビューを返す
            return response()->json([
                'success' => count($errors) === 0,
                'preview' => $preview,
                'errors' => $errors,
                'imported_count' => $imported,
            ]);
        }

        // 実際のインポート実行
        if (count($errors) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'エラーがあるためインポートを実行できませんでした。',
                'errors' => $errors,
                'imported' => $imported,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "{$imported}件のデータをインポートしました。",
            'imported' => $imported,
            'errors' => [],
        ]);
    }

    /**
     * 金額文字列を数値に変換
     */
    private function parseAmount(string $value): ?float
    {
        if (empty(trim($value))) {
            return null;
        }
        
        // カンマを除去
        $value = str_replace(',', '', $value);
        
        // 数値に変換
        $num = filter_var($value, FILTER_VALIDATE_FLOAT);
        
        return $num !== false ? $num : null;
    }

    /**
     * 指標の表示名マッピングを適用（ダッシュボードと同じロジック）
     */
    private function applyMetricDisplayNameMapping($metrics)
    {
        // ダッシュボードと同じマッピング定義
        $nameMappings = [
            // 全体
            ['csv_name' => '調整コスト（貸倒引当＋減額・値引想定）', 'display_name' => '調整コスト', 'category' => '全体'],
            
            // 新規開業
            ['csv_name' => '開業時計上売上', 'display_name' => '企業向け弁当', 'category' => '新規開業', 'sub_category' => '企業向け弁当'],
            ['csv_name' => '按分積上げ', 'display_name' => '企業向け弁当（按分）', 'category' => '新規開業', 'sub_category' => '企業向け弁当'],
            ['csv_name' => '開業時計上売上', 'display_name' => 'ファミリーデリ', 'category' => '新規開業', 'sub_category' => 'ファミリーデリ'],
            ['csv_name' => '按分積上げ', 'display_name' => 'ファミリーデリ（按分）', 'category' => '新規開業', 'sub_category' => 'ファミリーデリ'],
            ['csv_name' => '開業時計上売上', 'display_name' => 'A型B型申請', 'category' => '新規開業', 'sub_category' => 'A型・B型申請'],
            ['csv_name' => '開業時計上売上', 'display_name' => 'GH申請', 'category' => '新規開業', 'sub_category' => 'GH申請'],
            
            // ランニング
            ['csv_name' => '継続費売上合計', 'display_name' => 'やどかり／はぐくみ', 'category' => 'ランニング', 'sub_category' => 'やどかり/はぐくみ'],
            ['csv_name' => '継続費売上（半年経過）', 'display_name' => '継続費売上（半年経過）', 'category' => 'ランニング', 'sub_category' => 'ファミリーデリ'],
            ['csv_name' => '継続費売上（1年以上経過）', 'display_name' => '継続費売上（1年以上経過）', 'category' => 'ランニング', 'sub_category' => 'ファミリーデリ'],
            ['csv_name' => '福祉継続費売上合計', 'display_name' => '福祉事業', 'category' => 'ランニング', 'sub_category' => '福祉継続'],
            
            // 新規事業その他
            ['csv_name' => 'はぐパス：売上', 'display_name' => 'はぐパス', 'category' => null],
            ['csv_name' => 'はぐくみファイナンス売上', 'display_name' => 'はぐくみファイナンス', 'category' => null],
        ];
        
        foreach ($metrics as $metric) {
            $category = $metric->category;
            $rootCategory = $category->parent ?? $category;
            
            foreach ($nameMappings as $mapping) {
                $match = false;
                
                // カテゴリとサブカテゴリのチェック
                if (isset($mapping['category'])) {
                    if ($mapping['category'] !== $rootCategory->name) {
                        continue;
                    }
                } else {
                    // categoryがnullの場合は、全体、新規開業、ランニング、商品卸以外
                    if (in_array($rootCategory->name, ['全体', '新規開業', 'ランニング', '商品卸'])) {
                        continue;
                    }
                }
                
                if (isset($mapping['sub_category'])) {
                    if ($mapping['sub_category'] !== $category->name) {
                        continue;
                    }
                }
                
                // metric_idが指定されている場合は直接チェック
                if (isset($mapping['metric_id'])) {
                    if ($mapping['metric_id'] === $metric->id && $mapping['csv_name'] === $metric->name) {
                        $match = true;
                    }
                } else {
                    // csv_nameと一致するかチェック
                    if ($mapping['csv_name'] === $metric->name) {
                        $match = true;
                    }
                }
                
                if ($match) {
                    $metric->display_name = $mapping['display_name'];
                    break;
                }
            }
        }
    }
}

