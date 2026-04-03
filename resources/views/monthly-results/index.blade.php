@extends('layouts.app')

@section('title', '月次入力')
@section('page-title', '月次実績入力')

@section('content')
@if(!$fiscalYear)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> アクティブな年度が設定されていません。
    </div>
@else
    <!-- ヘッダー -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted" style="font-size: 0.875rem;">
                    {{ $fiscalYear->name }}
                </span>
                <div class="d-flex align-items-center gap-3">
                    @if(isset($fiscalYears) && $fiscalYears->count() > 0)
                        <form method="GET" action="{{ route('monthly-results.index') }}" class="mb-0" id="fiscalYearForm" onsubmit="saveAllInputValuesBeforeSubmit(event); return true;">
                            <select name="fiscal_year_id" onchange="saveAllInputValuesBeforeSubmit(event); setTimeout(function() { this.form.submit(); }.bind(this), 50);" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem; padding: 0.375rem 2rem 0.375rem 0.75rem; border: 0.5px solid var(--border-color); border-radius: 0.375rem;">
                                @foreach($fiscalYears as $fy)
                                    <option value="{{ $fy->id }}" {{ ($fiscalYear && $fiscalYear->id === $fy->id) ? 'selected' : '' }}>
                                        {{ $fy->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <button type="button" id="saveAllBtn" class="btn btn-primary btn-sm" onclick="saveAll()" style="font-size: 0.875rem; padding: 0.375rem 1.25rem;">
                        <i class="bi bi-check-all"></i> 全て保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        $months = [];
        $startDate = \Carbon\Carbon::parse($fiscalYear->start_date);
        $endDate = \Carbon\Carbon::parse($fiscalYear->end_date);
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $months[] = $current->format('Y-m-01');
            $current->addMonth();
        }
        
        // カテゴリ名のマッピング（表示名）
        $categoryDisplayNames = [
            '全体' => '全体',
            '新規開業' => '新規開業',
            'ランニング' => 'ランニング',
            '商品卸' => '商品卸',
            'その他売上' => '新規事業その他',
            'その他指標' => 'その他指標',
        ];
    @endphp

    <!-- カテゴリごとのボックス -->
    @foreach($metrics as $categoryName => $categoryMetrics)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-layers"></i>
                                <h5 class="mb-0">{{ $categoryDisplayNames[$categoryName] ?? $categoryName }}</h5>
                            </div>
                            <button type="button" class="btn btn-success btn-sm js-save-category-btn" data-category="{{ $categoryName }}" style="font-size: 0.875rem; padding: 0.375rem 1rem;">
                                <i class="bi bi-check-lg"></i> 保存
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive input-grid" style="overflow-x: auto; overflow-y: visible; max-height: none !important;">
                            <table class="table table-sm mb-0" style="border: none;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 200px; position: sticky; left: 0; z-index: 12; background-color: #fafafa; font-size: 0.875rem; font-weight: 500;">指標名</th>
                                        @foreach($months as $month)
                                            <th style="min-width: 120px; font-size: 0.875rem; font-weight: 500;">
                                                {{ \Carbon\Carbon::parse($month)->format('Y年n月') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categoryMetrics as $metric)
                                        <tr>
                                            <td style="font-weight: 400; position: sticky; left: 0; z-index: 11; background-color: #ffffff; font-size: 0.875rem; vertical-align: top; padding-top: 0.75rem;">
                                                @php
                                                    $displayName = isset($metric->display_name) ? $metric->display_name : $metric->name;
                                                    $hasSubCategory = $metric->category->name !== $categoryName;
                                                    $fullDisplayName = $hasSubCategory ? $metric->category->name . ' > ' . $displayName : $displayName;
                                                @endphp
                                                <div style="line-height: 1.4;">
                                                    @if($hasSubCategory)
                                                        <div style="margin-bottom: 0.25rem;">
                                                            <span class="badge" style="background-color: #f5f5f7; color: #86868b; font-size: 0.6875rem; font-weight: 400; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                                                                {{ $metric->category->name }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <a href="javascript:void(0);" onclick="openMetricChartModal({{ $metric->id }}, '{{ $fullDisplayName }}')" style="color: #1d1d1f; text-decoration: none; cursor: pointer; display: inline-block;">
                                                            {{ $displayName }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            @foreach($months as $month)
                                                @php
                                                    $resultKey = $metric->id . '_' . $month;
                                                    $result = $monthlyResults->get($resultKey);
                                                @endphp
                                                <td>
                                                    <div class="d-flex flex-column gap-1">
                                                        <input 
                                                            type="text" 
                                                            class="form-control form-control-sm value-input currency-input" 
                                                            data-metric-id="{{ $metric->id }}"
                                                            data-month="{{ $month }}"
                                                            data-metric-type="{{ $metric->type }}"
                                                            data-category-name="{{ $categoryName }}"
                                                            value="{{ $result && $result->value !== null ? number_format($result->value, 0) : '' }}"
                                                            style="width: 100px; padding: 0.25rem 0.5rem; font-size: 0.875rem;"
                                                        >
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-sm detail-btn"
                                                            onclick="openDetailModal({{ $metric->id }}, '{{ $month }}', {{ $result ? $result->id : 'null' }})"
                                                            title="詳細"
                                                            style="font-size: 0.75rem; padding: 0.125rem 0.5rem; background-color: rgba(0, 122, 255, 0.1); border: none; color: #0071E3; white-space: nowrap; align-self: flex-start;"
                                                            data-metric-id="{{ $metric->id }}"
                                                            data-month="{{ $month }}"
                                                            data-result-id="{{ $result ? $result->id : '' }}"
                                                        >
                                                            詳細
                                                        </button>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0" style="padding: 0.75rem 1rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-info-circle"></i> 入力後「保存」ボタンで確定 / 未保存の変更は<span style="border-left: 3px solid #ff9800; padding-left: 4px; background: #fff8e1; padding: 1px 4px;">黄色</span>で表示</small>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm js-csv-download-btn" data-category="{{ $categoryName }}" data-type="simple" style="font-size: 0.875rem;">
                                    <i class="bi bi-download"></i> CSVダウンロード
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm js-csv-download-btn" data-category="{{ $categoryName }}" data-type="detail" style="font-size: 0.875rem;">
                                    <i class="bi bi-download"></i> CSVダウンロード（詳細）
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm js-csv-import-btn" data-category="{{ $categoryName }}" style="font-size: 0.875rem;">
                                    <i class="bi bi-upload"></i> CSVインポート
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- 根拠実績詳細モーダル -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div>
                        <h5 class="modal-title mb-0" id="detailModalCategory"></h5>
                        <small class="text-muted" id="detailModalMetric"></small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeMonth(-1)" id="prevMonthBtn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                <i class="bi bi-chevron-left" style="font-size: 0.75rem;"></i>
                            </button>
                            <span id="detailModalMonth" class="fw-normal" style="font-size: 0.875rem;"></span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeMonth(1)" id="nextMonthBtn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                            </button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 0.75rem;"></button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="card" style="background-color: #fff5f5; border: none;">
                        <div class="card-body py-2 px-3">
                            <small class="text-muted d-block">合計</small>
                            <strong style="color: #c53030;" id="detailModalTotal">0円</strong>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addDetailRow()">
                        <i class="bi bi-plus-circle"></i> 行を追加
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="detailTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60%;">詳細</th>
                                <th style="width: 30%;">金額</th>
                                <th style="width: 10%;">操作</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            <!-- 動的に追加 -->
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <label for="detailModalComment" class="form-label" style="font-size: 0.875rem; font-weight: 500;">備考</label>
                    <textarea 
                        class="form-control" 
                        id="detailModalComment" 
                        rows="3"
                        style="font-size: 0.875rem;"
                    ></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveDetails()">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- 指標グラフモーダル -->
<div class="modal fade" id="metricChartModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="metricChartModalTitle">指標グラフ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 0.75rem;"></button>
            </div>
            <div class="modal-body">
                <div style="height: 400px;">
                    <canvas id="metricChartCanvas"></canvas>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<!-- CSVインポートモーダル -->
<div class="modal fade" id="csvImportModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="csvImportModalTitle">CSVインポート</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 0.75rem;"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="csvFileInput" class="form-label">CSVファイルを選択</label>
                    <input type="file" class="form-control" id="csvFileInput" accept=".csv,.txt">
                    <small class="form-text text-muted">形式: 指標名,年月,値 または 指標名,年月,詳細,金額</small>
                </div>
                
                <div id="csvPreviewArea" style="display: none;">
                    <h6 class="mb-2">プレビュー</h6>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-bordered" id="csvPreviewTable">
                            <thead class="table-light" style="position: sticky; top: 0;">
                                <tr>
                                    <th style="width: 5%;">行</th>
                                    <th style="width: 20%;">指標名</th>
                                    <th style="width: 15%;">年月</th>
                                    <th style="width: 20%;">詳細</th>
                                    <th style="width: 15%;">金額/値</th>
                                    <th style="width: 25%;">状態</th>
                                </tr>
                            </thead>
                            <tbody id="csvPreviewBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="csvErrorArea" style="display: none;" class="mt-3">
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">エラーが検出されました</h6>
                        <ul id="csvErrorList" class="mb-0">
                        </ul>
                    </div>
                </div>
                
                <div id="csvSuccessArea" style="display: none;" class="mt-3">
                    <div class="alert alert-success">
                        <h6 class="alert-heading">バリデーション成功</h6>
                        <p class="mb-0" id="csvSuccessMessage"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="csvImportExecuteBtn" disabled>インポート実行</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .input-grid {
        overflow-x: auto !important;
        overflow-y: visible !important;
        max-height: none !important;
        height: auto !important;
    }
    
    .input-grid.table-responsive {
        max-height: none !important;
        overflow-y: visible !important;
    }
    
    .input-grid table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .input-grid tbody tr:hover {
        background-color: #f7fafc;
    }
    
    .input-grid input:focus {
        background-color: #ffffff;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 0.2rem rgba(0, 122, 255, 0.1);
    }
    
    /* 値が空の入力欄は薄いグレー背景 */
    .value-input.is-empty {
        background-color: #f5f5f7 !important;
    }
    .value-input:not(.is-empty) {
        background-color: #ffffff !important;
    }
    
    /* 未保存の入力フィールドのスタイル */
    .value-input.unsaved {
        border-left: 3px solid #ff9800 !important;
        background-color: #fff8e1 !important;
    }
    .value-input.unsaved:focus {
        border-left: 3px solid #ff9800 !important;
        box-shadow: 0 0 0 0.15rem rgba(255, 152, 0, 0.25);
    }
    
    .input-grid .detail-btn:hover {
        background-color: rgba(0, 122, 255, 0.15) !important;
    }
    
    /* 詳細モーダルのスタイル */
    #detailModal .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 0.75rem 1rem;
    }
    
    #detailModal .modal-body {
        padding: 0.75rem 1rem;
    }
    
    #detailModal .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 0.75rem 1rem;
    }
    
    #detailModal .card {
        min-width: 150px;
    }
    
    #detailModal .table-responsive {
        margin: 0;
    }
    
    #detailModal .table {
        margin-bottom: 0;
    }
    
    #detailModal .table th,
    #detailModal .table td {
        padding: 0.5rem 0.75rem;
    }
    
    #detailTable th {
        font-weight: 500;
        font-size: 0.875rem;
    }
    
    #detailTable input {
        font-size: 0.875rem;
    }
    
    .input-grid .table th {
        font-weight: 500;
        font-size: 0.875rem;
        background-color: #fafafa;
        border: none;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .input-grid .table td {
        font-size: 0.875rem;
        border: none;
        border-bottom: 1px solid #e5e7eb;
        padding: 0.5rem;
    }
    
    .input-grid .table tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* 指標名リンクのスタイル */
    .input-grid .table td a {
        color: #1d1d1f;
        text-decoration: none;
        transition: text-decoration 0.2s ease;
    }
    
    .input-grid .table td a:hover {
        color: #1d1d1f;
        text-decoration: underline;
    }
    
    /* 指標グラフモーダルのスタイル */
    #metricChartModal .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 0.75rem 1rem;
    }
    
    #metricChartModal .modal-body {
        padding: 1rem;
    }
    
    #metricChartModal .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 0.75rem 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
const fiscalYearId = {{ $fiscalYear->id ?? 'null' }};
let currentMetricId = null;
let currentMonth = null;
let currentMonthlyResultId = null;
let currentMonthDate = null;
let detailRowIndex = 0;
let metricChart = null;

// === 変更追跡・保存システム ===
window.changedInputs = new Map();

// 入力のユニークキーを取得
function getInputKey(input) {
    return `${input.dataset.metricId}_${input.dataset.month}`;
}

// カテゴリごとの未保存カウントを更新
function updateCategoryUnsavedCount(categoryName) {
    const saveBtn = document.querySelector(`.js-save-category-btn[data-category="${categoryName}"]`);
    if (!saveBtn) return;
    
    let count = 0;
    window.changedInputs.forEach((data, key) => {
        if (data.category === categoryName) {
            count++;
        }
    });
    
    if (count > 0) {
        saveBtn.innerHTML = `<i class="bi bi-check-lg"></i> 保存 <span class="badge bg-warning text-dark ms-1">${count}</span>`;
        saveBtn.classList.remove('btn-success');
        saveBtn.classList.add('btn-warning');
    } else {
        saveBtn.innerHTML = `<i class="bi bi-check-lg"></i> 保存`;
        saveBtn.classList.remove('btn-warning');
        saveBtn.classList.add('btn-success');
    }
}

// 変更をマーク
function markAsChanged(input) {
    const key = getInputKey(input);
    const categoryName = input.dataset.categoryName;
    if (!window.changedInputs.has(key)) {
        window.changedInputs.set(key, { 
            input, 
            originalValue: input.dataset.originalValue,
            category: categoryName
        });
    }
    input.classList.add('unsaved');
    updateCategoryUnsavedCount(categoryName);
}

// 保存成功後にマークを解除
function markAsSaved(input) {
    const key = getInputKey(input);
    const categoryName = input.dataset.categoryName;
    window.changedInputs.delete(key);
    input.classList.remove('unsaved');
    input.dataset.originalValue = input.value;
    updateCategoryUnsavedCount(categoryName);
}

// 単一入力の保存
function saveInputValue(input, showFeedback = true) {
    const metricId = input.dataset.metricId;
    const month = input.dataset.month;
    const value = input.value.trim();
    
    let numValue = null;
    if (value !== '') {
        numValue = parseCurrency(value);
    }
    
    // 保存中のスタイル
    if (showFeedback) {
        input.style.backgroundColor = '#fffde7';
    }
    
    return axios.post('{{ route("monthly-results.update") }}', {
        fiscal_year_id: fiscalYearId,
        metric_id: metricId,
        target_month: month,
        value: numValue
    })
    .then(function(response) {
        markAsSaved(input);
        if (showFeedback) {
            input.style.backgroundColor = '#e8f5e9';
            setTimeout(() => {
                input.style.backgroundColor = '';
                updateEmptyClass(input);
            }, 800);
        }
        return true;
    })
    .catch(function(error) {
        console.error('Error:', error);
        if (showFeedback) {
            input.style.backgroundColor = '#ffebee';
            setTimeout(() => {
                input.style.backgroundColor = '';
                updateEmptyClass(input);
            }, 2000);
        }
        return false;
    });
}

// 入力値を保存
let savedInputValues = {};

function saveInputValues() {
    savedInputValues = {};
    document.querySelectorAll('.value-input').forEach(input => {
        const key = `${input.dataset.metricId}_${input.dataset.month}`;
        savedInputValues[key] = input.value;
    });
    // ローカルストレージにも保存（ページリロード時用、年度をキーに含める）
    try {
        const storageKey = `monthlyResultsInputValues_${fiscalYearId}`;
        localStorage.setItem(storageKey, JSON.stringify(savedInputValues));
    } catch (e) {
        console.warn('ローカルストレージへの保存に失敗しました:', e);
    }
}

// 入力値を復元
function restoreInputValues() {
    // まずローカルストレージから復元を試みる（年度をキーに含める）
    try {
        const storageKey = `monthlyResultsInputValues_${fiscalYearId}`;
        const stored = localStorage.getItem(storageKey);
        if (stored) {
            const storedValues = JSON.parse(stored);
            Object.assign(savedInputValues, storedValues);
        }
    } catch (e) {
        console.warn('ローカルストレージからの読み込みに失敗しました:', e);
    }
    
    document.querySelectorAll('.value-input').forEach(input => {
        const key = `${input.dataset.metricId}_${input.dataset.month}`;
        if (savedInputValues[key] !== undefined && savedInputValues[key] !== '') {
            // 現在の値が空でない場合は、保存された値で上書きしない（ユーザーが既に入力している可能性がある）
            if (input.value === '' || input.value === null) {
                input.value = savedInputValues[key];
            }
        }
    });
}

// フォーム送信前にすべての入力値を保存
function saveAllInputValuesBeforeSubmit(event) {
    // 年度変更時は、現在の年度のデータを保存してから送信
    saveInputValues();
    // 年度が変わるので、古い年度のデータはクリアしない（別年度として保持）
    return true;
}

// 入力欄が空かどうかをチェックしてクラスを付与
function updateEmptyClass(input) {
    if (!input.value || input.value.trim() === '') {
        input.classList.add('is-empty');
    } else {
        input.classList.remove('is-empty');
    }
}

// ページ読み込み時に保存された入力値を復元
document.addEventListener('DOMContentLoaded', function() {
    // 少し遅延させて確実に復元（DOMが完全に読み込まれた後）
    setTimeout(function() {
        restoreInputValues();
        // 復元後に空チェックと初期値を設定
        document.querySelectorAll('.value-input').forEach(input => {
            updateEmptyClass(input);
            // 初期値を記録
            input.dataset.originalValue = input.value;
        });
    }, 300);
    
    // イベントデリゲーション：保存ボタンのクリックイベント
    document.addEventListener('click', function(e) {
        const saveBtn = e.target.closest('.js-save-category-btn');
        if (!saveBtn) return;
        
        e.preventDefault();
        const categoryName = saveBtn.dataset.category;
        if (categoryName) {
            saveCategoryChanges(categoryName);
        }
    });
    
    // 入力値が変更されたら変更追跡と自動保存
    document.querySelectorAll('.value-input').forEach(input => {
        // 初期化時に空チェック
        updateEmptyClass(input);
        
        // inputイベント: 入力中に変更追跡（サーバーへの自動保存はしない）
        input.addEventListener('input', function() {
            updateEmptyClass(this);
            
            const currentValue = this.value.replace(/,/g, '');
            const originalValue = (this.dataset.originalValue || '').replace(/,/g, '');
            if (currentValue !== originalValue) {
                markAsChanged(this);
            }
            
            saveInputValues();
        });
        
        // blurイベント: LocalStorageに下書き保持のみ
        input.addEventListener('blur', function() {
            saveInputValues();
        });
    });
    
    // 金額入力のフォーマット処理
    document.querySelectorAll('.currency-input').forEach(input => {
        // フォーカス時にカンマを除去
        input.addEventListener('focus', function() {
            this.value = this.value.replace(/,/g, '');
        });
        
        // 入力時にカンマを追加（既存のinputイベントと併用）
        input.addEventListener('input', function(e) {
            // フォーマット処理
            const cursorPos = this.selectionStart;
            const oldValue = this.value;
            const newValue = formatCurrency(this.value);
            
            if (oldValue !== newValue) {
                this.value = newValue;
                // カーソル位置を調整
                const diff = newValue.length - oldValue.length;
                this.setSelectionRange(cursorPos + diff, cursorPos + diff);
            }
        }, true); // capture phaseで実行して、既存のイベントリスナーの前に実行
        
        // フォーカスアウト時にカンマを追加
        input.addEventListener('blur', function() {
            this.value = formatCurrency(this.value);
        });
    });
    
    // Enterキーで次のセルに移動
    const valueInputs = document.querySelectorAll('.value-input');
    valueInputs.forEach((input, index) => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const nextIndex = index + 1;
                if (nextIndex < valueInputs.length) {
                    valueInputs[nextIndex].focus();
                    valueInputs[nextIndex].select();
                }
            }
        });
    });
});

// 金額を3桁区切りカンマ付きでフォーマット（千円単位、切り上げ整数）
function formatCurrency(value) {
    if (!value && value !== 0) return '';
    const num = Math.round(parseFloat(value.toString().replace(/,/g, '')));
    if (isNaN(num)) return '';
    return num.toLocaleString('ja-JP');
}

// カンマを除去して数値に変換（切り上げ）
function parseCurrency(value) {
    if (!value) return null;
    const num = Math.round(parseFloat(value.toString().replace(/,/g, '')));
    return isNaN(num) ? null : num;
}

// 詳細モーダルを開く
function openDetailModal(metricId, month, monthlyResultId) {
    try {
        // 入力値を保存（モーダルを開く前に）
        saveInputValues();
        
        currentMetricId = metricId;
        currentMonth = month;
        // monthlyResultIdが'null'文字列の場合はnullに変換
        currentMonthlyResultId = (monthlyResultId === 'null' || monthlyResultId === null || monthlyResultId === undefined) ? null : monthlyResultId;
        currentMonthDate = new Date(month + 'T00:00:00');
        detailRowIndex = 0;
        
        // 備考欄をリセット
        document.getElementById('detailModalComment').value = '';
        
        // カテゴリーと指標名を設定
        const metricRow = document.querySelector(`[data-metric-id="${metricId}"]`)?.closest('tr');
        if (!metricRow) {
            console.error('指標行が見つかりません:', metricId);
            alert('エラー: 指標情報を取得できませんでした。');
            return;
        }
        
        const categoryRow = metricRow.closest('.card')?.querySelector('.card-header h5');
        const categoryName = categoryRow?.textContent.trim() || '';
        const metricName = metricRow.querySelector('td')?.textContent.trim() || '';
        
        document.getElementById('detailModalCategory').textContent = categoryName;
        document.getElementById('detailModalMetric').textContent = metricName;
        updateMonthDisplay();
        
        // 既存の詳細データを読み込む
        loadDetails();
        
        const modalElement = document.getElementById('detailModal');
        if (!modalElement) {
            console.error('モーダル要素が見つかりません');
            alert('エラー: モーダルを開けませんでした。');
            return;
        }
        
        // 既存のモーダルインスタンスを取得または作成
        let modal = bootstrap.Modal.getInstance(modalElement);
        if (!modal) {
            modal = new bootstrap.Modal(modalElement);
        }
        
        // モーダルが閉じられた後に入力値を復元（すべての閉じる方法に対応）
        const restoreHandler = function() {
            // 少し遅延させて確実に復元
            setTimeout(function() {
                restoreInputValues();
            }, 100);
        };
        
        // 既存のイベントリスナーを削除してから追加（重複を防ぐ）
        modalElement.removeEventListener('hidden.bs.modal', restoreHandler);
        modalElement.addEventListener('hidden.bs.modal', restoreHandler, { once: true });
        
        modal.show();
    } catch (error) {
        console.error('モーダルを開く際にエラーが発生しました:', error);
        alert('エラー: モーダルを開けませんでした。コンソールを確認してください。');
    }
}

// 月表示を更新
function updateMonthDisplay() {
    const year = currentMonthDate.getFullYear();
    const month = currentMonthDate.getMonth() + 1;
    document.getElementById('detailModalMonth').textContent = `${year}年${month}月`;
    
    // 前後月のボタンの有効/無効を設定
    const fiscalYearStart = new Date('{{ $fiscalYear->start_date ?? "2025-07-01" }}');
    const fiscalYearEnd = new Date('{{ $fiscalYear->end_date ?? "2026-06-30" }}');
    
    document.getElementById('prevMonthBtn').disabled = currentMonthDate <= fiscalYearStart;
    document.getElementById('nextMonthBtn').disabled = currentMonthDate >= fiscalYearEnd;
}

// 月を変更
function changeMonth(direction) {
    currentMonthDate.setMonth(currentMonthDate.getMonth() + direction);
    // toISOString() はUTC基準のためJSTで月境界がずれる。ローカル年月で組み立てる
    const y = currentMonthDate.getFullYear();
    const m = String(currentMonthDate.getMonth() + 1).padStart(2, '0');
    currentMonth = `${y}-${m}-01`;
    updateMonthDisplay();
    loadDetails();
}

// 詳細データを読み込む
function loadDetails() {
    // currentMonthlyResultIdがnullの場合は、まず月次実績を取得または作成
    // find-or-create エンドポイントを使うことで既存の value を上書きしない
    if (!currentMonthlyResultId) {
        axios.post('{{ route("monthly-results.find-or-create") }}', {
            fiscal_year_id: fiscalYearId,
            metric_id: currentMetricId,
            target_month: currentMonth,
        })
        .then(function(response) {
            currentMonthlyResultId = response.data.result.id;
            // 詳細ボタンの data-result-id もページリロードなしで更新する
            const btn = document.querySelector(
                `.detail-btn[data-metric-id="${currentMetricId}"][data-month="${currentMonth}"]`
            );
            if (btn) {
                btn.dataset.resultId = currentMonthlyResultId;
                btn.setAttribute(
                    'onclick',
                    `openDetailModal(${currentMetricId}, '${currentMonth}', ${currentMonthlyResultId})`
                );
            }
            
            // 詳細を取得
            return axios.get(`{{ url('/monthly-results') }}/${currentMonthlyResultId}/details`);
        })
        .then(function(response) {
            const details = response.data.details || [];
            document.getElementById('detailTableBody').innerHTML = '';
            detailRowIndex = 0;
            
            details.forEach((detail, index) => {
                addDetailRow(detail.detail, detail.amount, detail.id);
            });
            
            if (details.length === 0) {
                addDetailRow();
            }
            
            // 備考を設定
            document.getElementById('detailModalComment').value = response.data.comment || '';
            
            updateTotal();
        })
        .catch(function(error) {
            console.error('詳細データの読み込みに失敗しました:', error);
            document.getElementById('detailTableBody').innerHTML = '';
            addDetailRow();
            document.getElementById('detailModalComment').value = '';
            updateTotal();
        });
    } else {
        // 既存の月次実績IDがある場合は、直接詳細を取得
        axios.get(`{{ url('/monthly-results') }}/${currentMonthlyResultId}/details`)
        .then(function(response) {
            const details = response.data.details || [];
            document.getElementById('detailTableBody').innerHTML = '';
            detailRowIndex = 0;
            
            details.forEach((detail, index) => {
                addDetailRow(detail.detail, detail.amount, detail.id);
            });
            
            if (details.length === 0) {
                addDetailRow();
            }
            
            // 備考を設定
            document.getElementById('detailModalComment').value = response.data.comment || '';
            
            updateTotal();
        })
        .catch(function(error) {
            console.error('詳細データの読み込みに失敗しました:', error);
            document.getElementById('detailTableBody').innerHTML = '';
            addDetailRow();
            document.getElementById('detailModalComment').value = '';
            updateTotal();
        });
    }
}

// 詳細行を追加
function addDetailRow(detail = '', amount = '', detailId = null) {
    const tbody = document.getElementById('detailTableBody');
    const row = document.createElement('tr');
    row.dataset.detailId = detailId || '';
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm detail-input" value="${detail}" placeholder="詳細">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm amount-input currency-input" value="${formatCurrency(amount)}" placeholder="金額">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDetailRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    
    // 金額入力のフォーマット処理
    const amountInput = row.querySelector('.amount-input');
    amountInput.addEventListener('input', function() {
        this.value = formatCurrency(this.value);
        updateTotal();
    });
    
    // Enterキーで次の行に移動または行を追加
    row.querySelectorAll('input').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const nextInput = this.closest('tr').nextElementSibling?.querySelector('input');
                if (nextInput) {
                    nextInput.focus();
                } else {
                    addDetailRow();
                    const newRow = tbody.lastElementChild;
                    newRow.querySelector('input').focus();
                }
            }
        });
    });
    
    detailRowIndex++;
}

// 詳細行を削除
function removeDetailRow(btn) {
    btn.closest('tr').remove();
    updateTotal();
}

// 合計を更新
function updateTotal() {
    const rows = document.querySelectorAll('#detailTableBody tr');
    let total = 0;
    
    rows.forEach(row => {
        const amountInput = row.querySelector('.amount-input');
        if (amountInput) {
            const amount = parseCurrency(amountInput.value);
            if (amount) {
                total += amount;
            }
        }
    });
    
    document.getElementById('detailModalTotal').textContent = formatCurrency(total) + '千円';
}

// 詳細を保存
function saveDetails() {
    console.log('saveDetails called');
    const rows = document.querySelectorAll('#detailTableBody tr');
    console.log('Found rows:', rows.length);
    const details = [];
    
    rows.forEach((row, index) => {
        const detailInput = row.querySelector('.detail-input');
        const amountInput = row.querySelector('.amount-input');
        const detailId = row.dataset.detailId;
        
        if (detailInput && amountInput) {
            const detail = detailInput.value.trim();
            const amount = parseCurrency(amountInput.value);
            console.log(`Row ${index + 1}: detail="${detail}", amount="${amountInput.value}" -> ${amount}`);
            
            if (detail || amount) {
                details.push({
                    id: detailId || null,
                    detail: detail,
                    amount: amount || 0,
                });
            }
        }
    });
    
    console.log('Details to save:', details.length, details);
    
    // 月次実績IDが確定しているか確認
    if (!currentMonthlyResultId) {
        axios.post('{{ route("monthly-results.find-or-create") }}', {
            fiscal_year_id: fiscalYearId,
            metric_id: currentMetricId,
            target_month: currentMonth,
        })
        .then(function(response) {
            currentMonthlyResultId = response.data.result.id;
            return saveDetailsToServer(details);
        })
        .catch(function(error) {
            console.error('Failed to create monthly result:', error);
            alert('保存に失敗しました。');
        });
    } else {
        console.log('Using existing monthly result ID:', currentMonthlyResultId);
        saveDetailsToServer(details);
    }
}

// サーバーに詳細を保存
function saveDetailsToServer(details) {
    console.log('saveDetailsToServer called with:', details);
    console.log('Monthly result ID:', currentMonthlyResultId);
    
    if (!currentMonthlyResultId) {
        console.error('Monthly result ID is not set!');
        alert('エラー: 月次実績IDが設定されていません。');
        return Promise.reject(new Error('Monthly result ID is not set'));
    }
    
    // 備考を取得
    const comment = document.getElementById('detailModalComment').value.trim();
    
    return axios.post(`{{ url('/monthly-results') }}/${currentMonthlyResultId}/details`, {
        details: details,
        comment: comment
    })
    .then(function(response) {
        console.log('Details saved successfully:', response.data);
        // 保存成功時は現在の年度のローカルストレージをクリアしてからリロード
        try {
            const storageKey = `monthlyResultsInputValues_${fiscalYearId}`;
            localStorage.removeItem(storageKey);
        } catch (e) {
            console.warn('ローカルストレージのクリアに失敗しました:', e);
        }
        alert('詳細を保存しました。');
        const modal = bootstrap.Modal.getInstance(document.getElementById('detailModal'));
        if (modal) {
            modal.hide();
        }
        location.reload();
    })
    .catch(function(error) {
        console.error('Failed to save details:', error);
        if (error.response) {
            console.error('Response data:', error.response.data);
            console.error('Response status:', error.response.status);
            alert(`保存に失敗しました。\nエラー: ${error.response.data?.message || error.response.statusText || '不明なエラー'}`);
        } else {
            alert('保存に失敗しました。\nネットワークエラーまたはサーバーエラーの可能性があります。');
        }
    });
}

// カテゴリ単位で変更を保存（差分保存）
async function saveCategoryChanges(categoryName) {
    const saveBtn = document.querySelector(`.js-save-category-btn[data-category="${categoryName}"]`);
    
    // カテゴリの未保存入力を取得
    const inputsToSave = [];
    window.changedInputs.forEach((data, key) => {
        if (data.category === categoryName) {
            inputsToSave.push(data.input);
        }
    });
    
    if (inputsToSave.length === 0) {
        showToast('info', '保存する変更はありません');
        return;
    }
    
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> 保存中...';
    }
    
    const totalCount = inputsToSave.length;
    let savedCount = 0;
    let errorCount = 0;
    
    // バッチ処理（5件ずつ並列処理）
    const batchSize = 5;
    for (let i = 0; i < inputsToSave.length; i += batchSize) {
        const batch = inputsToSave.slice(i, i + batchSize);
        const results = await Promise.all(batch.map(input => saveInputValue(input, true)));
        
        results.forEach(success => {
            if (success) {
                savedCount++;
            } else {
                errorCount++;
            }
        });
        
        // 進捗表示
        if (saveBtn && i + batchSize < inputsToSave.length) {
            const progress = Math.min(i + batchSize, inputsToSave.length);
            saveBtn.innerHTML = `<i class="bi bi-hourglass-split"></i> ${progress}/${totalCount}`;
        }
    }
    
    if (saveBtn) {
        saveBtn.disabled = false;
        updateCategoryUnsavedCount(categoryName);
    }
    
    // 保存完了後にLocalStorageの下書きを更新
    saveInputValues();
    
    if (errorCount === 0) {
        showToast('success', `${savedCount}件のデータを保存しました`);
    } else {
        showToast('warning', `保存完了（成功: ${savedCount}件, エラー: ${errorCount}件）`);
    }
}

// 旧関数との互換性を保持
window.saveCategory = saveCategoryChanges;

// 一括保存（全カテゴリ）
function saveAll() {
    const inputs = document.querySelectorAll('.value-input');
    const updates = [];
    
    inputs.forEach(input => {
        const metricId = input.dataset.metricId;
        const month = input.dataset.month;
        const value = input.value.trim();
        
        if (value !== '') {
            // カンマを除去して数値に変換（千円単位）
            const numValue = parseCurrency(value);
            if (numValue !== null) {
                updates.push({
                    fiscal_year_id: fiscalYearId,
                    metric_id: metricId,
                    target_month: month,
                    value: numValue
                });
            }
        }
    });
    
    if (updates.length === 0) {
        alert('保存するデータがありません。');
        return;
    }
    
    if (!confirm(`${updates.length}件のデータを保存しますか？`)) {
        return;
    }
    
    // 各更新を順次実行
    let completed = 0;
    let failed = 0;
    
    updates.forEach(update => {
        axios.post('{{ route("monthly-results.update") }}', update)
            .then(function(response) {
                completed++;
                if (completed + failed === updates.length) {
                    if (failed === 0) {
                        // 保存成功時は現在の年度のローカルストレージをクリアしてからリロード
                        try {
                            const storageKey = `monthlyResultsInputValues_${fiscalYearId}`;
                            localStorage.removeItem(storageKey);
                        } catch (e) {
                            console.warn('ローカルストレージのクリアに失敗しました:', e);
                        }
                        alert('すべてのデータを保存しました。');
                        location.reload();
                    } else {
                        alert(`${completed}件保存成功、${failed}件保存失敗しました。`);
                    }
                }
            })
            .catch(function(error) {
                failed++;
                console.error(error);
                if (completed + failed === updates.length) {
                    alert(`${completed}件保存成功、${failed}件保存失敗しました。`);
                }
            });
    });
}


function roundToOneDecimal(num) {
    return Math.round(num * 10) / 10;
}

// 指標グラフモーダルを開く
function openMetricChartModal(metricId, metricName) {
    // 入力値を保存（モーダルを開く前に）
    saveInputValues();
    
    document.getElementById('metricChartModalTitle').textContent = metricName;
    
    // 既存のグラフを破棄
    if (metricChart) {
        metricChart.destroy();
        metricChart = null;
    }
    
    // データを取得
    axios.get(`{{ url('/monthly-results') }}/metric/${metricId}/chart-data`, {
        params: {
            fiscal_year_id: fiscalYearId
        }
    })
    .then(function(response) {
        const data = response.data;
        
        // グラフを描画
        const ctx = document.getElementById('metricChartCanvas').getContext('2d');
        
        // 単位に応じた色を設定（ダッシュボードと同じ）
        let backgroundColor = 'rgba(0, 122, 255, 0.8)';
        let borderColor = '#0071E3';
        
        if (data.metric.type === 'quantity') {
            backgroundColor = 'rgba(255, 149, 0, 0.8)';
            borderColor = '#FF9F0A';
        } else if (data.metric.type === 'percent') {
            backgroundColor = 'rgba(52, 199, 89, 0.8)';
            borderColor = '#24C761';
        }
        
        metricChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.metric.name,
                    data: data.data,
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: false
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                if (value === null) return 'データなし';
                                
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                
                                if (data.metric.type === 'currency') {
                                    // 千円単位なので1000倍して円に変換（切り上げ）
                                    const yenValue = value * 1000;
                                    const absValue = Math.abs(yenValue);
                                    if (absValue >= 100000000) { // 1億円以上
                                        label += roundToOneDecimal(yenValue / 100000000).toFixed(1) + '億円';
                                    } else if (absValue >= 10000000) { // 1千万円以上
                                        label += roundToOneDecimal(yenValue / 10000000).toFixed(1) + '千万円';
                                    } else if (absValue >= 1000000) { // 100万円以上
                                        label += roundToOneDecimal(yenValue / 1000000).toFixed(1) + '百万円';
                                    } else {
                                        label += Math.round(value).toLocaleString('ja-JP') + '千円';
                                    }
                                } else if (data.metric.type === 'quantity') {
                                    label += Math.round(value).toLocaleString('ja-JP') + (data.metric.unit || '個');
                                } else if (data.metric.type === 'percent') {
                                    label += roundToOneDecimal(value).toFixed(1) + '%';
                                } else {
                                    label += Math.round(value).toLocaleString('ja-JP');
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: false
                        },
                        ticks: {
                            callback: function(value) {
                                if (data.metric.type === 'currency') {
                                    // 千円単位なので1000倍して円に変換、その後1億で割る（切り上げ）
                                    const yenValue = value * 1000;
                                    const hundredMillion = yenValue / 100000000;
                                    return roundToOneDecimal(hundredMillion).toFixed(1) + '億';
                                } else if (data.metric.type === 'quantity') {
                                    return Math.round(value).toLocaleString('ja-JP');
                                } else if (data.metric.type === 'percent') {
                                    return roundToOneDecimal(value).toFixed(1) + '%';
                                }
                                return Math.round(value);
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '400'
                            },
                            color: '#1d1d1f'
                        }
                    }
                }
            }
        });
        
        // モーダルを表示
        const modal = new bootstrap.Modal(document.getElementById('metricChartModal'));
        
        // モーダルが閉じられた後に入力値を復元
        const modalElement = document.getElementById('metricChartModal');
        modalElement.addEventListener('hidden.bs.modal', function restoreInputValuesHandler() {
            restoreInputValues();
            // イベントリスナーを一度だけ実行するように削除
            modalElement.removeEventListener('hidden.bs.modal', restoreInputValuesHandler);
        }, { once: true });
        
        modal.show();
    })
    .catch(function(error) {
        console.error(error);
        alert('データの取得に失敗しました。');
    });
}

// CSVダウンロード（イベントデリゲーション）
document.addEventListener('click', function(e) {
    const downloadBtn = e.target.closest('.js-csv-download-btn');
    if (!downloadBtn) return;
    
    e.preventDefault();
    const category = downloadBtn.dataset.category;
    const type = downloadBtn.dataset.type;
    const fiscalYearId = {{ $fiscalYear->id ?? 'null' }};
    
    if (!category || !fiscalYearId) {
        alert('エラー: 必要な情報が不足しています。');
        return;
    }
    
    const url = type === 'detail' 
        ? '{{ route("monthly-results.csv.download-detail") }}?fiscal_year_id=' + fiscalYearId + '&category=' + encodeURIComponent(category)
        : '{{ route("monthly-results.csv.download-simple") }}?fiscal_year_id=' + fiscalYearId + '&category=' + encodeURIComponent(category);
    
    window.location.href = url;
});

// CSVインポート（イベントデリゲーション）
let currentImportCategory = null;
let currentImportFiscalYearId = null;
let csvValidationResult = null;

