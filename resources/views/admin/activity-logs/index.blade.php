@extends('layouts.app')

@section('title', '操作ログ')
@section('page-title', '操作ログ')

@section('content')
<div class="container-fluid">
    <!-- フィルター -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-2 align-items-end">
                <div class="col-auto" style="min-width: 120px;">
                    <label class="form-label small mb-1">ユーザー</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">すべて</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto" style="min-width: 100px;">
                    <label class="form-label small mb-1">操作種別</label>
                    <select name="event" class="form-select form-select-sm">
                        <option value="">すべて</option>
                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>追加</option>
                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>更新</option>
                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>削除</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">開始</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" style="width: 140px;">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">終了</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" style="width: 140px;">
                </div>
                <div class="col-auto" style="min-width: 150px;">
                    <label class="form-label small mb-1">キーワード</label>
                    <input type="text" name="keyword" class="form-control form-control-sm" value="{{ request('keyword') }}" placeholder="指標名など">
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                        <i class="bi bi-search me-1"></i>検索
                    </button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                        <i class="bi bi-x-lg me-1"></i>クリア
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ログ一覧 -->
    <div class="card">
        <div class="card-header bg-white">
            <span class="text-muted small">
                {{ $activities->total() }}件中 {{ $activities->firstItem() ?? 0 }}〜{{ $activities->lastItem() ?? 0 }}件を表示
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 160px;">日時</th>
                        <th style="width: 120px;">ユーザー</th>
                        <th style="width: 80px;">操作</th>
                        <th>内容</th>
                        <th style="width: 200px;">変更詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td class="text-muted">
                                {{ $activity->created_at->timezone('Asia/Tokyo')->format('Y/m/d H:i:s') }}
                            </td>
                            <td>
                                @if($activity->causer)
                                    {{ $activity->causer->name }}
                                @else
                                    <span class="text-muted">システム</span>
                                @endif
                            </td>
                            <td>
                                @switch($activity->event)
                                    @case('created')
                                        <span class="badge bg-success">追加</span>
                                        @break
                                    @case('updated')
                                        <span class="badge bg-primary">更新</span>
                                        @break
                                    @case('deleted')
                                        <span class="badge bg-danger">削除</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $activity->event }}</span>
                                @endswitch
                            </td>
                            <td>{{ $activity->description }}</td>
                            <td>
                                @if($activity->properties && ($activity->properties['old'] ?? null) !== null)
                                    @php
                                        $old = $activity->properties['old'] ?? [];
                                        $new = $activity->properties['attributes'] ?? [];
                                    @endphp
                                    <small class="text-muted">
                                        @foreach($new as $key => $value)
                                            @if(isset($old[$key]) && $old[$key] != $value)
                                                <span class="d-block">
                                                    {{ $key }}: 
                                                    <span class="text-danger">{{ $old[$key] ?? '(空)' }}</span>
                                                    →
                                                    <span class="text-success">{{ $value ?? '(空)' }}</span>
                                                </span>
                                            @elseif(!isset($old[$key]))
                                                <span class="d-block">
                                                    {{ $key }}: 
                                                    <span class="text-success">{{ $value ?? '(空)' }}</span>
                                                </span>
                                            @endif
                                        @endforeach
                                    </small>
                                @elseif($activity->event === 'created' && isset($activity->properties['attributes']))
                                    <small class="text-muted">
                                        @foreach($activity->properties['attributes'] as $key => $value)
                                            @if($value !== null)
                                                <span class="d-block">{{ $key }}: {{ $value }}</span>
                                            @endif
                                        @endforeach
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                ログがありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())
            <div class="card-footer bg-white">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

