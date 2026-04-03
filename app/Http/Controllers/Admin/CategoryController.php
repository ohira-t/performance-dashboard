<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\SecurityHelper;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * カテゴリー一覧表示
     */
    public function index()
    {
        // ルートカテゴリーを取得（子カテゴリーも含む）
        $rootCategories = Category::getRootCategories()->load('children.metrics');
        
        return view('admin.categories.index', [
            'rootCategories' => $rootCategories,
        ]);
    }

    /**
     * カテゴリー作成フォーム表示
     */
    public function create(Request $request)
    {
        $parentId = $request->get('parent_id');
        $parentCategory = $parentId ? Category::find($parentId) : null;
        
        // 親カテゴリー候補（自分自身を除く）
        $parentOptions = Category::where('id', '!=', $parentId ?? 0)
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.create', [
            'parentCategory' => $parentCategory,
            'parentOptions' => $parentOptions,
        ]);
    }

    /**
     * カテゴリーの作成
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        if ($request->parent_id) {
            try {
                $parent = Category::find($request->parent_id);
                if ($parent) {
                    $this->checkCircularReference($parent, $request->parent_id);
                }
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['parent_id' => '循環参照が発生します。別の親カテゴリーを選択してください。']);
            }
        }

        // sort_orderが指定されていない場合は最大値+1
        if (!$request->has('sort_order') || $request->sort_order === null) {
            $maxSortOrder = Category::where('parent_id', $request->parent_id)
                ->max('sort_order') ?? 0;
            $request->merge(['sort_order' => $maxSortOrder + 1]);
        }

        $category = Category::create([
            'name' => SecurityHelper::sanitizeString($request->name),
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'カテゴリーを作成しました。');
    }

    /**
     * カテゴリー編集フォーム表示
     */
    public function edit(Category $category)
    {
        $category->load('parent', 'children', 'metrics');
        
        // 親カテゴリー候補（自分自身と子孫を除く）
        $parentOptions = Category::where('id', '!=', $category->id)
            ->whereDoesntHave('parent', function($query) use ($category) {
                $query->where('id', $category->id);
            })
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.edit', [
            'category' => $category,
            'parentOptions' => $parentOptions,
        ]);
    }

    /**
     * カテゴリーの更新
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        // 自分自身を親に設定しようとしている場合はエラー
        if ($request->parent_id == $category->id) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['parent_id' => '自分自身を親カテゴリーに設定することはできません。']);
        }

        // 循環参照チェック
        if ($request->parent_id) {
            try {
                $parent = Category::find($request->parent_id);
                if ($parent) {
                    $this->checkCircularReference($parent, $category->id);
                }
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['parent_id' => '循環参照が発生します。別の親カテゴリーを選択してください。']);
            }
        }

        $category->update([
            'name' => SecurityHelper::sanitizeString($request->name),
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? $category->sort_order,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'カテゴリーを更新しました。');
    }

    /**
     * カテゴリーの削除
     */
    public function destroy(Category $category)
    {
        // 子カテゴリーがある場合は削除不可
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', '子カテゴリーが存在するため削除できません。先に子カテゴリーを削除してください。');
        }

        // 指標が紐づいている場合は削除不可
        if ($category->metrics()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', '指標が紐づいているため削除できません。先に指標を削除または移動してください。');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'カテゴリーを削除しました。');
    }

    /**
     * 循環参照チェック
     */
    private function checkCircularReference(Category $parent, int $categoryId): void
    {
        $current = $parent;
        while ($current) {
            if ($current->id == $categoryId) {
                throw new \Exception('循環参照が発生します。');
            }
            $current = $current->parent;
        }
    }
}