document.addEventListener('click', function(e) {
    const importBtn = e.target.closest('.js-csv-import-btn');
    if (!importBtn) return;
    
    e.preventDefault();
    currentImportCategory = importBtn.dataset.category;
    currentImportFiscalYearId = {{ $fiscalYear->id ?? 'null' }};
    
    if (!currentImportCategory || !currentImportFiscalYearId) {
        alert('エラー: 必要な情報が不足しています。');
        return;
    }
    
    // モーダルを開く
    const modal = new bootstrap.Modal(document.getElementById('csvImportModal'));
    document.getElementById('csvImportModalTitle').textContent = 'CSVインポート - ' + currentImportCategory;
    
    // モーダルをリセット
    document.getElementById('csvFileInput').value = '';
    document.getElementById('csvPreviewArea').style.display = 'none';
    document.getElementById('csvErrorArea').style.display = 'none';
    document.getElementById('csvSuccessArea').style.display = 'none';
    document.getElementById('csvImportExecuteBtn').disabled = true;
    csvValidationResult = null;
    
    modal.show();
});

// ファイル選択時の処理
document.getElementById('csvFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // プレビューエリアをリセット
    document.getElementById('csvPreviewBody').innerHTML = '';
    document.getElementById('csvErrorArea').style.display = 'none';
    document.getElementById('csvSuccessArea').style.display = 'none';
    document.getElementById('csvImportExecuteBtn').disabled = true;
    csvValidationResult = null;
    
    // ファイルを読み込んでバリデーション
    const formData = new FormData();
    formData.append('file', file);
    formData.append('fiscal_year_id', currentImportFiscalYearId);
    formData.append('category', currentImportCategory);
    formData.append('validate_only', '1'); // バリデーションのみフラグ
    
    axios.post('{{ route("monthly-results.csv.import") }}', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    })
    .then(function(response) {
        csvValidationResult = response.data;
        displayCsvPreview(response.data);
    })
    .catch(function(error) {
        console.error(error);
        let message = 'ファイルの読み込みに失敗しました。';
        if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
        }
        alert(message);
    });
});

// プレビュー表示
function displayCsvPreview(data) {
    const previewBody = document.getElementById('csvPreviewBody');
    previewBody.innerHTML = '';
    
    if (data.preview && data.preview.length > 0) {
        data.preview.forEach(function(row, index) {
            const tr = document.createElement('tr');
            const rowNum = index + 2; // ヘッダー行を考慮
            
            let statusClass = 'table-success';
            let statusText = '✓ 正常';
            if (row.error) {
                statusClass = 'table-danger';
                statusText = '✗ ' + row.error;
            }
            
            tr.className = statusClass;
            tr.innerHTML = `
                <td>${rowNum}</td>
                <td>${row.metric_name || ''}</td>
                <td>${row.month || ''}</td>
                <td>${row.detail || ''}</td>
                <td>${row.amount !== null && row.amount !== undefined ? row.amount.toLocaleString('ja-JP') : ''}</td>
                <td><small>${statusText}</small></td>
            `;
            previewBody.appendChild(tr);
        });
        
        document.getElementById('csvPreviewArea').style.display = 'block';
    }
    
    // エラー表示
    if (data.errors && data.errors.length > 0) {
        const errorList = document.getElementById('csvErrorList');
        errorList.innerHTML = '';
        data.errors.forEach(function(error) {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        document.getElementById('csvErrorArea').style.display = 'block';
        document.getElementById('csvImportExecuteBtn').disabled = true;
    } else if (data.preview && data.preview.length > 0) {
        // エラーがない場合
        const errorCount = data.preview.filter(r => r.error).length;
        if (errorCount === 0) {
            document.getElementById('csvSuccessMessage').textContent = 
                `${data.preview.length}件のデータが正常に読み込めました。インポートを実行できます。`;
            document.getElementById('csvSuccessArea').style.display = 'block';
            document.getElementById('csvImportExecuteBtn').disabled = false;
        } else {
            document.getElementById('csvImportExecuteBtn').disabled = true;
        }
    }
}

// インポート実行
document.getElementById('csvImportExecuteBtn').addEventListener('click', function() {
    if (!csvValidationResult || !document.getElementById('csvFileInput').files[0]) {
        alert('ファイルが選択されていません。');
        return;
    }
    
    if (!confirm('インポートを実行しますか？')) {
        return;
    }
    
    const file = document.getElementById('csvFileInput').files[0];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('fiscal_year_id', currentImportFiscalYearId);
    formData.append('category', currentImportCategory);
    
    // インポート実行
    axios.post('{{ route("monthly-results.csv.import") }}', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    })
    .then(function(response) {
        if (response.data.success) {
            let message = response.data.message;
            if (response.data.errors && response.data.errors.length > 0) {
                message += '\n\nエラー:\n' + response.data.errors.join('\n');
            }
            alert(message);
            const modal = bootstrap.Modal.getInstance(document.getElementById('csvImportModal'));
            modal.hide();
            location.reload();
        } else {
            alert('エラー: ' + (response.data.message || 'インポートに失敗しました。'));
        }
    })
    .catch(function(error) {
        console.error(error);
        let message = 'インポートに失敗しました。';
        if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
        }
        alert(message);
    });
});

// トースト表示
function showToast(type, message) {
    // トーストコンテナが無ければ作成
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1100';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : 
                    type === 'warning' ? 'bg-warning' : 
                    type === 'info' ? 'bg-info' : 'bg-danger';
    const textClass = (type === 'warning' || type === 'info') ? 'text-dark' : 'text-white';
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center ${bgClass} ${textClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

// ページ離脱時に未保存の変更がある場合は警告を表示
window.addEventListener('beforeunload', function(e) {
    if (window.changedInputs && window.changedInputs.size > 0) {
        e.preventDefault();
        e.returnValue = '未保存の変更があります。ページを離れると変更が失われます。';
        return e.returnValue;
    }
});
</script>
@endpush
