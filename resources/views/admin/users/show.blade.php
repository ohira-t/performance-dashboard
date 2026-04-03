@extends('layouts.app')

@section('title', 'ユーザー編集')
@section('page-title', 'ユーザー編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person"></i> {{ $user->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">名前</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">メールアドレス</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <small class="text-muted">メールアドレスはGoogleアカウントと連携しているため変更できません。</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    有効
                                </label>
                            </div>
                            <small class="text-muted">無効にすると、このユーザーはログインできなくなります。</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> 保存
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- カスタム権限設定 -->
            @php
                $primaryRole = $user->getPrimaryRole();
                $showCustomPermissions = $primaryRole && in_array($primaryRole->name, ['manager', 'user']);
                $customPerms = $user->custom_permissions ?? [];
            @endphp
            @if($showCustomPermissions)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-sliders"></i> 権限設定
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- アクセス可能な事業 -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">アクセス可能な事業</label>
                            <small class="text-muted d-block mb-2">チェックした事業のみ表示されます。チェックがない場合は全事業にアクセス可能です。</small>
                            <div class="row">
                                @foreach($businessUnits as $bu)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="business_units[]" value="{{ $bu->id }}" id="bu_{{ $bu->id }}"
                                            {{ isset($customPerms['business_units']) && in_array($bu->id, $customPerms['business_units']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="bu_{{ $bu->id }}">
                                            {{ $bu->display_name }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 管理項目の権限 -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">管理項目の権限</label>
                            <small class="text-muted d-block mb-2">各管理項目に対するアクセス権限を設定します。</small>
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>管理項目</th>
                                        <th class="text-center" style="width: 100px;">アクセス不可</th>
                                        <th class="text-center" style="width: 100px;">閲覧のみ</th>
                                        <th class="text-center" style="width: 100px;">フル操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(\App\Models\User::MANAGEMENT_RESOURCES as $resource => $label)
                                    @php
                                        $currentLevel = $customPerms[$resource] ?? 'full';
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="radio" name="permissions[{{ $resource }}]" value="none" id="{{ $resource }}_none"
                                                {{ $currentLevel === 'none' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="radio" name="permissions[{{ $resource }}]" value="view" id="{{ $resource }}_view"
                                                {{ $currentLevel === 'view' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="radio" name="permissions[{{ $resource }}]" value="full" id="{{ $resource }}_full"
                                                {{ $currentLevel === 'full' ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> 権限を保存
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check"></i> 役割の管理
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.roles.update', $user) }}" id="rolesForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">役割を選択</label>
                            @foreach($roles as $role)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="role_id" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                        {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        <strong>{{ $role->display_name }}</strong>
                                        @if($role->description)
                                            <br><small class="text-muted">{{ $role->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check"></i> 役割を更新
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> ユーザー情報
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt style="font-size: 0.875rem; color: var(--text-secondary);">Google ID</dt>
                        <dd style="font-size: 0.875rem; margin-bottom: 0.75rem;">{{ $user->google_id ?: '（未設定）' }}</dd>

                        <dt style="font-size: 0.875rem; color: var(--text-secondary);">最終ログイン</dt>
                        <dd style="font-size: 0.875rem; margin-bottom: 0.75rem;">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('Y年m月d日 H:i') }}
                            @else
                                <span class="text-muted">未ログイン</span>
                            @endif
                        </dd>

                        <dt style="font-size: 0.875rem; color: var(--text-secondary);">作成日時</dt>
                        <dd style="font-size: 0.875rem; margin-bottom: 0.75rem;">{{ $user->created_at->format('Y年m月d日 H:i') }}</dd>

                        <dt style="font-size: 0.875rem; color: var(--text-secondary);">更新日時</dt>
                        <dd style="font-size: 0.875rem;">{{ $user->updated_at->format('Y年m月d日 H:i') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- ユーザー削除 -->
            @if(auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
            <div class="card mt-3 border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <h5 class="mb-0 text-danger">
                        <i class="bi bi-exclamation-triangle"></i> 危険な操作
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">ユーザーを削除すると、元に戻すことはできません。</p>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('本当に「{{ $user->name }}」を削除しますか？\n\nこの操作は取り消せません。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-trash"></i> ユーザーを削除
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 役割が変更されたらページをリロード
    document.getElementById('rolesForm')?.addEventListener('submit', function(e) {
        // フォーム送信後にリロードするため、サーバーサイドでリダイレクト
    });
});
</script>
@endpush
@endsection
