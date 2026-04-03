<?php

namespace App\Http\Controllers;

use App\Helpers\DashboardHelper;
use App\Models\FiscalYear;
use App\Models\Metric;
use App\Models\MonthlyResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * ダッシュボード表示
     */
    public function index(Request $request)
    {
        $fiscalYearId = $request->get('fiscal_year_id');
        
        if ($fiscalYearId) {
            $fiscalYear = FiscalYear::find($fiscalYearId);
        } else {
            $fiscalYear = FiscalYear::getActive();
        }
        
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();
        
        if (!$fiscalYear) {
            return view('dashboard', [
                'fiscalYear' => null,
                'fiscalYears' => $fiscalYears,
                'kpiCards' => [],
                'segments' => [],
                'monthlyData' => [],
                'availableMonths' => collect(),
                'selectedMonth' => null,
            ]);
        }

        // 月次データを取得
        $monthlyData = MonthlyResult::where('fiscal_year_id', $fiscalYear->id)
            ->with(['metric.category.parent'])
            ->orderBy('target_month')
            ->get()
            ->groupBy('metric_id');

        // KPIカード用の利用可能な月のリストを取得（値が存在する月のみ）
        $allAvailableMonths = MonthlyResult::select('target_month')
            ->whereNotNull('value')
            ->distinct()
            ->orderBy('target_month', 'desc')
            ->get()
            ->map(function($result) {
                return [
                    'value' => $result->target_month->format('Y-m-d'),
                    'label' => $result->target_month->format('Y年n月'),
                ];
            });

        // 選択された月を取得（デフォルトは前月）
        $selectedMonth = $request->get('selected_month');
        if (!$selectedMonth) {
            // 前月を計算（閲覧している時点から数えて前月）
            $now = \Carbon\Carbon::now();
            $previousMonth = $now->copy()->subMonth()->startOfMonth();
            $selectedMonth = $previousMonth->format('Y-m-d');
            
            // 前月が利用可能な月リストに存在しない場合は、最新月を使用
            $exists = $allAvailableMonths->contains(function($month) use ($selectedMonth) {
                return $month['value'] === $selectedMonth;
            });
            if (!$exists && $allAvailableMonths->isNotEmpty()) {
                $selectedMonth = $allAvailableMonths->first()['value'];
            }
        } elseif ($selectedMonth) {
            // 選択された月が利用可能な月リストに存在するか確認
            $exists = $allAvailableMonths->contains(function($month) use ($selectedMonth) {
                return $month['value'] === $selectedMonth;
            });
            if (!$exists && $allAvailableMonths->isNotEmpty()) {
                $selectedMonth = $allAvailableMonths->first()['value'];
            }
        }
        
        // KPIカード用の利用可能な月リスト
        $availableMonths = $allAvailableMonths;

        // KPIカード用のデータを計算（選択された月のデータ、全年度から取得）
        $kpiCards = $this->calculateKPICards($selectedMonth);
        
        // 期間別サマリーを計算
        $periodSummary = $this->calculatePeriodSummary($fiscalYear, $monthlyData);

        // セグメント別に指標をグループ化
        $segments = $this->groupMetricsBySegment($fiscalYear, $monthlyData);
        
        // 数量指標系のセグメントを追加
        $operationSegments = $this->groupOperationMetricsBySegment($fiscalYear, $monthlyData);

        // グラフ用データを準備
        $chartData = $this->prepareChartData($fiscalYear, $monthlyData);
        
        // 前期比較は無効化
        $previousChartData = null;
        
        return view('dashboard', [
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => $fiscalYears,
            'kpiCards' => $kpiCards,
            'periodSummary' => $periodSummary,
            'segments' => $segments,
            'operationSegments' => $operationSegments,
            'monthlyData' => $monthlyData,
            'chartData' => $chartData,
            'previousChartData' => $previousChartData,
            'availableMonths' => $availableMonths,
            'selectedMonth' => $selectedMonth,
        ]);
    }
    
    /**
     * グラフ用データを準備
     */
    private function prepareChartData(FiscalYear $fiscalYear, $monthlyData): array
    {
        // 年度の全月を生成（7月〜6月）
        $currentMonth = $fiscalYear->start_date->copy()->startOfMonth();
        $endMonth = $fiscalYear->end_date->copy()->endOfMonth();
        $allFiscalMonths = [];
        
        while ($currentMonth <= $endMonth) {
            $allFiscalMonths[] = $currentMonth->copy();
            $currentMonth->addMonth();
        }

        if (empty($allFiscalMonths)) {
            return [
                'months' => collect(),
                'revenue' => [],
                'expenses' => [],
                'profit' => [],
            ];
        }

        // 売上データ（全体の売上合計）
        $revenueData = [];
        $overallCategory = \App\Models\Category::where('name', '全体')
            ->whereNull('parent_id')
            ->first();
        
        if ($overallCategory) {
            $revenueMetric = \App\Models\Metric::where('category_id', $overallCategory->id)
                ->where('name', '売上合計')
                ->first();
            
            if ($revenueMetric) {
                foreach ($allFiscalMonths as $month) {
                    $monthStr = $month->format('Y-m-d');
                    $results = $monthlyData->get($revenueMetric->id);
                    if ($results) {
                        $result = $results->first(function($r) use ($monthStr) {
                            return $r->target_month->format('Y-m-d') === $monthStr;
                        });
                        // データがある場合のみ値を設定、ない場合はnull
                        $revenueData[$monthStr] = ($result && $result->value !== null) ? $result->value : null;
                    } else {
                        $revenueData[$monthStr] = null;
                    }
                }
            }
        }

        // 経費データ（販管費合計 + 原価合計）
        $expenseMetrics = ['販管費合計', '原価合計'];
        $expenseData = [];
        
        if ($overallCategory) {
            foreach ($allFiscalMonths as $month) {
                $monthStr = $month->format('Y-m-d');
                $totalExpense = 0;
                $hasData = false;
                
                foreach ($expenseMetrics as $metricName) {
                    $metric = \App\Models\Metric::where('category_id', $overallCategory->id)
                        ->where('name', $metricName)
                        ->first();
                    
                    if ($metric) {
                        $results = $monthlyData->get($metric->id);
                        if ($results) {
                            $result = $results->first(function($r) use ($monthStr) {
                                return $r->target_month->format('Y-m-d') === $monthStr;
                            });
                            if ($result && $result->value !== null) {
                                $totalExpense += $result->value;
                                $hasData = true;
                            }
                        }
                    }
                }
                // データがある場合のみ値を設定、ない場合はnull
                $expenseData[$monthStr] = $hasData ? $totalExpense : null;
            }
        }

        // 経常利益（折れ線グラフ用）
        $profitData = [];
        if ($overallCategory) {
            $profitMetric = \App\Models\Metric::where('category_id', $overallCategory->id)
                ->where('name', '経常利益')
                ->first();
            
            if ($profitMetric) {
                foreach ($allFiscalMonths as $month) {
                    $monthStr = $month->format('Y-m-d');
                    $results = $monthlyData->get($profitMetric->id);
                    if ($results) {
                        $result = $results->first(function($r) use ($monthStr) {
                            return $r->target_month->format('Y-m-d') === $monthStr;
                        });
                        // データがある場合のみ値を設定、ない場合はnull
                        $profitData[$monthStr] = ($result && $result->value !== null) ? $result->value : null;
                    } else {
                        $profitData[$monthStr] = null;
                    }
                }
            }
        }

        return [
            'months' => collect($allFiscalMonths)->map(function($month) {
                return $month->format('n月');
            })->values(),
            'revenue' => array_values($revenueData),
            'expenses' => array_values($expenseData),
            'profit' => array_values($profitData),
        ];
    }

    /**
     * KPIカードのデータを計算（全年度から取得）
     */
    private function calculateKPICards(?string $selectedMonth = null): array
    {
        $kpis = [];
        
        if (!$selectedMonth) {
            return [];
        }
        
        // 選択された月のCarbonオブジェクト
        $selectedMonthDate = \Carbon\Carbon::parse($selectedMonth);
        $previousMonthDate = $selectedMonthDate->copy()->subMonthNoOverflow();
        $previousYearMonthDate = $selectedMonthDate->copy()->subYear(); // 前期比用（1年前の同じ月）
        
        // 全年度から月次データを一括取得（3つのクエリを1つに統合）
        $targetMonths = [
            $selectedMonthDate->format('Y-m-d'),
            $previousMonthDate->format('Y-m-d'),
            $previousYearMonthDate->format('Y-m-d'),
        ];
        
        $allMonthlyResults = MonthlyResult::whereIn('target_month', $targetMonths)
            ->with(['metric.category.parent'])
            ->get();
        
        // 月別にデータを分割してからmetric_idでグループ化
        $allMonthlyData = $allMonthlyResults
            ->filter(function($result) use ($selectedMonthDate) {
                return $result->target_month && $result->target_month->isSameDay($selectedMonthDate);
            })
            ->groupBy('metric_id');
        
        $previousMonthlyData = $allMonthlyResults
            ->filter(function($result) use ($previousMonthDate) {
                return $result->target_month && $result->target_month->isSameDay($previousMonthDate);
            })
            ->groupBy('metric_id');
        
        $previousYearMonthlyData = $allMonthlyResults
            ->filter(function($result) use ($previousYearMonthDate) {
                return $result->target_month && $result->target_month->isSameDay($previousYearMonthDate);
            })
            ->groupBy('metric_id');
        
        // 必要な指標を一括取得（N+1問題を解決）
        $overallCategory = \App\Models\Category::where('name', '全体')->whereNull('parent_id')->first();
        $metrics = $overallCategory 
            ? Metric::where('category_id', $overallCategory->id)
                ->whereIn('name', ['売上合計', '経常利益', '原価合計', '販管費合計'])
                ->get()
                ->keyBy('name')
            : collect();
        
        // 売上合計
        $revenueMetric = $metrics->get('売上合計');
        
        if ($revenueMetric) {
            $currentValue = $allMonthlyData->get($revenueMetric->id)?->first()?->value;
            $previousValue = $previousMonthlyData->get($revenueMetric->id)?->first()?->value;
            $previousYearValue = $previousYearMonthlyData->get($revenueMetric->id)?->first()?->value;
            $kpis['revenue'] = [
                'label' => '売上合計',
                'value' => $currentValue,
                'previous' => $previousValue,
                'mom' => DashboardHelper::calculateMonthOverMonth($currentValue, $previousValue),
                'yoy' => DashboardHelper::calculateMonthOverMonth($currentValue, $previousYearValue),
                'icon' => 'bi-cash-stack',
                'color' => 'primary',
            ];
        }

        // 経常利益
        $profitMetric = $metrics->get('経常利益');
        
        if ($profitMetric) {
            $currentValue = $allMonthlyData->get($profitMetric->id)?->first()?->value;
            $previousValue = $previousMonthlyData->get($profitMetric->id)?->first()?->value;
            $previousYearValue = $previousYearMonthlyData->get($profitMetric->id)?->first()?->value;
            $kpis['profit'] = [
                'label' => '経常利益',
                'value' => $currentValue,
                'previous' => $previousValue,
                'mom' => DashboardHelper::calculateMonthOverMonth($currentValue, $previousValue),
                'yoy' => DashboardHelper::calculateMonthOverMonth($currentValue, $previousYearValue),
                'icon' => 'bi-graph-up-arrow',
                'color' => 'success',
            ];
        }

        // 原価合計
        $costMetric = $metrics->get('原価合計');
        
        if ($costMetric) {
            $currentValue = $allMonthlyData->get($costMetric->id)?->first()?->value;
            $previousValue = $previousMonthlyData->get($costMetric->id)?->first()?->value;
            $previousYearValue = $previousYearMonthlyData->get($costMetric->id)?->first()?->value;
            $kpis['cost'] = [
                'label' => '原価合計',
                'value' => $currentValue,
                'previous' => $previousValue,
                'mom' => DashboardHelper::calculateMonthOverMonth($currentValue, $previousValue),
                'yoy' => DashboardHelper::calculateMonthOverMonth($currentValue, $previousYearValue),
                'icon' => 'bi-cart',
                'color' => 'warning',
                'reverse' => true, // 原価は増加が悪い
            ];
        }

        // 販管費合計
        $expenseMetric = $metrics->get('販管費合計');
        
        if ($expenseMetric) {
            $currentValue = $allMonthlyData->get($expenseMetric->id)?->first()?->value;
            $previousValue = $previousMonthlyData->get($expenseMetric->id)?->first()?->value;
            $previousYearValue = $previousYearMonthlyData->get($expenseMetric->id)?->first()?->value;
            $kpis['expense'] = [
                'label' => '販管費合計',
                'value' => $currentValue,
                'previous' => $previousValue,
                'mom' => DashboardHelper::calculateMonthOverMonth($currentValue, $previousValue),
                'yoy' => DashboardHelper::calculateMonthOverMonth($currentValue, $previousYearValue),
                'icon' => 'bi-receipt',
                'color' => 'info',
                'reverse' => true, // 費用は増加が悪い
            ];
        }

        return $kpis;
    }
    
    /**
     * 期間別サマリーを計算（上半期・下半期・通期）+ 前期比
     * 
     * 前期比は「今期でデータがある月」と「前期の同じ月」を比較する
     * 例: 今期7-11月にデータがあれば、前期も7-11月のみを合計して比較
     */
    private function calculatePeriodSummary(FiscalYear $fiscalYear, $monthlyData): array
    {
        $summary = [
            'first_half' => ['revenue' => 0, 'profit' => 0, 'cost' => 0, 'expense' => 0],
            'second_half' => ['revenue' => 0, 'profit' => 0, 'cost' => 0, 'expense' => 0],
            'full_year' => ['revenue' => 0, 'profit' => 0, 'cost' => 0, 'expense' => 0],
            'prev_first_half' => ['revenue' => 0, 'profit' => 0, 'cost' => 0, 'expense' => 0],
            'prev_second_half' => ['revenue' => 0, 'profit' => 0, 'cost' => 0, 'expense' => 0],
            'prev_full_year' => ['revenue' => 0, 'profit' => 0, 'cost' => 0, 'expense' => 0],
        ];
        
        // 上半期: 7月〜12月、下半期: 1月〜6月
        $startDate = $fiscalYear->start_date->copy();
        $firstHalfEnd = $startDate->copy()->addMonths(5)->endOfMonth(); // 12月末
        $secondHalfStart = $firstHalfEnd->copy()->addDay()->startOfMonth(); // 1月初
        $endDate = $fiscalYear->end_date->copy();
        
        // 前期の年度を取得
        $previousFiscalYear = FiscalYear::where('end_date', '<', $fiscalYear->start_date)
            ->orderBy('end_date', 'desc')
            ->first();
        
        // メトリクスを取得
        $overallCategory = \App\Models\Category::where('name', '全体')
            ->whereNull('parent_id')
            ->first();
        
        if (!$overallCategory) {
            return $summary;
        }
        
        $metrics = [
            'revenue' => Metric::where('category_id', $overallCategory->id)->where('name', '売上合計')->first(),
            'profit' => Metric::where('category_id', $overallCategory->id)->where('name', '経常利益')->first(),
            'cost' => Metric::where('category_id', $overallCategory->id)->where('name', '原価合計')->first(),
            'expense' => Metric::where('category_id', $overallCategory->id)->where('name', '販管費合計')->first(),
        ];
        
        // 今期でデータがある月を追跡（前期比較用）
        $currentFirstHalfMonths = [];  // 上半期でデータがある月（月番号: 7,8,9,10,11,12）
        $currentSecondHalfMonths = []; // 下半期でデータがある月（月番号: 1,2,3,4,5,6）
        $currentAllMonths = [];        // 通期でデータがある月
        
        // 当期データ - まずrevenueでデータがある月を特定
        $revenueMetric = $metrics['revenue'];
        if ($revenueMetric) {
            $revenueResults = $monthlyData->get($revenueMetric->id);
            if ($revenueResults) {
                foreach ($revenueResults as $result) {
                    if ($result->value === null) continue;
                    
                    $month = $result->target_month;
                    $monthNum = (int)$month->format('n'); // 月番号（1-12）
                    
                    // 上半期（7月〜12月）
                    if ($month >= $startDate && $month <= $firstHalfEnd) {
                        $currentFirstHalfMonths[] = $monthNum;
                    }
                    // 下半期（1月〜6月）
                    elseif ($month >= $secondHalfStart && $month <= $endDate) {
                        $currentSecondHalfMonths[] = $monthNum;
                    }
                    
                    $currentAllMonths[] = $monthNum;
                }
            }
        }
        
        // 当期データを集計
        foreach ($metrics as $key => $metric) {
            if (!$metric) continue;
            
            $results = $monthlyData->get($metric->id);
            if (!$results) continue;
            
            foreach ($results as $result) {
                if ($result->value === null) continue;
                
                $month = $result->target_month;
                
                // 上半期（7月〜12月）
                if ($month >= $startDate && $month <= $firstHalfEnd) {
                    $summary['first_half'][$key] += $result->value;
                }
                // 下半期（1月〜6月）
                elseif ($month >= $secondHalfStart && $month <= $endDate) {
                    $summary['second_half'][$key] += $result->value;
                }
                
                // 通期
                $summary['full_year'][$key] += $result->value;
            }
        }
        
        // 前期データを取得（今期と同じ月のみを集計）
        if ($previousFiscalYear) {
            $prevMonthlyData = MonthlyResult::where('fiscal_year_id', $previousFiscalYear->id)
                ->with(['metric.category.parent'])
                ->orderBy('target_month')
                ->get()
                ->groupBy('metric_id');
            
            foreach ($metrics as $key => $metric) {
                if (!$metric) continue;
                
                $results = $prevMonthlyData->get($metric->id);
                if (!$results) continue;
                
                foreach ($results as $result) {
                    if ($result->value === null) continue;
                    
                    $month = $result->target_month;
                    $monthNum = (int)$month->format('n'); // 月番号（1-12）
                    
                    // 前期上半期（今期と同じ月のみ）
                    if (in_array($monthNum, $currentFirstHalfMonths)) {
                        $summary['prev_first_half'][$key] += $result->value;
                    }
                    // 前期下半期（今期と同じ月のみ）
                    if (in_array($monthNum, $currentSecondHalfMonths)) {
                        $summary['prev_second_half'][$key] += $result->value;
                    }
                    
                    // 前期通期（今期と同じ月のみ）
                    if (in_array($monthNum, $currentAllMonths)) {
                        $summary['prev_full_year'][$key] += $result->value;
                    }
                }
            }
        }
        
        return $summary;
    }

    /**
     * 指定された月の値を取得
     */
    private function getValueForMonth(int $metricId, ?\Carbon\Carbon $monthDate, $monthlyData): ?float
    {
        if (!$monthDate) {
            return null;
        }
        
        $results = $monthlyData->get($metricId);
        if (!$results) {
            return null;
        }
        
        $result = $results->first(function($r) use ($monthDate) {
            return $r->target_month->format('Y-m-d') === $monthDate->format('Y-m-d');
        });
        
        return $result ? $result->value : null;
    }

    /**
     * 最新月と前月の値を取得
     */
    private function getLatestAndPrevious(int $metricId, int $fiscalYearId, $monthlyData): array
    {
        $results = $monthlyData->get($metricId);
        
        if (!$results || $results->isEmpty()) {
            return ['current' => null, 'previous' => null, 'current_month' => null, 'previous_month' => null];
        }

        $withValue = $results->filter(fn($r) => $r->value !== null)->sortByDesc('target_month');
        $latest = $withValue->first();
        $previous = $withValue->skip(1)->first();

        return [
            'current' => $latest?->value,
            'previous' => $previous?->value,
            'current_month' => $latest?->target_month,
            'previous_month' => $previous?->target_month,
        ];
    }


    /**
     * セグメント別に指標をグループ化
     */
    private function groupMetricsBySegment(FiscalYear $fiscalYear, $monthlyData): array
    {
        // セグメント定義（CSVの実際の指標名を使用）
        $segmentDefinitions = [
            '全体' => [
                'category' => '全体',
                'metrics' => [
                    ['name' => '売上合計', 'csv_name' => '売上合計'],
                    ['name' => '原価合計', 'csv_name' => '原価合計'],
                    ['name' => '物流費合計', 'csv_name' => '物流費合計'],
                    ['name' => '調整コスト', 'csv_name' => '調整コスト（貸倒引当＋減額・値引想定）'],
                    ['name' => '販管費合計', 'csv_name' => '販管費合計'],
                    ['name' => '経常利益', 'csv_name' => '経常利益'],
                ],
            ],
            '新規開業' => [
                'category' => '新規開業',
                'metrics' => [
                    ['name' => '合計', 'csv_name' => null, 'is_total' => true],
                    ['name' => '企業向け弁当', 'csv_name' => '開業時計上売上', 'sub_category' => '企業向け弁当'],
                    ['name' => '企業向け弁当（按分）', 'csv_name' => '按分積上げ', 'sub_category' => '企業向け弁当'],
                    ['name' => 'ファミリーデリ', 'csv_name' => '開業時計上売上', 'sub_category' => 'ファミリーデリ'],
                    ['name' => 'ファミリーデリ（按分）', 'csv_name' => '按分積上げ', 'sub_category' => 'ファミリーデリ'],
                    ['name' => 'A型B型申請', 'csv_name' => '開業時計上売上', 'sub_category' => 'A型・B型申請'],
                    ['name' => 'GH申請', 'csv_name' => '開業時計上売上', 'sub_category' => 'GH申請'],
                ],
                'calculateTotal' => true,
            ],
            'ランニング' => [
                'category' => 'ランニング',
                'metrics' => [
                    ['name' => '合計', 'csv_name' => null, 'is_total' => true],
                    ['name' => 'やどかり／はぐくみ', 'csv_name' => '継続費売上合計', 'sub_category' => 'やどかり/はぐくみ'],
                    ['name' => 'FD（半年）', 'csv_name' => '継続費売上（半年経過）', 'sub_category' => 'ファミリーデリ', 'metric_id' => 19],
                    ['name' => 'FD（1年）', 'csv_name' => '継続費売上（1年以上経過）', 'sub_category' => 'ファミリーデリ', 'metric_id' => 47],
                    ['name' => '福祉事業', 'csv_name' => '福祉継続費売上合計', 'sub_category' => '福祉継続'],
                    ['name' => '請求代行手数料', 'csv_name' => '請求代行手数料', 'sub_category' => '福祉継続'],
                ],
                'calculateTotal' => true,
            ],
            '商品卸' => [
                'category' => '商品卸',
                'metrics' => [
                    ['name' => '卸売上全体（システム込）', 'csv_name' => '卸売上全体（システム込）'],
                ],
                'calculateTotal' => false,
            ],
            '新規事業その他' => [
                'category' => null,
                'metrics' => [
                    ['name' => '合計', 'csv_name' => null, 'is_total' => true],
                    ['name' => 'VRロイヤリティ', 'csv_name' => 'VRロイヤリティ', 'sub_category' => null],
                    ['name' => 'はぐWeb', 'csv_name' => 'はぐWeb', 'sub_category' => null],
                    ['name' => 'はぐパス', 'csv_name' => 'はぐパス：売上', 'sub_category' => null],
                    ['name' => 'はぐくみファイナンス', 'csv_name' => 'はぐくみファイナンス売上', 'sub_category' => null],
                ],
                'calculateTotal' => true,
            ],
        ];

        $segments = [];
        
        foreach ($segmentDefinitions as $segmentName => $definition) {
            $segmentMetrics = [];
            
            if ($definition['category']) {
                // 特定カテゴリから取得
                $rootCategory = \App\Models\Category::where('name', $definition['category'])
                    ->whereNull('parent_id')
                    ->first();
                
                if ($rootCategory) {
                    $otherMetrics = [];
                    foreach ($definition['metrics'] as $metricDef) {
                        if (is_array($metricDef) && isset($metricDef['is_total']) && $metricDef['is_total']) {
                            // 合計行は後で計算
                            continue;
                        }
                        
                        $csvName = is_array($metricDef) ? ($metricDef['csv_name'] ?? $metricDef['name']) : $metricDef;
                        $displayName = is_array($metricDef) ? $metricDef['name'] : $metricDef;
                        $subCategory = is_array($metricDef) ? ($metricDef['sub_category'] ?? null) : null;
                        $filter = is_array($metricDef) ? ($metricDef['filter'] ?? null) : null;
                        
                        $metric = null;
                        
                        if ($subCategory) {
                            // 子カテゴリから取得
                            $subCategoryObj = \App\Models\Category::where('name', $subCategory)
                                ->where('parent_id', $rootCategory->id)
                                ->first();
                            
                            if ($subCategoryObj) {
                                // metric_idが指定されている場合は直接取得
                                if (isset($metricDef['metric_id']) && $metricDef['metric_id'] !== null) {
                                    $metric = Metric::find($metricDef['metric_id']);
                                } else {
                                    $query = Metric::where('category_id', $subCategoryObj->id)
                                        ->where('name', $csvName);
                                    $metric = $query->first();
                                }
                            }
                        } else {
                            // ルートカテゴリから取得
                            $metric = Metric::where('category_id', $rootCategory->id)
                                ->where('name', $csvName)
                                ->first();
                        }
                        
                        if ($metric) {
                            // 表示名を上書き
                            $metric->display_name = $displayName;
                            
                            $data = $this->getLatestAndPrevious($metric->id, $fiscalYear->id, $monthlyData);
                            $segmentMetrics[] = [
                                'metric' => $metric,
                                'current' => $data['current'],
                                'previous' => $data['previous'],
                                'mom' => DashboardHelper::calculateMonthOverMonth($data['current'], $data['previous']),
                                'current_month' => $data['current_month'],
                            ];
                            $otherMetrics[] = $metric;
                        }
                    }
                    
                    // 合計行を計算（新規開業の場合）
                    $hasTotal = false;
                    foreach ($definition['metrics'] as $metricDef) {
                        if (is_array($metricDef) && isset($metricDef['is_total']) && $metricDef['is_total']) {
                            $hasTotal = true;
                            break;
                        }
                    }
                    
                    if ($hasTotal && isset($definition['calculateTotal']) && $definition['calculateTotal']) {
                        $totalCurrent = 0;
                        $totalPrevious = 0;
                        foreach ($otherMetrics as $m) {
                            $data = $this->getLatestAndPrevious($m->id, $fiscalYear->id, $monthlyData);
                            $totalCurrent += $data['current'] ?? 0;
                            $totalPrevious += $data['previous'] ?? 0;
                        }
                        
                        // 合計行を先頭に追加
                        array_unshift($segmentMetrics, [
                            'metric' => (object)[
                                'id' => 0,
                                'name' => '合計',
                                'type' => 'currency',
                                'unit' => '円',
                            ],
                            'current' => $totalCurrent,
                            'previous' => $totalPrevious,
                            'mom' => DashboardHelper::calculateMonthOverMonth($totalCurrent, $totalPrevious),
                        ]);
                    }
                }
            } else {
                // 複数カテゴリから取得（新規事業その他）
                $otherMetrics = [];
                foreach ($definition['metrics'] as $metricDef) {
                    if (is_array($metricDef) && isset($metricDef['is_total']) && $metricDef['is_total']) {
                        // 合計行は後で計算
                        continue;
                    }
                    
                    $csvName = is_array($metricDef) ? ($metricDef['csv_name'] ?? $metricDef['name']) : $metricDef;
                    $displayName = is_array($metricDef) ? $metricDef['name'] : $metricDef;
                    
                    $metric = Metric::where('name', $csvName)
                        ->with('category.parent')
                        ->first();
                    
                    if ($metric) {
                        // 表示名を上書き
                        $metric->display_name = $displayName;
                        
                        $data = $this->getLatestAndPrevious($metric->id, $fiscalYear->id, $monthlyData);
                        $segmentMetrics[] = [
                            'metric' => $metric,
                            'current' => $data['current'],
                            'previous' => $data['previous'],
                            'mom' => DashboardHelper::calculateMonthOverMonth($data['current'], $data['previous']),
                            'current_month' => $data['current_month'],
                        ];
                        $otherMetrics[] = $metric;
                    }
                }
                
                // 合計行を計算（新規事業その他の場合）
                $hasTotal = false;
                foreach ($definition['metrics'] as $metricDef) {
                    if (is_array($metricDef) && isset($metricDef['is_total']) && $metricDef['is_total']) {
                        $hasTotal = true;
                        break;
                    }
                }
                
                if ($hasTotal && isset($definition['calculateTotal']) && $definition['calculateTotal']) {
                    $totalCurrent = 0;
                    $totalPrevious = 0;
                    foreach ($otherMetrics as $m) {
                        $data = $this->getLatestAndPrevious($m->id, $fiscalYear->id, $monthlyData);
                        $totalCurrent += $data['current'] ?? 0;
                        $totalPrevious += $data['previous'] ?? 0;
                    }
                    
                    // 合計行を先頭に追加
                    array_unshift($segmentMetrics, [
                        'metric' => (object)[
                            'id' => 0,
                            'name' => '合計',
                            'type' => 'currency',
                            'unit' => '円',
                        ],
                        'current' => $totalCurrent,
                        'previous' => $totalPrevious,
                        'mom' => DashboardHelper::calculateMonthOverMonth($totalCurrent, $totalPrevious),
                    ]);
                }
            }
            
            if (!empty($segmentMetrics)) {
                // グラフ用データを準備（合計行を除く）
                $metricsForChart = array_filter($segmentMetrics, function($item) {
                    return isset($item['metric']->id) && $item['metric']->id !== 0;
                });
                $chartData = $this->prepareSegmentChartData($fiscalYear, $monthlyData, $segmentName, $metricsForChart);
                
                // 1期分（7月〜6月）の合計を計算
                $fiscalYearTotal = $this->calculateFiscalYearTotal($fiscalYear, $segmentMetrics, $monthlyData);
                
                // 各月の値を取得
                $monthlyValues = $this->getMonthlyValues($fiscalYear, $segmentMetrics, $monthlyData);
                
                // 合計行の1期分を計算
                if (isset($segmentMetrics[0]) && isset($segmentMetrics[0]['metric']->id) && $segmentMetrics[0]['metric']->id === 0) {
                    $totalFiscalYear = 0;
                    foreach (array_slice($segmentMetrics, 1) as $item) {
                        if (isset($item['metric']->id)) {
                            $totalFiscalYear += $fiscalYearTotal[$item['metric']->id] ?? 0;
                        }
                    }
                    $fiscalYearTotal[0] = $totalFiscalYear;
                    
                    // 合計行の各月の値を計算
                    $totalMonthlyValues = [];
                    $months = ['7月', '8月', '9月', '10月', '11月', '12月', '1月', '2月', '3月', '4月', '5月', '6月'];
                    foreach ($months as $month) {
                        $total = 0;
                        foreach (array_slice($segmentMetrics, 1) as $item) {
                            if (isset($item['metric']->id)) {
                                $total += $monthlyValues[$item['metric']->id][$month] ?? 0;
                            }
                        }
                        $totalMonthlyValues[$month] = $total;
                    }
                    $monthlyValues[0] = $totalMonthlyValues;
                }
                
                // 前月比の対象月ラベルを取得（最初の非合計メトリクスから）
                $momLabel = null;
                foreach ($segmentMetrics as $item) {
                    if (isset($item['current_month']) && $item['current_month']) {
                        $momLabel = $item['current_month']->format('n月');
                        break;
                    }
                }
                
                $segments[$segmentName] = [
                    'name' => $segmentName,
                    'metrics' => $segmentMetrics,
                    'chartData' => $chartData,
                    'fiscalYearTotal' => $fiscalYearTotal,
                    'monthlyValues' => $monthlyValues,
                    'momLabel' => $momLabel,
                ];
            }
        }
        
        return array_values($segments);
    }

    /**
     * セグメント別のグラフデータを準備
     * 1期分（7月〜6月）の全月を表示し、データがある月のみプロット
     */
    private function prepareSegmentChartData(FiscalYear $fiscalYear, $monthlyData, string $segmentName, array $metrics): array
    {
        // 年度の全月を生成（7月〜6月）
        $currentMonth = $fiscalYear->start_date->copy()->startOfMonth();
        $endMonth = $fiscalYear->end_date->copy()->endOfMonth();
        $allFiscalMonths = [];
        
        while ($currentMonth <= $endMonth) {
            $allFiscalMonths[] = $currentMonth->copy();
            $currentMonth->addMonth();
        }

        if (empty($allFiscalMonths)) {
            return [
                'months' => collect(),
                'data' => [],
            ];
        }

        // データが存在する月を取得
        $dataMonths = collect();
        foreach ($monthlyData as $results) {
            foreach ($results as $result) {
                $dataMonths->push($result->target_month->format('Y-m-d'));
            }
        }
        $uniqueDataMonths = $dataMonths->unique()->sort()->values();

        $chartData = [];
        
        foreach ($metrics as $item) {
            $metric = $item['metric'];
            $data = [];
            
            // 全月に対してデータを設定（データがある月のみ値、ない月はnull）
            foreach ($allFiscalMonths as $month) {
                $monthStr = $month->format('Y-m-d');
                $results = $monthlyData->get($metric->id);
                
                if ($results) {
                    $result = $results->first(function($r) use ($monthStr) {
                        return $r->target_month->format('Y-m-d') === $monthStr;
                    });
                    // データがある場合のみ値を設定、ない場合はnull
                    $data[$monthStr] = ($result && $result->value !== null) ? $result->value : null;
                } else {
                    $data[$monthStr] = null;
                }
            }
            
            $chartData[] = [
                'label' => isset($metric->display_name) ? $metric->display_name : $metric->name,
                'data' => array_values($data),
            ];
        }

        return [
            'months' => collect($allFiscalMonths)->map(function($month) {
                return $month->format('n月');
            })->values(),
            'datasets' => $chartData,
        ];
    }

    /**
     * 1期分（7月〜6月）の合計を計算
     */
    private function calculateFiscalYearTotal(FiscalYear $fiscalYear, array $metrics, $monthlyData): array
    {
        $totals = [];
        
        foreach ($metrics as $item) {
            $metric = $item['metric'];
            $total = 0;
            
            // 年度の開始月から終了月まで
            $startMonth = $fiscalYear->start_date->copy()->startOfMonth();
            $endMonth = $fiscalYear->end_date->copy()->endOfMonth();
            
            $results = $monthlyData->get($metric->id);
            if ($results) {
                foreach ($results as $result) {
                    if ($result->target_month >= $startMonth && $result->target_month <= $endMonth) {
                        if ($result->value !== null) {
                            $total += $result->value;
                        }
                    }
                }
            }
            
            $totals[$metric->id] = $total;
        }
        
        return $totals;
    }

    /**
     * 各月の値を取得（7月〜6月）
     */
    private function getMonthlyValues(FiscalYear $fiscalYear, array $metrics, $monthlyData): array
    {
        $monthlyValues = [];
        
        // 年度の各月を生成（7月〜6月）
        $currentMonth = $fiscalYear->start_date->copy()->startOfMonth();
        $endMonth = $fiscalYear->end_date->copy()->endOfMonth();
        $months = [];
        
        while ($currentMonth <= $endMonth) {
            $months[] = $currentMonth->copy();
            $currentMonth->addMonth();
        }
        
        foreach ($metrics as $item) {
            $metric = $item['metric'];
            $metricId = isset($metric->id) ? $metric->id : 0;
            $values = [];
            
            if ($metricId === 0) {
                foreach ($months as $month) {
                    $total = 0;
                    $hasAny = false;
                    foreach (array_slice($metrics, 1) as $otherItem) {
                        if (isset($otherItem['metric']->id)) {
                            $value = $this->getValueForMonth($otherItem['metric']->id, $month, $monthlyData);
                            if ($value !== null) {
                                $total += $value;
                                $hasAny = true;
                            }
                        }
                    }
                    $values[$month->format('n月')] = $hasAny ? $total : null;
                }
            } else {
                foreach ($months as $month) {
                    $values[$month->format('n月')] = $this->getValueForMonth($metricId, $month, $monthlyData);
                }
            }
            
            $monthlyValues[$metricId] = $values;
        }
        
        return $monthlyValues;
    }

    /**
     * 数量指標系のセグメント別に指標をグループ化
     */
    private function groupOperationMetricsBySegment(FiscalYear $fiscalYear, $monthlyData): array
    {
        // 数量指標系セグメント定義
        $segmentDefinitions = [
            '新規開業' => [
                'category' => '新規開業',
                'metrics' => [
                    ['name' => '企業向け弁当 開業店舗数', 'csv_name' => '開業店舗数', 'sub_category' => '企業向け弁当'],
                    ['name' => '企業向け弁当 開業枠数', 'csv_name' => '開業枠数', 'sub_category' => '企業向け弁当'],
                    ['name' => 'ファミリーデリ 開業店舗数（0円以外）', 'csv_name' => '開業店舗数（0円以外）', 'sub_category' => 'ファミリーデリ'],
                    ['name' => 'ファミリーデリ 開業枠数（0円以外）', 'csv_name' => '開業枠数（0円以外）', 'sub_category' => 'ファミリーデリ'],
                    ['name' => 'A型・B型申請 開業事業所数', 'csv_name' => '開業事業所数', 'sub_category' => 'A型・B型申請'],
                    ['name' => 'GH申請 開業棟数', 'csv_name' => '開業棟数', 'sub_category' => 'GH申請'],
                ],
            ],
            'ランニング' => [
                'category' => 'ランニング',
                'metrics' => [
                    ['name' => 'やどかり/はぐくみ 稼働店舗数', 'csv_name' => '稼働店舗数', 'sub_category' => 'やどかり/はぐくみ'],
                    ['name' => 'やどかり/はぐくみ 稼働枠数', 'csv_name' => '稼働枠数', 'sub_category' => 'やどかり/はぐくみ'],
                    ['name' => 'ファミリーデリ 稼働枠数', 'csv_name' => '稼働枠数', 'sub_category' => 'ファミリーデリ'],
                    ['name' => 'ファミリーデリ 半年経過枠数', 'csv_name' => '半年経過枠数', 'sub_category' => 'ファミリーデリ'],
                    ['name' => 'ファミリーデリ 1年以上経過枠数', 'csv_name' => '1年以上経過枠数', 'sub_category' => 'ファミリーデリ'],
                    ['name' => '福祉継続 AB型稼働事業所数', 'csv_name' => 'AB型稼働事業所数', 'sub_category' => '福祉継続'],
                    ['name' => '福祉継続 GH稼働棟数', 'csv_name' => 'GH稼働棟数', 'sub_category' => '福祉継続'],
                ],
            ],
            '商品卸' => [
                'category' => '商品卸',
                'metrics' => [
                    ['name' => '合計販売食数（千食）', 'csv_name' => '合計販売食数（千食）'],
                    ['name' => '法人向け食数（千食）', 'csv_name' => '法人向け食数（千食）'],
                    ['name' => '高齢者向け食数（千食）', 'csv_name' => '高齢者向け食数（千食）'],
                ],
            ],
        ];

        $segments = [];
        
        foreach ($segmentDefinitions as $segmentName => $definition) {
            $segmentMetrics = [];
            
            if ($definition['category']) {
                // 特定カテゴリから取得
                $rootCategory = \App\Models\Category::where('name', $definition['category'])
                    ->whereNull('parent_id')
                    ->first();
                
                if ($rootCategory) {
                    foreach ($definition['metrics'] as $metricDef) {
                        $csvName = is_array($metricDef) ? ($metricDef['csv_name'] ?? $metricDef['name']) : $metricDef;
                        $displayName = is_array($metricDef) ? $metricDef['name'] : $metricDef;
                        $subCategory = is_array($metricDef) ? ($metricDef['sub_category'] ?? null) : null;
                        
                        $metric = null;
                        
                        if ($subCategory) {
                            // 子カテゴリから取得
                            $subCategoryObj = \App\Models\Category::where('name', $subCategory)
                                ->where('parent_id', $rootCategory->id)
                                ->first();
                            
                            if ($subCategoryObj) {
                                $metric = Metric::where('category_id', $subCategoryObj->id)
                                    ->where('name', $csvName)
                                    ->first();
                            }
                        } else {
                            // ルートカテゴリから取得
                            $metric = Metric::where('category_id', $rootCategory->id)
                                ->where('name', $csvName)
                                ->first();
                        }
                        
                        if ($metric) {
                            // 表示名を上書き
                            $metric->display_name = $displayName;
                            
                            $data = $this->getLatestAndPrevious($metric->id, $fiscalYear->id, $monthlyData);
                            $segmentMetrics[] = [
                                'metric' => $metric,
                                'current' => $data['current'],
                                'previous' => $data['previous'],
                                'mom' => DashboardHelper::calculateMonthOverMonth($data['current'], $data['previous']),
                                'current_month' => $data['current_month'],
                            ];
                        }
                    }
                }
            }
            
            if (!empty($segmentMetrics)) {
                $chartData = $this->prepareSegmentChartData($fiscalYear, $monthlyData, $segmentName, $segmentMetrics);
                $fiscalYearTotal = $this->calculateFiscalYearTotal($fiscalYear, $segmentMetrics, $monthlyData);
                $monthlyValues = $this->getMonthlyValues($fiscalYear, $segmentMetrics, $monthlyData);
                
                $momLabel = null;
                foreach ($segmentMetrics as $item) {
                    if (isset($item['current_month']) && $item['current_month']) {
                        $momLabel = $item['current_month']->format('n月');
                        break;
                    }
                }
                
                $segments[] = [
                    'name' => $segmentName,
                    'metrics' => $segmentMetrics,
                    'chartData' => $chartData,
                    'fiscalYearTotal' => $fiscalYearTotal,
                    'monthlyValues' => $monthlyValues,
                    'momLabel' => $momLabel,
                ];
            }
        }
        
        return $segments;
    }
}

