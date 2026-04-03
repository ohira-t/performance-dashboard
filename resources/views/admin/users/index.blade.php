@extends('layouts.app')

@section('title', 'ユーザー管理')
@section('page-title', 'ユーザー管理')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-people"></i> ユーザー一覧
            </h5>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission('users.create'))
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus"></i> ユーザーを追加
                </a>
                @endif
            </div>
        </div>
        
        <!-- 検索フォーム -->
        <div class="card-body border-bottom bg-light py-2">
            <form method="GET" action="{{ route('admin.users.index') }}" id="userFilterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small mb-0">キーワード</label>
                        <input type="text" name="search" id="searchInput" value="{{ $search }}" placeholder="名前・メールで検索" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">役割</label>
                        <select name="role" class="form-select form-select-sm" onchange="document.getElementById('userFilterForm').submit();">
                            <option value="">すべて</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ $selectedRole === $role->name ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 d-flex align-items-center gap-3">
                        @php
                            $hasFilters = $search || $selectedRole;
                        @endphp
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm {{ !$hasFilters ? 'disabled' : '' }}">
                            <i class="bi bi-arrow-counterclockwise"></i> リセット
                        </a>
                        <span class="text-muted small">
                            <strong>{{ $users->total() }}</strong> 件
                        </span>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="white-space: nowrap;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 200px;">名前</th>
                            <th style="width: 250px;">メールアドレス</th>
                            <th style="width: 150px;">役割</th>
                            <th style="width: 100px;">ステータス</th>
                            <th style="width: 180px;">最終ログイン</th>
                            <th style="width: 150px;">作成日時</th>
                            <th style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td style="white-space: nowrap;">{{ $user->id }}</td>
                                <td style="white-space: nowrap;">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($user->avatar)
                                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 32px; height: 32px; flex-shrink: 0;">
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: var(--accent-color); color: white; font-size: 0.875rem; flex-shrink: 0;">
                                                {{ mb_substr($user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span style="white-space: nowrap;">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td style="white-space: nowrap;">
                                    {{ $user->email }}
                                    @if(!$user->google_id)
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem; font-weight: 400;" title="まだログインしていません">未ログイン</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <span class="badge" style="background-color: rgba(0, 122, 255, 0.1); color: var(--accent-color); font-size: 0.75rem; font-weight: 400; padding: 0.25em 0.5em; border-radius: 0.25rem; white-space: nowrap;">
                                                {{ $role->display_name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem; white-space: nowrap;">役割なし</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($user->is_active)
                                        <span class="badge bg-success" style="font-size: 0.75rem; font-weight: 400; white-space: nowrap;">有効</span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size: 0.75rem; font-weight: 400; white-space: nowrap;">無効</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($user->last_login_at)
                                        <span style="font-size: 0.875rem;">{{ $user->last_login_at->format('Y/m/d H:i') }}</span>
                                    @else
                                        <span class="text-muted" style="font-size: 0.875rem;">未ログイン</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.875rem; white-space: nowrap;">{{ $user->created_at->format('Y/m/d H:i') }}</td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> 編集
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    ユーザーが見つかりませんでした。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer" style="padding: 1rem 1.25rem; background-color: #f8f9fa; border-top: 1px solid rgba(0, 0, 0, 0.1);">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted me-3" style="font-size: 0.875rem; white-space: nowrap;">
                        {{ $users->firstItem() }} 〜 {{ $users->lastItem() }} / {{ $users->total() }}件
                    </div>
                    {{ $users->links('pagination.bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('userFilterForm');
    let originalValue = searchInput ? searchInput.value : '';
    
    if (searchInput && filterForm) {
        searchInput.addEventListener('blur', function() {
            if (this.value !== originalValue) {
                filterForm.submit();
            }
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterForm.submit();
            }
        });
    }
});
</script>
@endpush
@endsection

