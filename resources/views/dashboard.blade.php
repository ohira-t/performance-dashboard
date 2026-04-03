@extends('layouts.app')

@section('title', 'ダッシュボード')
@section('page-title', 'ダッシュボード')

@php
use App\Helpers\DashboardHelper;
@endphp

@section('content')
@if(!$fiscalYear)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> 年度が選択されていません。
    </div>
@else
    <!-- KPIカードセクション -->
    <div class="row" style="margin-bottom: 0.5rem;">
        <div class="col-12" style="margin-bottom: 0.5rem;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <!-- 期間切り替えタブ -->
                    <div class="btn-group" role="group" style="border-radius: 8px; overflow: hidden;">
                        <button type="button" class="btn btn-sm period-tab active" data-period="monthly" style="font-size: 0.8125rem; padding: 0.375rem 0.75rem; border: 1px solid var(--border-color); background: var(--accent-color); color: white;">
                            当月
                        </button>
                        <button type="button" class="btn btn-sm period-tab" data-period="first_half" style="font-size: 0.8125rem; padding: 0.375rem 0.75rem; border: 1px solid var(--border-color); background: white; color: var(--text-primary);">
                            上半期
                        </button>
                        <button type="button" class="btn btn-sm period-tab" data-period="second_half" style="font-size: 0.8125rem; padding: 0.375rem 0.75rem; border: 1px solid var(--border-color); background: white; color: var(--text-primary);">
                            下半期
                        </button>
                        <button type="button" class="btn btn-sm period-tab" data-period="full_year" style="font-size: 0.8125rem; padding: 0.375rem 0.75rem; border: 1px solid var(--border-color); background: white; color: var(--text-primary);">
                            通期
                        </button>
                    </div>
                    @if(isset($selectedMonth) && $selectedMonth)
                        <span id="periodLabel" class="text-muted" style="font-size: 0.875rem;">
                            {{ \Carbon\Carbon::parse($selectedMonth)->format('Y年n月') }}
                        </span>
                    @endif
                </div>
                @if(isset($availableMonths) && $availableMonths->count() > 0)
                    <form method="GET" action="{{ route('dashboard') }}" class="mb-0" id="kpiMonthForm">
                        @if(isset($fiscalYear) && $fiscalYear)
                            <input type="hidden" name="fiscal_year_id" value="{{ $fiscalYear->id }}" id="kpiMonthFormFiscalYear">
                        @endif
                        <select name="selected_month" id="kpiMonthSelect" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem; padding: 0.375rem 2rem 0.375rem 0.75rem; border: 0.5px solid var(--border-color); border-radius: 0.375rem;">
                            @foreach($availableMonths as $month)
                                <option value="{{ $month['value'] }}" {{ (isset($selectedMonth) && $selectedMonth === $month['value']) ? 'selected' : '' }}>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    </div>
    
    <!-- KPIカード（当月データ） -->
    <div class="row" style="margin-bottom: 0.5rem;" id="monthlyKpiCards">
        @foreach($kpiCards as $key => $kpi)
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card kpi-card h-100">
                    <div class="card-body" style="padding: 1rem;">
                        <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 1rem;">
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 400; text-transform: none; letter-spacing: -0.01em;">
                                    {{ $kpi['label'] }}
                                </h6>
                                <h3 class="mb-0" style="font-size: 1.5rem; font-weight: 500; color: var(--text-primary); letter-spacing: -0.02em;">
                                    @if($kpi['value'] !== null)
                                        {!! DashboardHelper::formatCurrencyYen($kpi['value']) !!}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </h3>
                            </div>
                            <div class="kpi-icon" style="width: 40px; height: 40px; border-radius: 6px; background: rgba(0, 113, 227, 0.1); display: flex; align-items: center; justify-content: center; color: var(--accent-color); font-size: 1.25rem;">
                                <i class="bi {{ $kpi['icon'] }}"></i>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem;">
                            <div style="margin-bottom: 0.5rem;">
                                @if($kpi['mom'] !== null)
                                    <span class="{{ DashboardHelper::getMomColorClass($kpi['mom'], $kpi['reverse'] ?? false) }}" style="font-weight: 400; font-size: 0.8125rem;">
                                        <i class="bi {{ DashboardHelper::getMomIcon($kpi['mom'], $kpi['reverse'] ?? false) }}"></i>
                                        {{ DashboardHelper::formatMonthOverMonth($kpi['mom']) }}
                                    </span>
                                    <span class="text-muted" style="font-size: 0.75rem; margin-left: 0.375rem;">前月比</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.75rem;">前月比 -</span>
                                @endif
                            </div>
                            <div>
                                @if(isset($kpi['yoy']) && $kpi['yoy'] !== null)
                                    <span class="{{ DashboardHelper::getMomColorClass($kpi['yoy'], $kpi['reverse'] ?? false) }}" style="font-weight: 400; font-size: 0.8125rem;">
                                        <i class="bi {{ DashboardHelper::getMomIcon($kpi['yoy'], $kpi['reverse'] ?? false) }}"></i>
                                        {{ DashboardHelper::formatMonthOverMonth($kpi['yoy']) }}
                                    </span>
                                    <span class="text-muted" style="font-size: 0.75rem; margin-left: 0.375rem;">前期比</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.75rem;">前期比 -</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- KPIカード（期間サマリー） -->
    @if(isset($periodSummary))
    <div class="row" style="margin-bottom: 0.5rem; display: none;" id="periodKpiCards">
        @php
            $periodKpis = [
                'revenue' => ['label' => '売上合計', 'icon' => 'bi-cash-stack', 'reverse' => false],
                'profit' => ['label' => '経常利益', 'icon' => 'bi-graph-up-arrow', 'reverse' => false],
                'cost' => ['label' => '原価合計', 'icon' => 'bi-cart', 'reverse' => true],
                'expense' => ['label' => '販管費合計', 'icon' => 'bi-receipt', 'reverse' => true],
            ];
        @endphp
        @foreach($periodKpis as $key => $kpi)
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card kpi-card h-100">
                    <div class="card-body" style="padding: 1rem;">
                        <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 1rem;">
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 400; text-transform: none; letter-spacing: -0.01em;">
                                    {{ $kpi['label'] }}
                                </h6>
                                <h3 class="mb-0 period-value" data-key="{{ $key }}" 
                                    data-first-half="{{ $periodSummary['first_half'][$key] }}"
                                    data-second-half="{{ $periodSummary['second_half'][$key] }}"
                                    data-full-year="{{ $periodSummary['full_year'][$key] }}"
                                    data-prev-first-half="{{ $periodSummary['prev_first_half'][$key] }}"
                                    data-prev-second-half="{{ $periodSummary['prev_second_half'][$key] }}"
                                    data-prev-full-year="{{ $periodSummary['prev_full_year'][$key] }}"
                                    data-reverse="{{ $kpi['reverse'] ? 'true' : 'false' }}"
                                    style="font-size: 1.5rem; font-weight: 500; color: var(--text-primary); letter-spacing: -0.02em;">
                                    -
                                </h3>
                            </div>
                            <div class="kpi-icon" style="width: 40px; height: 40px; border-radius: 6px; background: rgba(0, 113, 227, 0.1); display: flex; align-items: center; justify-content: center; color: var(--accent-color); font-size: 1.25rem;">
                                <i class="bi {{ $kpi['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="period-yoy-container" style="margin-top: 1rem;">
                            <span class="period-yoy text-muted" style="font-size: 0.75rem;">前期比: -</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- メイングラフ -->
    <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-graph-up"></i>
                            <h5 class="mb-0">業績推移グラフ</h5>
                        </div>
                        <div>
                            @if(isset($fiscalYears) && $fiscalYears->count() > 0)
                                <form method="GET" action="{{ route('dashboard') }}" class="mb-0" id="mainGraphFiscalYearForm">
                                    <select name="fiscal_year_id" onchange="document.getElementById('mainGraphFiscalYearForm').submit();" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem; padding: 0.375rem 2rem 0.375rem 0.75rem; border: 0.5px solid var(--border-color); border-radius: 0.375rem;">
                                        @foreach($fiscalYears as $fy)
                                            <option value="{{ $fy->id }}" {{ ($fiscalYear && $fiscalYear->id === $fy->id) ? 'selected' : '' }}>
                                                {{ $fy->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @elseif($fiscalYear)
                                <span class="badge bg-secondary">{{ $fiscalYear->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(isset($chartData) && !empty($chartData['months']) && count($chartData['months']) > 0)
                        <div style="padding: 1.5rem;">
                            <canvas id="performanceChart" style="height: clamp(250px, 30vw, 400px);"></canvas>
                        </div>
                    @else
                        <div class="alert alert-info mb-0" style="margin: 1.5rem;">
                            <i class="bi bi-info-circle"></i> グラフデータがありません。
                        </div>
                    @endif
                </div>
                </div>
            </div>
        </div>

    

    <!-- セグメント別指標（アコーディオン） -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-layers"></i>
                        <h5 class="mb-0">収益指標</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="segmentAccordion">
                        @if(isset($segments) && !empty($segments))
                        @foreach($segments as $segmentIndex => $segment)
                            <div class="accordion-item border-0" style="{{ $segmentIndex < count($segments) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                                <h2 class="accordion-header" id="heading{{ $segmentIndex }}">
                                    <button class="accordion-button {{ $segmentIndex === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $segmentIndex }}" aria-expanded="{{ $segmentIndex === 0 ? 'true' : 'false' }}">
                                        <i class="bi bi-folder me-2" style="font-size: 0.875rem;"></i>
                                        <span style="font-weight: 400;">{{ $segment['name'] }}</span>
                                    </button>
                                </h2>
                                <div id="collapse{{ $segmentIndex }}" class="accordion-collapse collapse {{ $segmentIndex === 0 ? 'show' : '' }}" data-bs-parent="#segmentAccordion">
                                    <div class="accordion-body">
                                        <!-- グラフ -->
                                        @if(isset($segment['chartData']) && !empty($segment['chartData']['months']))
                                            <div class="mb-4">
                                                <div class="d-flex align-items-center justify-content-end mb-2">
                                                    @if(isset($fiscalYears) && $fiscalYears->count() > 0)
                                                        <form method="GET" action="{{ route('dashboard') }}" class="mb-0">
                                                            <select name="fiscal_year_id" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem; padding: 0.375rem 2rem 0.375rem 0.75rem; border: 0.5px solid var(--border-color); border-radius: 0.375rem;">
                                                                @foreach($fiscalYears as $fy)
                                                                    <option value="{{ $fy->id }}" {{ ($fiscalYear && $fiscalYear->id === $fy->id) ? 'selected' : '' }}>
                                                                        {{ $fy->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </form>
                                                    @endif
                                                </div>
                                                <div style="height: clamp(220px, 25vw, 350px); position: relative;">
                                                    <canvas id="segmentChart{{ $segmentIndex }}"></canvas>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- 表 -->
                                        <div class="table-responsive" style="overflow-x: auto; overflow-y: visible;">
                                            <table class="table table-hover mb-0" style="font-size: 0.875rem; white-space: nowrap;">
                                                <thead style="position: sticky; top: 0; background-color: #fafafa; z-index: 10;">
                                                    <tr>
                                                        <th style="width: 10%; border-right: 1px solid #e5e7eb;">指標名</th>
                                                        <th style="width: 8%; border-right: 1px solid #e5e7eb;" class="text-end">合計</th>
                                                        <th style="width: 6%;" class="text-end">7月</th>
                                                        <th style="width: 6%;" class="text-end">8月</th>
                                                        <th style="width: 6%;" class="text-end">9月</th>
                                                        <th style="width: 6%;" class="text-end">10月</th>
                                                        <th style="width: 6%;" class="text-end">11月</th>
                                                        <th style="width: 6%;" class="text-end">12月</th>
                                                        <th style="width: 6%;" class="text-end">1月</th>
                                                        <th style="width: 6%;" class="text-end">2月</th>
                                                        <th style="width: 6%;" class="text-end">3月</th>
                                                        <th style="width: 6%;" class="text-end">4月</th>
                                                        <th style="width: 6%;" class="text-end">5月</th>
                                                        <th style="width: 6%;" class="text-end">6月</th>
                                                        <th style="width: 8%; border-left: 1px solid #e5e7eb;" class="text-end">前月比{{ isset($segment['momLabel']) && $segment['momLabel'] ? '('.$segment['momLabel'].')' : '' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($segment['metrics']) && !empty($segment['metrics']))
                                                    @foreach($segment['metrics'] as $item)
                                                        @php
                                                            $metric = $item['metric'];
                                                            $mom = $item['mom'];
                                                            $reverse = in_array($metric->name, ['原価合計', '販管費合計', '物流費合計', '調整コスト']);
                                                            $metricId = isset($metric->id) ? $metric->id : 0;
                                                            $fiscalYearTotal = $segment['fiscalYearTotal'][$metricId] ?? 0;
                                                            $monthlyValues = $segment['monthlyValues'][$metricId] ?? [];
                                                            $isTotalRow = ($metricId === 0);
                                                            $months = ['7月', '8月', '9月', '10月', '11月', '12月', '1月', '2月', '3月', '4月', '5月', '6月'];
                                                        @endphp
                                                        <tr style="{{ $isTotalRow ? 'background-color: #f8f9fa !important; border-top: 1px solid #e5e7eb !important; border-bottom: 1px solid #e5e7eb !important;' : '' }}">
                                                            <td style="font-weight: {{ $isTotalRow ? '500' : '400' }};{{ $isTotalRow ? ' color: #1d1d1f !important; background-color: #f8f9fa !important;' : '' }} border-right: 1px solid #e5e7eb;">
                                                                {{ isset($metric->display_name) ? $metric->display_name : $metric->name }}
                                                            </td>
                                                            <td class="text-end" style="font-weight: {{ $isTotalRow ? '500' : '400' }};{{ $isTotalRow ? ' color: #1d1d1f !important; background-color: #f8f9fa !important;' : '' }} border-right: 1px solid #e5e7eb;">
                                                                @if($fiscalYearTotal !== null && $fiscalYearTotal != 0)
                                                                    @if(isset($metric->type) && $metric->type === 'currency')
                                                                        {{ DashboardHelper::formatCurrencyWithoutUnit($fiscalYearTotal, 0) }}
                                                                    @else
                                                                        {{ DashboardHelper::formatNumber($fiscalYearTotal, 0) }}
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            @foreach($months as $month)
                                                                <td class="text-end" style="{{ $isTotalRow ? ' color: #1d1d1f !important; background-color: #f8f9fa !important;' : '' }}">
                                                                    @php
                                                                        $monthValue = $monthlyValues[$month] ?? null;
                                                                    @endphp
                                                                    @if($monthValue !== null)
                                                                        @if(isset($metric->type) && $metric->type === 'currency')
                                                                            {{ DashboardHelper::formatCurrencyWithoutUnit($monthValue, 0) }}
                                                                        @else
                                                                            {{ DashboardHelper::formatNumber($monthValue, 0) }}
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                            <td class="text-end" style="{{ $isTotalRow ? ' color: #1d1d1f !important; background-color: #f8f9fa !important;' : '' }} border-left: 1px solid #e5e7eb;">
                                                                @if($mom !== null)
                                                                    <span class="{{ DashboardHelper::getMomColorClass($mom, $reverse) }}" style="font-weight: 400; font-size: 0.8125rem;">
                                                                        <i class="bi {{ DashboardHelper::getMomIcon($mom, $reverse) }}" style="font-size: 0.75rem;"></i>
                                                                        {{ DashboardHelper::formatMonthOverMonth($mom) }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    @else
                                                    <tr>
                                                        <td colspan="15" class="text-center text-muted" style="padding: 2rem;">
                                                            <i class="bi bi-info-circle"></i> データがありません。
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                            <div class="text-end mt-2" style="padding-right: 1rem; padding-bottom: 0.5rem;">
                                                <small class="text-muted" style="font-size: 0.75rem;">※単位：千円</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> セグメントデータがありません。
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 数量指標（アコーディオン） -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-layers"></i>
                        <h5 class="mb-0">数量指標</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="operationSegmentAccordion">
                        @if(isset($operationSegments) && !empty($operationSegments))
                        @foreach($operationSegments as $segmentIndex => $segment)
                            <div class="accordion-item border-0" style="{{ $segmentIndex < count($operationSegments) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                                <h2 class="accordion-header" id="operationHeading{{ $segmentIndex }}">
                                    <button class="accordion-button {{ $segmentIndex === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#operationCollapse{{ $segmentIndex }}" aria-expanded="{{ $segmentIndex === 0 ? 'true' : 'false' }}">
                                        <i class="bi bi-folder me-2" style="font-size: 0.875rem;"></i>
                                        <span style="font-weight: 400;">{{ $segment['name'] }}</span>
                                    </button>
                                </h2>
                                <div id="operationCollapse{{ $segmentIndex }}" class="accordion-collapse collapse {{ $segmentIndex === 0 ? 'show' : '' }}" data-bs-parent="#operationSegmentAccordion">
                                    <div class="accordion-body">
                                        <!-- グラフ -->
                                        @if(isset($segment['chartData']) && !empty($segment['chartData']['months']))
                                            <div class="mb-4">
                                                <div class="d-flex align-items-center justify-content-end mb-2">
                                                    @if(isset($fiscalYears) && $fiscalYears->count() > 0)
                                                        <form method="GET" action="{{ route('dashboard') }}" class="mb-0">
                                                            <select name="fiscal_year_id" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem; padding: 0.375rem 2rem 0.375rem 0.75rem; border: 0.5px solid var(--border-color); border-radius: 0.375rem;">
                                                                @foreach($fiscalYears as $fy)
                                                                    <option value="{{ $fy->id }}" {{ ($fiscalYear && $fiscalYear->id === $fy->id) ? 'selected' : '' }}>
                                                                        {{ $fy->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </form>
                                                    @endif
                                                </div>
                                                <div style="height: clamp(220px, 25vw, 350px); position: relative;">
                                                    <canvas id="operationSegmentChart{{ $segmentIndex }}"></canvas>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- 表 -->
                                        <div class="table-responsive" style="overflow-x: auto; overflow-y: visible;">
                                            <table class="table table-hover mb-0" style="font-size: 0.875rem; white-space: nowrap;">
                                                <thead style="position: sticky; top: 0; background-color: #fafafa; z-index: 10;">
                                                    <tr>
                                                        <th style="width: 10%; border-right: 1px solid #e5e7eb;">指標名</th>
                                                        <th style="width: 8%; border-right: 1px solid #e5e7eb;" class="text-end">合計</th>
                                                        <th style="width: 6%;" class="text-end">7月</th>
                                                        <th style="width: 6%;" class="text-end">8月</th>
                                                        <th style="width: 6%;" class="text-end">9月</th>
                                                        <th style="width: 6%;" class="text-end">10月</th>
                                                        <th style="width: 6%;" class="text-end">11月</th>
                                                        <th style="width: 6%;" class="text-end">12月</th>
                                                        <th style="width: 6%;" class="text-end">1月</th>
                                                        <th style="width: 6%;" class="text-end">2月</th>
                                                        <th style="width: 6%;" class="text-end">3月</th>
                                                        <th style="width: 6%;" class="text-end">4月</th>
                                                        <th style="width: 6%;" class="text-end">5月</th>
                                                        <th style="width: 6%;" class="text-end">6月</th>
                                                        <th style="width: 8%; border-left: 1px solid #e5e7eb;" class="text-end">前月比{{ isset($segment['momLabel']) && $segment['momLabel'] ? '('.$segment['momLabel'].')' : '' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($segment['metrics']) && !empty($segment['metrics']))
                                                        @foreach($segment['metrics'] as $item)
                                                            @php
                                                                $metric = $item['metric'];
                                                                $mom = $item['mom'];
                                                                $reverse = false;
                                                                $metricId = isset($metric->id) ? $metric->id : 0;
                                                                $fiscalYearTotal = isset($segment['fiscalYearTotal']) && isset($segment['fiscalYearTotal'][$metricId]) ? $segment['fiscalYearTotal'][$metricId] : 0;
                                                                $monthlyValues = isset($segment['monthlyValues']) && isset($segment['monthlyValues'][$metricId]) ? $segment['monthlyValues'][$metricId] : [];
                                                                $isTotalRow = false;
                                                                $months = ['7月', '8月', '9月', '10月', '11月', '12月', '1月', '2月', '3月', '4月', '5月', '6月'];
                                                            @endphp
                                                            <tr>
                                                                <td style="font-weight: 400; border-right: 1px solid #e5e7eb;">
                                                                    {{ isset($metric->display_name) ? $metric->display_name : $metric->name }}
                                                                </td>
                                                                <td class="text-end" style="font-weight: 400; border-right: 1px solid #e5e7eb;">
                                                                    @if($fiscalYearTotal !== null && $fiscalYearTotal != 0)
                                                                        {{ DashboardHelper::formatNumber($fiscalYearTotal, 0) }}
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                @foreach($months as $month)
                                                                    <td class="text-end">
                                                                        @php
                                                                            $monthValue = isset($monthlyValues[$month]) ? $monthlyValues[$month] : null;
                                                                        @endphp
                                                                        @if($monthValue !== null)
                                                                            {{ DashboardHelper::formatNumber($monthValue, 0) }}
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                                <td class="text-end" style="border-left: 1px solid #e5e7eb;">
                                                                    @if($mom !== null)
                                                                        <span class="{{ DashboardHelper::getMomColorClass($mom, $reverse) }}" style="font-weight: 400; font-size: 0.8125rem;">
                                                                            <i class="bi {{ DashboardHelper::getMomIcon($mom, $reverse) }}" style="font-size: 0.75rem;"></i>
                                                                            {{ DashboardHelper::formatMonthOverMonth($mom) }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="15" class="text-center text-muted" style="padding: 2rem;">
                                                                <i class="bi bi-info-circle"></i> データがありません。
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> 数量指標データがありません。
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif
@endsection

@push('styles')
<style>
    .kpi-card {
        border-left: 2px solid var(--accent-color);
        transition: all 0.2s ease;
    }
    
    .kpi-card:hover {
        box-shadow: var(--shadow-md);
    }
    
    .accordion-button {
        font-weight: 400;
        background-color: #fafafa;
        border: none;
        padding: 0.875rem 1.25rem;
        font-size: 0.875rem;
    }
    
    .accordion-button::after {
        width: 0.75rem;
        height: 0.75rem;
        background-size: 0.75rem 0.75rem;
        margin-left: auto;
        opacity: 0.8;
        transition: transform 0.2s ease, opacity 0.2s ease;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2386868b' %3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E") !important;
    }
    
    .accordion-button:not(.collapsed)::after {
        transform: rotate(180deg);
        opacity: 1;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #f5f5f7;
        color: var(--text-primary);
        box-shadow: none;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: transparent;
    }
    
    .accordion-button:hover::after {
        opacity: 1;
    }
    
    .accordion-body {
        padding: 1rem;
        background-color: #ffffff;
    }
    
    .sparkline {
        opacity: 0.6;
    }
</style>
@endpush

@push('scripts')
@if($fiscalYear)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
window.__dashboardConfig = {
    periodLabels: {
        'monthly': '{{ isset($selectedMonth) ? \Carbon\Carbon::parse($selectedMonth)->format("Y年n月") : "" }}',
        'first_half': '{{ $fiscalYear->name }} 上半期（7月〜12月）',
        'second_half': '{{ $fiscalYear->name }} 下半期（1月〜6月）',
        'full_year': '{{ $fiscalYear->name }} 通期'
    },
    @if(isset($chartData) && !empty($chartData['months']) && count($chartData['months']) > 0)
    mainChart: {
        months: @json($chartData['months']),
        revenue: @json($chartData['revenue']),
        expenses: @json($chartData['expenses']),
        profit: @json($chartData['profit'])
    },
    @endif
    segmentCharts: [
        @foreach($segments as $segmentIndex => $segment)
            @if(isset($segment['chartData']) && !empty($segment['chartData']['months']) && count($segment['chartData']['months']) > 0)
            { canvasId: 'segmentChart{{ $segmentIndex }}', months: @json($segment['chartData']['months']), datasets: @json($segment['chartData']['datasets']) },
            @endif
        @endforeach
    ],
    operationCharts: [
        @if(isset($operationSegments) && !empty($operationSegments))
        @foreach($operationSegments as $segmentIndex => $segment)
            @if(isset($segment['chartData']) && !empty($segment['chartData']['months']) && count($segment['chartData']['months']) > 0)
            { canvasId: 'operationSegmentChart{{ $segmentIndex }}', months: @json($segment['chartData']['months']), datasets: @json($segment['chartData']['datasets']) },
            @endif
        @endforeach
        @endif
    ]
};
</script>
<script src="/js/dashboard.js"></script>
@endif
@endpush
