<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\SecurityHelper;
use App\Models\Metric;
use App\Models\Category;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    /**
     * 指標一覧表示
     */
    public function index(Request $request)
    {
        $query = Metric::with('category')->orderBy('category_id')->orderBy('sort_order');

        // カテゴリーでフィルタ
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // 検索機能
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $metrics = $query->paginate(50);
        $categories = Category::orderBy('name')->get();

        return view('admin.metrics.index', [
            'metrics' => $metrics,
            'categories' => $categories,
            'selectedCategoryId' => $request->category_id ?? '',
            'search' => $request->search ?? '',
        ]);
    }

    /**
     * 指標作成フォーム表示
     */
    public function create(Request $request)
    {
        $categoryId = $request->get('category_id');
        $category = $categoryId ? Category::find($categoryId) : null;
        
        $categories = Category::orderBy('name')->get();

        return view('admin.metrics.create', [
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    /**
     * 指標の作成
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:currency,quantity,percent',
            'unit' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // sort_orderが指定されていない場合は最大値+1
        if (!$request->has('sort_order') || $request->sort_order === null) {
            $maxSortOrder = Metric::where('category_id', $request->category_id)
                ->max('sort_order') ?? 0;
            $request->merge(['sort_order' => $maxSortOrder + 1]);
        }

        $metric = Metric::create([
            'name' => SecurityHelper::sanitizeString($request->name),
            'category_id' => $request->category_id,
            'type' => $request->type,
            'unit' => SecurityHelper::sanitizeString($request->unit),
            'sort_order' => $request->sort_order,
        ]);

        return redirect()->route('admin.metrics.index', ['category_id' => $request->category_id])
            ->with('success', '指標を作成しました。');
    }

    /**
     * 指標編集フォーム表示
     */
    public function edit(Metric $metric)
    {
        $metric->load('category');
        $categories = Category::orderBy('name')->get();

        return view('admin.metrics.edit', [
            'metric' => $metric,
            'categories' => $categories,
        ]);
    }

    /**
     * 指標の更新
     */
    public function update(Request $request, Metric $metric)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:currency,quantity,percent',
            'unit' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $metric->update([
            'name' => SecurityHelper::sanitizeString($request->name),
            'category_id' => $request->category_id,
            'type' => $request->type,
            'unit' => SecurityHelper::sanitizeString($request->unit),
            'sort_order' => $request->sort_order ?? $metric->sort_order,
        ]);

        return redirect()->route('admin.metrics.index', ['category_id' => $request->category_id])
            ->with('success', '指標を更新しました。');
    }

    /**
     * 指標の削除
     */
    public function destroy(Metric $metric)
    {
        // 月次実績が紐づいている場合は削除不可
        if ($metric->monthlyResults()->count() > 0) {
            return redirect()->route('admin.metrics.index')
                ->with('error', '月次実績が紐づいているため削除できません。先に月次実績を削除してください。');
        }

        $categoryId = $metric->category_id;
        $metric->delete();

        return redirect()->route('admin.metrics.index', ['category_id' => $categoryId])
            ->with('success', '指標を削除しました。');
    }
}
