@extends('layouts.app')

@section('title', '指標編集')
@section('page-title', '指標編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 指標の編集
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.metrics.update', $metric) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">指標名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $metric->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">カテゴリー <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">選択してください</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $metric->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->getFullPath() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">タイプ <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">選択してください</option>
                                <option value="currency" {{ old('type', $metric->type) == 'currency' ? 'selected' : '' }}>金額 (currency)</option>
                                <option value="quantity" {{ old('type', $metric->type) == 'quantity' ? 'selected' : '' }}>数量 (quantity)</option>
                                <option value="percent" {{ old('type', $metric->type) == 'percent' ? 'selected' : '' }}>率 (percent)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">単位</label>
                            <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', $metric->unit) }}" placeholder="例: 円, 店, 食, %">
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">並び順</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $metric->sort_order) }}" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> 更新
                            </button>
                            <a href="{{ route('admin.metrics.index') }}" class="btn btn-outline-secondary">
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























