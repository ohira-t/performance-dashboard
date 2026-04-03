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
document.addEventListener('DOMContentLoaded', function() {
    // 期間切り替えタブの処理
    const periodTabs = document.querySelectorAll('.period-tab');
    const monthlyKpiCards = document.getElementById('monthlyKpiCards');
    const periodKpiCards = document.getElementById('periodKpiCards');
    const periodLabel = document.getElementById('periodLabel');
    const kpiMonthSelect = document.getElementById('kpiMonthSelect');
    
    const periodLabels = {
        'monthly': '{{ isset($selectedMonth) ? \Carbon\Carbon::parse($selectedMonth)->format("Y年n月") : "" }}',
        'first_half': '{{ $fiscalYear->name }} 上半期（7月〜12月）',
        'second_half': '{{ $fiscalYear->name }} 下半期（1月〜6月）',
        'full_year': '{{ $fiscalYear->name }} 通期'
    };
    
    // 金額フォーマット関数（千円単位を円に変換して実数表示 - 当月カードと同じ形式）
    function formatCurrency(value) {
        if (value === 0 || value === null || value === undefined || isNaN(value)) {
            return '<span class="text-muted">-</span>';
        }
        const yenValue = value * 1000; // 千円単位を円に変換
        const formatted = new Intl.NumberFormat('ja-JP').format(yenValue);
        return formatted + '<span style="font-size: 0.75rem;">円</span>';
    }
    
    periodTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const period = this.dataset.period;
            
            // タブのアクティブ状態を更新
            periodTabs.forEach(t => {
                t.style.background = 'white';
                t.style.color = 'var(--text-primary)';
                t.classList.remove('active');
            });
            this.style.background = 'var(--accent-color)';
            this.style.color = 'white';
            this.classList.add('active');
            
            // ラベルを更新
            if (periodLabel) {
                periodLabel.textContent = periodLabels[period];
            }
            
            // 月選択の表示/非表示
            if (kpiMonthSelect) {
                kpiMonthSelect.closest('form').style.display = period === 'monthly' ? 'block' : 'none';
            }
            
            if (period === 'monthly') {
                // 当月表示
                if (monthlyKpiCards) monthlyKpiCards.style.display = 'flex';
                if (periodKpiCards) periodKpiCards.style.display = 'none';
            } else {
                // 期間サマリー表示
                if (monthlyKpiCards) monthlyKpiCards.style.display = 'none';
                if (periodKpiCards) {
                    periodKpiCards.style.display = 'flex';
                    
                    // 値と前期比を更新
                    document.querySelectorAll('.period-value').forEach(el => {
                        const key = el.dataset.key;
                        const isReverse = el.dataset.reverse === 'true';
                        let value, prevValue;
                        
                        if (period === 'first_half') {
                            value = parseFloat(el.dataset.firstHalf);
                            prevValue = parseFloat(el.dataset.prevFirstHalf);
                        } else if (period === 'second_half') {
                            value = parseFloat(el.dataset.secondHalf);
                            prevValue = parseFloat(el.dataset.prevSecondHalf);
                        } else {
                            value = parseFloat(el.dataset.fullYear);
                            prevValue = parseFloat(el.dataset.prevFullYear);
                        }
                        
                        el.innerHTML = formatCurrency(value);
                        
                        // 前期比を計算して表示
                        const yoyContainer = el.closest('.card-body').querySelector('.period-yoy');
                        if (yoyContainer) {
                            if (prevValue !== null && prevValue !== undefined && !isNaN(prevValue) && prevValue !== 0 && value !== null && value !== undefined && !isNaN(value)) {
                                const yoyPercent = ((value - prevValue) / prevValue) * 100;
                                const yoyFormatted = yoyPercent.toFixed(1);
                                const isPositive = yoyPercent >= 0;
                                // reverseの場合は色を逆に（コストや販管費は減少が良い）
                                const isGood = isReverse ? !isPositive : isPositive;
                                const colorClass = isGood ? 'text-success' : 'text-danger';
                                const arrow = isPositive ? '↑' : '↓';
                                yoyContainer.innerHTML = `前期比: <span class="${colorClass}">${arrow}${Math.abs(yoyFormatted)}%</span>`;
                            } else {
                                yoyContainer.innerHTML = '前期比: <span class="text-muted">-</span>';
                            }
                        }
                    });
                }
            }
        });
    });
    
    // 月選択の変更イベント
    if (kpiMonthSelect) {
        kpiMonthSelect.addEventListener('change', function() {
            document.getElementById('kpiMonthForm').submit();
        });
    }
    
    function roundToOneDecimal(num) {
        return Math.round(num * 10) / 10;
    }
    
    // 金額を億円単位に変換する関数（切り上げ）
    function formatToHundredMillion(value) {
        // 千円単位なので、1000倍して円に変換、その後1億で割る
        const yenValue = value * 1000;
        const hundredMillion = yenValue / 100000000;
        return roundToOneDecimal(hundredMillion).toFixed(1);
    }
    
    // 数量指標用のフォーマット関数（数量はそのまま表示、切り上げ）
    const DashboardHelper = {
        formatAmountForGraph: function(value) {
            if (value === null || value === undefined) {
                return '-';
            }
            const absValue = Math.abs(value);
            if (absValue >= 1000) {
                return roundToOneDecimal(value / 1000).toFixed(1) + '千';
            } else {
                return Math.round(value).toFixed(0);
            }
        }
    };
    
    // メイングラフ
    @if(isset($chartData) && !empty($chartData['months']) && count($chartData['months']) > 0)
    const chartCanvas = document.getElementById('performanceChart');
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        
        const monthLabels = @json($chartData['months']);
        const revenueData = @json($chartData['revenue']);
        const expensesData = @json($chartData['expenses']);
        const profitData = @json($chartData['profit']);
        
        // 前年度のデータ
        const previousChartData = @json($previousChartData ?? null);
        
        // データセットを準備（P/L形式：左=費用+経常利益、右=売上）
        // Apple Design風カラーパレット
        const datasets = [
            {
                label: '費用',
                data: expensesData,
                type: 'bar',
                stack: 'left',
                order: 2,
                backgroundColor: 'rgba(251, 77, 61, 0.88)',  // Apple Red - 柔らかめ
                borderWidth: 0,
                yAxisID: 'y'
            },
            {
                label: '経常利益',
                data: profitData,
                type: 'bar',
                stack: 'left',
                order: 1,
                backgroundColor: 'rgba(36, 199, 97, 0.88)',  // Apple Green - 落ち着いた緑
                borderWidth: 0,
                yAxisID: 'y'
            },
            {
                label: '売上',
                data: revenueData,
                type: 'bar',
                stack: 'right',
                order: 3,
                backgroundColor: 'rgba(0, 113, 227, 0.88)',  // Apple Blue - 公式サイトの青
                borderWidth: 0,
                yAxisID: 'y'
            }
        ];
        
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: false
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 12,
                                weight: '400'
                            },
                            padding: 15,
                            usePointStyle: false,
                            boxWidth: 14,
                            boxHeight: 14
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    // 千円単位なので1000倍して円に変換（切り上げ）
                                    const yenValue = context.parsed.y * 1000;
                                    const hundredMillion = yenValue / 100000000;
                                    label += roundToOneDecimal(hundredMillion).toFixed(1) + '億円';
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
                        stacked: true,
                        title: {
                            display: false
                        },
                        ticks: {
                            callback: function(value) {
                                // 千円単位なので1000倍して円に変換、その後1億で割る（切り上げ）
                                const yenValue = value * 1000;
                                const hundredMillion = yenValue / 100000000;
                                return roundToOneDecimal(hundredMillion).toFixed(1) + '億';
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
                        stacked: true,
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
        
    }
    @endif
    
    // セグメント別グラフ
    @foreach($segments as $segmentIndex => $segment)
        @if(isset($segment['chartData']) && !empty($segment['chartData']['months']) && count($segment['chartData']['months']) > 0)
        const segmentChart{{ $segmentIndex }}Canvas = document.getElementById('segmentChart{{ $segmentIndex }}');
        if (segmentChart{{ $segmentIndex }}Canvas) {
            const segmentCtx{{ $segmentIndex }} = segmentChart{{ $segmentIndex }}Canvas.getContext('2d');
            
            const segmentMonthLabels{{ $segmentIndex }} = @json($segment['chartData']['months']);
            const segmentDatasets{{ $segmentIndex }} = @json($segment['chartData']['datasets']);
            
            const segmentChartDatasets{{ $segmentIndex }} = segmentDatasets{{ $segmentIndex }}.map((dataset, index) => {
                // Apple Design風カラーパレット（洗練された彩度・明度）
                const colors = [
                    { bg: 'rgba(0, 113, 227, 0.75)', border: '#0071E3' },      // Apple Blue
                    { bg: 'rgba(36, 199, 97, 0.75)', border: '#24C761' },      // Apple Green
                    { bg: 'rgba(255, 159, 10, 0.75)', border: '#FF9F0A' },     // Apple Orange
                    { bg: 'rgba(175, 82, 222, 0.75)', border: '#AF52DE' },     // Apple Purple
                    { bg: 'rgba(251, 77, 61, 0.75)', border: '#FB4D3D' },      // Apple Red
                    { bg: 'rgba(90, 200, 250, 0.75)', border: '#5AC8FA' },     // Apple Teal
                    { bg: 'rgba(255, 214, 10, 0.75)', border: '#FFD60A' },     // Apple Yellow
                ];
                const color = colors[index % colors.length];
                
                return {
                    label: dataset.label,
                    data: dataset.data,
                    type: 'line',
                    borderColor: color.border,
                    backgroundColor: color.bg,
                    borderWidth: 2,
                    fill: false,
                    tension: 0,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: color.border,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1,
                    yAxisID: 'y'
                };
            });
            
            new Chart(segmentCtx{{ $segmentIndex }}, {
                type: 'line',
                data: {
                    labels: segmentMonthLabels{{ $segmentIndex }},
                    datasets: segmentChartDatasets{{ $segmentIndex }}
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12,
                                    weight: '400'
                                },
                                padding: 15,
                                usePointStyle: false,
                                boxWidth: 14,
                                boxHeight: 14
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        const yenValue = context.parsed.y * 1000; // 千円単位から円に変換
                                        
                                        // データの範囲に応じて適切な単位を選択（切り上げ）
                                        if (Math.abs(yenValue) >= 100000000) {
                                            const hundredMillion = yenValue / 100000000;
                                            label += roundToOneDecimal(hundredMillion).toFixed(1) + '億円';
                                        } else if (Math.abs(yenValue) >= 10000000) {
                                            const tenMillion = yenValue / 10000000;
                                            label += roundToOneDecimal(tenMillion).toFixed(1) + '千万円';
                                        } else if (Math.abs(yenValue) >= 1000000) {
                                            const million = yenValue / 1000000;
                                            label += roundToOneDecimal(million).toFixed(1) + '百万円';
                                        } else {
                                            label += Math.round(context.parsed.y).toLocaleString() + '千円';
                                        }
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
                            title: {
                                display: false
                            },
                            ticks: {
                                callback: function(value) {
                                    if (value === null || value === undefined) return '';
                                    const yenValue = value * 1000; // 千円単位から円に変換
                                    
                                    // データの範囲に応じて適切な単位を選択（切り上げ）
                                    if (Math.abs(yenValue) >= 100000000) {
                                        // 1億円以上は億円単位
                                        const hundredMillion = yenValue / 100000000;
                                        return roundToOneDecimal(hundredMillion).toFixed(1) + '億';
                                    } else if (Math.abs(yenValue) >= 10000000) {
                                        // 1千万円以上は千万円単位
                                        const tenMillion = yenValue / 10000000;
                                        return roundToOneDecimal(tenMillion).toFixed(1) + '千万';
                                    } else if (Math.abs(yenValue) >= 1000000) {
                                        // 100万円以上は百万円単位
                                        const million = yenValue / 1000000;
                                        return roundToOneDecimal(million).toFixed(1) + '百万';
                                    } else {
                                        // それ以下は千円単位
                                        return Math.round(value).toLocaleString() + '千';
                                    }
                                },
                                font: {
                                    size: 10
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
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
    }
    @endif
    @endforeach
    
    // 数量指標系セグメント別グラフ
    @if(isset($operationSegments) && !empty($operationSegments))
    @foreach($operationSegments as $segmentIndex => $segment)
        @if(isset($segment['chartData']) && !empty($segment['chartData']['months']) && count($segment['chartData']['months']) > 0)
        const operationSegmentChart{{ $segmentIndex }}Canvas = document.getElementById('operationSegmentChart{{ $segmentIndex }}');
        if (operationSegmentChart{{ $segmentIndex }}Canvas) {
            const operationSegmentCtx{{ $segmentIndex }} = operationSegmentChart{{ $segmentIndex }}Canvas.getContext('2d');
            
            const operationSegmentMonthLabels{{ $segmentIndex }} = @json($segment['chartData']['months']);
            const operationSegmentDatasets{{ $segmentIndex }} = @json($segment['chartData']['datasets']);
            
            const operationSegmentChartDatasets{{ $segmentIndex }} = operationSegmentDatasets{{ $segmentIndex }}.map((dataset, index) => {
                // Apple Design風カラーパレット（洗練された彩度・明度）
                const colors = [
                    { bg: 'rgba(0, 113, 227, 0.75)', border: '#0071E3' },      // Apple Blue
                    { bg: 'rgba(36, 199, 97, 0.75)', border: '#24C761' },      // Apple Green
                    { bg: 'rgba(255, 159, 10, 0.75)', border: '#FF9F0A' },     // Apple Orange
                    { bg: 'rgba(175, 82, 222, 0.75)', border: '#AF52DE' },     // Apple Purple
                    { bg: 'rgba(251, 77, 61, 0.75)', border: '#FB4D3D' },      // Apple Red
                    { bg: 'rgba(90, 200, 250, 0.75)', border: '#5AC8FA' },     // Apple Teal
                    { bg: 'rgba(255, 214, 10, 0.75)', border: '#FFD60A' },     // Apple Yellow
                ];
                const color = colors[index % colors.length];
                
                return {
                    label: dataset.label,
                    data: dataset.data,
                    type: 'line',
                    borderColor: color.border,
                    backgroundColor: color.bg,
                    borderWidth: 2,
                    fill: false,
                    tension: 0,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: color.border,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1,
                    yAxisID: 'y'
                };
            });
            
            new Chart(operationSegmentCtx{{ $segmentIndex }}, {
                type: 'line',
                data: {
                    labels: operationSegmentMonthLabels{{ $segmentIndex }},
                    datasets: operationSegmentChartDatasets{{ $segmentIndex }}
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12,
                                    weight: '400'
                                },
                                padding: 15,
                                usePointStyle: false,
                                boxWidth: 14,
                                boxHeight: 14
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += DashboardHelper.formatAmountForGraph(context.parsed.y);
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
                            title: {
                                display: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return DashboardHelper.formatAmountForGraph(value);
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
        }
        @endif
    @endforeach
    @endif
    
    
    // KPIカードの月プルダウンの年度を更新する関数（グローバルスコープで定義）
    window.updateKpiMonthFormFiscalYear = function() {
        const mainGraphForm = document.getElementById('mainGraphFiscalYearForm');
        if (mainGraphForm) {
            const fiscalYearSelect = mainGraphForm.querySelector('select[name="fiscal_year_id"]');
            if (fiscalYearSelect) {
                const kpiMonthFormFiscalYear = document.getElementById('kpiMonthFormFiscalYear');
                if (kpiMonthFormFiscalYear) {
                    kpiMonthFormFiscalYear.value = fiscalYearSelect.value;
                }
            }
        }
    };
    
    // 年度プルダウンが変更されたときに、KPIカードの月プルダウンの年度も更新
    const mainGraphFiscalYearForm = document.getElementById('mainGraphFiscalYearForm');
    if (mainGraphFiscalYearForm) {
        const fiscalYearSelect = mainGraphFiscalYearForm.querySelector('select[name="fiscal_year_id"]');
        if (fiscalYearSelect) {
            fiscalYearSelect.addEventListener('change', function() {
                window.updateKpiMonthFormFiscalYear();
            });
        }
    }
    
    // ========================================
    // アコーディオン状態保持機能
    // ========================================
    
    // 現在開いているアコーディオンのIDを取得
    function getOpenAccordionIds() {
        const openAccordions = document.querySelectorAll('.accordion-collapse.show');
        return Array.from(openAccordions).map(el => el.id);
    }
    
    // URLパラメータからアコーディオンIDを取得
    function getAccordionFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const accordionParam = urlParams.get('open_accordion');
        return accordionParam ? accordionParam.split(',') : [];
    }
    
    // ページロード時にアコーディオンを復元
    const accordionIds = getAccordionFromUrl();
    if (accordionIds.length > 0) {
        accordionIds.forEach(accordionId => {
            const accordionElement = document.getElementById(accordionId);
            if (accordionElement) {
                // Bootstrap 5のCollapseインスタンスを使用
                const bsCollapse = new bootstrap.Collapse(accordionElement, { toggle: false });
                bsCollapse.show();
                
                // アコーディオンが開いた後にスクロール
                setTimeout(() => {
                    const accordionHeader = accordionElement.previousElementSibling;
                    if (accordionHeader) {
                        // ヘッダーの位置にスクロール（少し上にオフセット）
                        const headerRect = accordionHeader.getBoundingClientRect();
                        const scrollTop = window.pageYOffset + headerRect.top - 100;
                        window.scrollTo({ top: scrollTop, behavior: 'instant' });
                    }
                }, 100);
            }
        });
    }
    
    // フォーム送信時に開いているアコーディオンを保持
    document.querySelectorAll('form').forEach(form => {
        const fiscalYearSelect = form.querySelector('select[name="fiscal_year_id"]');
        if (fiscalYearSelect && !form.id.includes('mainGraphFiscalYear')) {
            // アコーディオン内のフォームかどうかを確認
            const accordionBody = form.closest('.accordion-body');
            if (accordionBody) {
                const accordionCollapse = accordionBody.closest('.accordion-collapse');
                if (accordionCollapse) {
                    fiscalYearSelect.addEventListener('change', function(e) {
                        e.preventDefault();
                        
                        // 現在開いているアコーディオンのIDを取得
                        const openIds = getOpenAccordionIds();
                        
                        // フォームにhidden inputを追加
                        let hiddenInput = form.querySelector('input[name="open_accordion"]');
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'open_accordion';
                            form.appendChild(hiddenInput);
                        }
                        hiddenInput.value = openIds.join(',');
                        
                        // フォームを送信
                        form.submit();
                    });
                    
                    // 元のonchangeを削除
                    fiscalYearSelect.removeAttribute('onchange');
                }
            }
        }
    });
});

</script>
@endif
@endpush
