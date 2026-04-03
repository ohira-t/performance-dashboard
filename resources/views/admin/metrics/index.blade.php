@extends('layouts.app')

@section('title', '指標マスタ管理')
@section('page-title', '指標マスタ管理')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> 指標一覧
            </h5>
            @if(auth()->user()->hasPermission('master.metrics.create'))
            <a href="{{ route('admin.metrics.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> 指標を追加
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="mb-3">
                <form method="GET" action="{{ route('admin.metrics.index') }}" class="d-flex gap-2">
                    <select name="category_id" class="form-select form-select-sm" style="width: 250px;">
                        <option value="">全てのカテゴリー</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->getFullPath() }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" name="search" value="{{ $search }}" placeholder="指標名で検索" class="form-control form-control-sm" style="width: 200px;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    @if($selectedCategoryId || $search)
                        <a href="{{ route('admin.metrics.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x"></i> クリア
                        </a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" style="white-space: nowrap;">
                    <thead>
                        <tr>
                            <th style="width: 250px;">指標名</th>
                            <th style="width: 250px;">カテゴリー</th>
                            <th style="width: 120px;">タイプ</th>
                            <th style="width: 100px;">単位</th>
                            <th style="width: 100px;">並び順</th>
                            <th style="width: 150px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($metrics as $metric)
                            <tr>
                                <td style="white-space: nowrap;">
                                    <strong>{{ $metric->name }}</strong>
                                </td>
                                <td style="white-space: nowrap; font-size: 0.875rem;">
                                    <span class="text-muted">{{ $metric->category->getFullPath() }}</span>
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($metric->type === 'currency')
                                        <span class="badge" style="background-color: rgba(0, 122, 255, 0.1); color: var(--accent-color); font-size: 0.75rem; font-weight: 400;">金額</span>
                                    @elseif($metric->type === 'quantity')
                                        <span class="badge" style="background-color: rgba(255, 149, 0, 0.1); color: var(--warning-color); font-size: 0.75rem; font-weight: 400;">数量</span>
                                    @else
                                        <span class="badge" style="background-color: rgba(52, 199, 89, 0.1); color: var(--success-color); font-size: 0.75rem; font-weight: 400;">率</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap; font-size: 0.875rem;">
                                    {{ $metric->unit ?? '-' }}
                                </td>
                                <td style="white-space: nowrap; font-size: 0.875rem;">
                                    {{ $metric->sort_order }}
                                </td>
                                <td style="white-space: nowrap;">
                                    <div class="d-flex gap-1">
                                        @if(auth()->user()->hasPermission('master.metrics.update'))
                                        <a href="{{ route('admin.metrics.edit', $metric) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> 編集
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('master.metrics.delete'))
                                        <form method="POST" action="{{ route('admin.metrics.destroy', $metric) }}" style="display: inline;" onsubmit="return confirm('指標「{{ $metric->name }}」を削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> 削除
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    指標が見つかりませんでした。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($metrics->hasPages())
                <div class="mt-3">
                    {{ $metrics->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

