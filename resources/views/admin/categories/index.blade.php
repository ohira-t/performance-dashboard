@extends('layouts.app')

@section('title', 'カテゴリー管理')
@section('page-title', 'カテゴリー管理')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-folder"></i> カテゴリー一覧
            </h5>
            @if(auth()->user()->hasPermission('master.categories.create'))
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> カテゴリーを追加
            </a>
            @endif
        </div>
        <div class="card-body">
            @if($rootCategories->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 300px;">カテゴリー名</th>
                                <th style="width: 200px;">階層パス</th>
                                <th style="width: 100px;">子カテゴリー数</th>
                                <th style="width: 100px;">指標数</th>
                                <th style="width: 100px;">並び順</th>
                                <th style="width: 150px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rootCategories as $rootCategory)
                                @include('admin.categories._category_row', ['category' => $rootCategory, 'level' => 0])
                                
                                @foreach($rootCategory->children as $childCategory)
                                    @include('admin.categories._category_row', ['category' => $childCategory, 'level' => 1])
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    カテゴリーが登録されていません。
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

