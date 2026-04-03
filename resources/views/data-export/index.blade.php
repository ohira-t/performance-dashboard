@extends('layouts.app')

@section('title', 'データエクスポート')
@section('page-title', 'データエクスポート')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0" style="font-weight: 500; font-size: 1.25rem;">データエクスポート</h4>
        </div>
        <p class="text-muted mt-2 mb-0" style="font-size: 0.875rem;">
            各種データをCSV形式でダウンロードできます。開発・バックアップ用途にご利用ください。
        </p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-zip"></i>
                <h5 class="mb-0">全データエクスポート</h5>
            </div>
            <div class="card-body">
                <p class="mb-3" style="font-size: 0.875rem; color: var(--text-secondary);">
                    すべてのマスタデータと実績データをZIPファイルにまとめてダウンロードします。
                </p>
                <a href="{{ route('data-export.all') }}" class="btn btn-primary">
                    <i class="bi bi-download"></i> 全データをダウンロード（ZIP）
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-database"></i>
                <h5 class="mb-0">マスタデータ</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 25%;">データ名</th>
                                <th>説明</th>
                                <th style="width: 15%; text-align: right;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="bi bi-folder me-2" style="color: var(--accent-color);"></i>カテゴリー</td>
                                <td style="color: var(--text-secondary); font-size: 0.875rem;">指標を分類するカテゴリー情報</td>
                                <td class="text-end">
                                    <a href="{{ route('data-export.categories') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download"></i> CSV
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-list-ul me-2" style="color: var(--accent-color);"></i>指標マスタ</td>
                                <td style="color: var(--text-secondary); font-size: 0.875rem;">月次実績入力で使用する指標の定義</td>
                                <td class="text-end">
                                    <a href="{{ route('data-export.metrics') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download"></i> CSV
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-calendar3 me-2" style="color: var(--accent-color);"></i>年度マスタ</td>
                                <td style="color: var(--text-secondary); font-size: 0.875rem;">会計年度の定義</td>
                                <td class="text-end">
                                    <a href="{{ route('data-export.fiscal-years') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download"></i> CSV
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-people me-2" style="color: var(--accent-color);"></i>ユーザー</td>
                                <td style="color: var(--text-secondary); font-size: 0.875rem;">システムユーザー情報（パスワードは含まれません）</td>
                                <td class="text-end">
                                    <a href="{{ route('data-export.users') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download"></i> CSV
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-table"></i>
                <h5 class="mb-0">月次実績データ</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('data-export.monthly-results') }}" method="GET" class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label for="monthlyFiscalYear" style="font-size: 0.875rem; white-space: nowrap;">年度:</label>
                        <select name="fiscal_year_id" id="monthlyFiscalYear" class="form-select form-select-sm" style="width: auto;">
                            <option value="">すべての年度</option>
                            @foreach($fiscalYears as $fy)
                                <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download"></i> 月次実績をダウンロード
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-1"></i>注意事項</h6>
            <ul class="mb-0" style="font-size: 0.875rem; padding-left: 1.25rem;">
                <li>CSVファイルはUTF-8（BOM付き）形式で出力されます。Excelで開く場合はそのまま開けます。</li>
                <li>ユーザーデータにはパスワード情報は含まれません。</li>
                <li>大量のデータをエクスポートする場合、ダウンロードに時間がかかる場合があります。</li>
            </ul>
        </div>
    </div>
</div>
@endsection
