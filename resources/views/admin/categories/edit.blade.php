@extends('layouts.app')

@section('title', 'カテゴリー編集')
@section('page-title', 'カテゴリー編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-folder"></i> カテゴリーの編集
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">カテゴリー名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">親カテゴリー</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">なし（ルートカテゴリー）</option>
                                @foreach($parentOptions as $option)
                                    <option value="{{ $option->id }}" {{ old('parent_id', $category->parent_id) == $option->id ? 'selected' : '' }}>
                                        {{ $option->getFullPath() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">親カテゴリーを変更すると、階層構造が変わります</small>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">並び順</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">数値が小さいほど上に表示されます</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> 更新
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection























