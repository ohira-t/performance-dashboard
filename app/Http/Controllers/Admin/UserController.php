<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\SecurityHelper;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * ユーザー作成フォーム表示
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', [
            'roles' => $roles,
        ]);
    }

    /**
     * ユーザーの作成（事前登録）
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'email' => SecurityHelper::sanitizeString($request->email),
            'name' => SecurityHelper::sanitizeString($request->name),
            'google_id' => null, // ログイン時に設定される
            'is_active' => true,
        ]);

        // 役割を割り当て
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'ユーザーを事前登録しました。このユーザーは次回ログイン時にシステムに追加されます。');
    }

    /**
     * ユーザー一覧表示
     */
    public function index(Request $request)
    {
        $query = User::with('roles')->orderBy('created_at', 'desc');

        // 検索機能
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 役割でフィルタ
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->paginate(20);
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'search' => $request->search ?? '',
            'selectedRole' => $request->role ?? '',
        ]);
    }

    /**
     * ユーザー詳細表示
     */
    public function show(User $user)
    {
        $user->load('roles');
        $roles = Role::orderBy('name')->get();
        $businessUnits = \App\Models\BusinessUnit::orderBy('sort_order')->get();

        return view('admin.users.show', [
            'user' => $user,
            'roles' => $roles,
            'businessUnits' => $businessUnits,
        ]);
    }

    /**
     * ユーザー情報の更新
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => SecurityHelper::sanitizeString($request->name),
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'ユーザー情報を更新しました。');
    }

    /**
     * ユーザーの役割を更新
     */
    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->roles()->sync([$request->role_id]);

        // 役割が変更されたら権限設定をリセット
        if (!in_array($user->getPrimaryRole()?->name, ['manager', 'user'])) {
            $user->update(['custom_permissions' => null]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', '役割を更新しました。');
    }

    /**
     * ユーザーの権限設定を更新
     */
    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'business_units' => 'array',
            'business_units.*' => 'exists:business_units,id',
            'permissions' => 'array',
            'permissions.*' => 'in:none,view,full',
        ]);

        $customPermissions = [];

        // 事業アクセス権限
        if ($request->has('business_units') && !empty($request->business_units)) {
            $customPermissions['business_units'] = array_map('intval', $request->business_units);
        }

        // 管理項目権限
        if ($request->has('permissions')) {
            foreach ($request->permissions as $resource => $level) {
                if (array_key_exists($resource, User::MANAGEMENT_RESOURCES)) {
                    $customPermissions[$resource] = $level;
                }
            }
        }

        $user->update([
            'custom_permissions' => !empty($customPermissions) ? $customPermissions : null,
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', '権限設定を更新しました。');
    }

    /**
     * ユーザーの削除
     */
    public function destroy(User $user)
    {
        // 自分自身は削除不可
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.show', $user)
                ->with('error', '自分自身を削除することはできません。');
        }

        $userName = $user->name;
        $user->roles()->detach();
        $user->delete();

        activity()
            ->withProperties(['deleted_user' => $userName])
            ->log("ユーザー「{$userName}」を削除しました");

        return redirect()->route('admin.users.index')
            ->with('success', "ユーザー「{$userName}」を削除しました。");
    }
}
