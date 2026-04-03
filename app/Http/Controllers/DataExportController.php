<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Metric;
use App\Models\MonthlyResult;
use App\Models\FiscalYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DataExportController extends Controller
{
    public function index()
    {
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();
        return view('data-export.index', compact('fiscalYears'));
    }

    public function exportAll(Request $request)
    {
        $zip = new \ZipArchive();
        $zipFileName = 'all_data_export_' . date('Y-m-d_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'ZIPファイルの作成に失敗しました。');
        }

        $zip->addFromString('categories.csv', $this->generateCategoriesCsv());
        $zip->addFromString('metrics.csv', $this->generateMetricsCsv());
        $zip->addFromString('fiscal_years.csv', $this->generateFiscalYearsCsv());
        $zip->addFromString('monthly_results.csv', $this->generateMonthlyResultsCsv());
        $zip->addFromString('users.csv', $this->generateUsersCsv());

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function exportCategories()
    {
        return $this->downloadCsv($this->generateCategoriesCsv(), 'categories_' . date('Y-m-d_His') . '.csv');
    }

    public function exportMetrics()
    {
        return $this->downloadCsv($this->generateMetricsCsv(), 'metrics_' . date('Y-m-d_His') . '.csv');
    }

    public function exportFiscalYears()
    {
        return $this->downloadCsv($this->generateFiscalYearsCsv(), 'fiscal_years_' . date('Y-m-d_His') . '.csv');
    }

    public function exportMonthlyResults(Request $request)
    {
        $fiscalYearId = $request->input('fiscal_year_id');
        $csv = $this->generateMonthlyResultsCsv($fiscalYearId);
        $suffix = $fiscalYearId ? '_fy' . $fiscalYearId : '';
        return $this->downloadCsv($csv, 'monthly_results' . $suffix . '_' . date('Y-m-d_His') . '.csv');
    }

    public function exportUsers()
    {
        return $this->downloadCsv($this->generateUsersCsv(), 'users_' . date('Y-m-d_His') . '.csv');
    }

    private function generateCategoriesCsv(): string
    {
        $categories = Category::with('parent')->orderBy('sort_order')->get();
        $lines = [['ID', '親カテゴリーID', '親カテゴリー名', 'カテゴリー名', '表示順', '作成日時', '更新日時']];
        foreach ($categories as $category) {
            $lines[] = [
                $category->id, $category->parent_id ?? '', $category->parent ? $category->parent->name : '',
                $category->name, $category->sort_order,
                $category->created_at->format('Y-m-d H:i:s'), $category->updated_at->format('Y-m-d H:i:s'),
            ];
        }
        return $this->arrayToCsv($lines);
    }

    private function generateMetricsCsv(): string
    {
        $metrics = Metric::with('category.parent')->orderBy('sort_order')->get();
        $lines = [['ID', 'カテゴリーID', 'カテゴリー名', '親カテゴリー名', '指標名', '表示名', 'タイプ', '表示順', '有効', '作成日時', '更新日時']];
        foreach ($metrics as $metric) {
            $lines[] = [
                $metric->id, $metric->category_id, $metric->category ? $metric->category->name : '',
                $metric->category && $metric->category->parent ? $metric->category->parent->name : '',
                $metric->name, $metric->display_name ?? '', $metric->type, $metric->sort_order,
                $metric->is_active ? '有効' : '無効',
                $metric->created_at->format('Y-m-d H:i:s'), $metric->updated_at->format('Y-m-d H:i:s'),
            ];
        }
        return $this->arrayToCsv($lines);
    }

    private function generateFiscalYearsCsv(): string
    {
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();
        $lines = [['ID', '年度名', '開始日', '終了日', '有効', '作成日時', '更新日時']];
        foreach ($fiscalYears as $fy) {
            $lines[] = [
                $fy->id, $fy->name, $fy->start_date, $fy->end_date, $fy->is_active ? '有効' : '無効',
                $fy->created_at->format('Y-m-d H:i:s'), $fy->updated_at->format('Y-m-d H:i:s'),
            ];
        }
        return $this->arrayToCsv($lines);
    }

    private function generateMonthlyResultsCsv(?int $fiscalYearId = null): string
    {
        $query = MonthlyResult::with(['metric.category.parent', 'fiscalYear']);
        if ($fiscalYearId) {
            $query->where('fiscal_year_id', $fiscalYearId);
        }
        $results = $query->orderBy('target_month')->orderBy('metric_id')->get();
        $lines = [['ID', '年度ID', '年度名', '指標ID', '指標名', 'カテゴリー', '親カテゴリー', '対象月', '値', '作成日時', '更新日時']];
        foreach ($results as $result) {
            $lines[] = [
                $result->id, $result->fiscal_year_id, $result->fiscalYear ? $result->fiscalYear->name : '',
                $result->metric_id, $result->metric ? $result->metric->name : '',
                $result->metric && $result->metric->category ? $result->metric->category->name : '',
                $result->metric && $result->metric->category && $result->metric->category->parent ? $result->metric->category->parent->name : '',
                $result->target_month, $result->value,
                $result->created_at->format('Y-m-d H:i:s'), $result->updated_at->format('Y-m-d H:i:s'),
            ];
        }
        return $this->arrayToCsv($lines);
    }

    private function generateUsersCsv(): string
    {
        $users = User::with('roles')->orderBy('id')->get();
        $lines = [['ID', '名前', 'メールアドレス', 'ロール', '最終ログイン', '作成日時', '更新日時']];
        foreach ($users as $user) {
            $lines[] = [
                $user->id, $user->name, $user->email, $user->roles->pluck('name')->implode(', '),
                $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '',
                $user->created_at->format('Y-m-d H:i:s'), $user->updated_at->format('Y-m-d H:i:s'),
            ];
        }
        return $this->arrayToCsv($lines);
    }

    private function arrayToCsv(array $lines): string
    {
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        foreach ($lines as $line) {
            fputcsv($output, $line);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    private function downloadCsv(string $csv, string $filename)
    {
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
