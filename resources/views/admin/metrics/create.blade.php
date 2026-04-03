@extends('layouts.app')

@section('title', '指標追加')
@section('page-title', '指標追加')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 指標の追加
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.metrics.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">指標名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">カテゴリー <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">選択してください</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $category?->id) == $cat->id ? 'selected' : '' }}>
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
                                <option value="currency" {{ old('type') == 'currency' ? 'selected' : '' }}>金額 (currency)</option>
                                <option value="quantity" {{ old('type') == 'quantity' ? 'selected' : '' }}>数量 (quantity)</option>
                                <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>率 (percent)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">金額: 売上、費用など / 数量: 店舗数、食数など / 率: 割合、パーセンテージなど</small>
                        </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">単位</label>
                            <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit') }}" placeholder="例: 円, 店, 食, %">
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">表示用の単位を入力してください（任意）</small>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">並び順</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order') }}" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">数値が小さいほど上に表示されます。未指定の場合は最後に追加されます</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> 作成
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























