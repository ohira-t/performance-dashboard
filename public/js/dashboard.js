/**
 * Dashboard chart & UI logic
 * Blade側で window.__dashboardConfig にデータを注入して使う
 */
(function () {
    'use strict';

    const COLORS = [
        { bg: 'rgba(0, 113, 227, 0.75)', border: '#0071E3' },
        { bg: 'rgba(36, 199, 97, 0.75)', border: '#24C761' },
        { bg: 'rgba(255, 159, 10, 0.75)', border: '#FF9F0A' },
        { bg: 'rgba(175, 82, 222, 0.75)', border: '#AF52DE' },
        { bg: 'rgba(251, 77, 61, 0.75)', border: '#FB4D3D' },
        { bg: 'rgba(90, 200, 250, 0.75)', border: '#5AC8FA' },
        { bg: 'rgba(255, 214, 10, 0.75)', border: '#FFD60A' },
    ];

    function round1(num) {
        return Math.round(num * 10) / 10;
    }

    function formatCurrencyHtml(value) {
        if (value === 0 || value === null || value === undefined || isNaN(value)) {
            return '<span class="text-muted">-</span>';
        }
        var yenValue = value * 1000;
        var formatted = new Intl.NumberFormat('ja-JP').format(yenValue);
        return formatted + '<span style="font-size: 0.75rem;">円</span>';
    }

    function formatYenAxis(value) {
        if (value === null || value === undefined) return '';
        var yen = value * 1000;
        var abs = Math.abs(yen);
        if (abs >= 1e8) return round1(yen / 1e8).toFixed(1) + '億';
        if (abs >= 1e7) return round1(yen / 1e7).toFixed(1) + '千万';
        if (abs >= 1e6) return round1(yen / 1e6).toFixed(1) + '百万';
        return Math.round(value).toLocaleString() + '千';
    }

    function formatYenTooltip(value) {
        var yen = value * 1000;
        var abs = Math.abs(yen);
        if (abs >= 1e8) return round1(yen / 1e8).toFixed(1) + '億円';
        if (abs >= 1e7) return round1(yen / 1e7).toFixed(1) + '千万円';
        if (abs >= 1e6) return round1(yen / 1e6).toFixed(1) + '百万円';
        return Math.round(value).toLocaleString() + '千円';
    }

    function formatQuantity(value) {
        if (value === null || value === undefined) return '-';
        var abs = Math.abs(value);
        if (abs >= 1000) return round1(value / 1000).toFixed(1) + '千';
        return Math.round(value).toFixed(0);
    }

    function buildLineDatasets(rawDatasets) {
        return rawDatasets.map(function (ds, i) {
            var c = COLORS[i % COLORS.length];
            return {
                label: ds.label,
                data: ds.data,
                type: 'line',
                borderColor: c.border,
                backgroundColor: c.bg,
                borderWidth: 2,
                fill: false,
                tension: 0,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: c.border,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1,
                yAxisID: 'y'
            };
        });
    }

    var commonLegend = {
        display: true, position: 'top',
        labels: { font: { size: 12, weight: '400' }, padding: 15, usePointStyle: false, boxWidth: 14, boxHeight: 14 }
    };

    function createCurrencyLineChart(canvasId, labels, rawDatasets) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: { labels: labels, datasets: buildLineDatasets(rawDatasets) },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    title: { display: false }, legend: commonLegend,
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var l = ctx.dataset.label || '';
                                if (l) l += ': ';
                                if (ctx.parsed.y !== null) l += formatYenTooltip(ctx.parsed.y);
                                return l;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear', display: true, position: 'left',
                        ticks: { callback: formatYenAxis, font: { size: 10 } },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    function createQuantityLineChart(canvasId, labels, rawDatasets) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: { labels: labels, datasets: buildLineDatasets(rawDatasets) },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    title: { display: false }, legend: commonLegend,
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var l = ctx.dataset.label || '';
                                if (l) l += ': ';
                                if (ctx.parsed.y !== null) l += formatQuantity(ctx.parsed.y);
                                return l;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear', display: true, position: 'left',
                        ticks: { callback: formatQuantity, font: { size: 11 } },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 12, weight: '400' }, color: '#1d1d1f' } }
                }
            }
        });
    }

    // --------------------------------------------------
    // 初期化（DOMContentLoaded）
    // --------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        var cfg = window.__dashboardConfig;
        if (!cfg) return;

        // --- 期間切り替えタブ ---
        var periodTabs = document.querySelectorAll('.period-tab');
        var monthlyCards = document.getElementById('monthlyKpiCards');
        var periodCards = document.getElementById('periodKpiCards');
        var periodLabel = document.getElementById('periodLabel');
        var kpiMonthSelect = document.getElementById('kpiMonthSelect');

        periodTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var period = this.dataset.period;
                periodTabs.forEach(function (t) {
                    t.style.background = 'white';
                    t.style.color = 'var(--text-primary)';
                    t.classList.remove('active');
                });
                this.style.background = 'var(--accent-color)';
                this.style.color = 'white';
                this.classList.add('active');

                if (periodLabel) periodLabel.textContent = cfg.periodLabels[period] || '';
                if (kpiMonthSelect) kpiMonthSelect.closest('form').style.display = period === 'monthly' ? 'block' : 'none';

                if (period === 'monthly') {
                    if (monthlyCards) monthlyCards.style.display = 'flex';
                    if (periodCards) periodCards.style.display = 'none';
                } else {
                    if (monthlyCards) monthlyCards.style.display = 'none';
                    if (periodCards) {
                        periodCards.style.display = 'flex';
                        document.querySelectorAll('.period-value').forEach(function (el) {
                            var isReverse = el.dataset.reverse === 'true';
                            var value, prevValue;
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
                            el.innerHTML = formatCurrencyHtml(value);
                            var yoyEl = el.closest('.card-body').querySelector('.period-yoy');
                            if (yoyEl) {
                                if (prevValue && !isNaN(prevValue) && prevValue !== 0 && value && !isNaN(value)) {
                                    var pct = ((value - prevValue) / prevValue) * 100;
                                    var pos = pct >= 0;
                                    var good = isReverse ? !pos : pos;
                                    var cls = good ? 'text-success' : 'text-danger';
                                    var arrow = pos ? '↑' : '↓';
                                    yoyEl.innerHTML = '前期比: <span class="' + cls + '">' + arrow + Math.abs(pct).toFixed(1) + '%</span>';
                                } else {
                                    yoyEl.innerHTML = '前期比: <span class="text-muted">-</span>';
                                }
                            }
                        });
                    }
                }
            });
        });

        if (kpiMonthSelect) {
            kpiMonthSelect.addEventListener('change', function () {
                document.getElementById('kpiMonthForm').submit();
            });
        }

        // --- メイングラフ ---
        if (cfg.mainChart) {
            var mc = cfg.mainChart;
            var mainCanvas = document.getElementById('performanceChart');
            if (mainCanvas) {
                new Chart(mainCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: mc.months,
                        datasets: [
                            { label: '費用', data: mc.expenses, type: 'bar', stack: 'left', order: 2, backgroundColor: 'rgba(251,77,61,0.88)', borderWidth: 0, yAxisID: 'y' },
                            { label: '経常利益', data: mc.profit, type: 'bar', stack: 'left', order: 1, backgroundColor: 'rgba(36,199,97,0.88)', borderWidth: 0, yAxisID: 'y' },
                            { label: '売上', data: mc.revenue, type: 'bar', stack: 'right', order: 3, backgroundColor: 'rgba(0,113,227,0.88)', borderWidth: 0, yAxisID: 'y' }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            title: { display: false }, legend: commonLegend,
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        var l = ctx.dataset.label || '';
                                        if (l) l += ': ';
                                        if (ctx.parsed.y !== null) {
                                            var yen = ctx.parsed.y * 1000;
                                            l += round1(yen / 1e8).toFixed(1) + '億円';
                                        }
                                        return l;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear', display: true, position: 'left', stacked: true,
                                ticks: {
                                    callback: function (v) { var yen = v * 1000; return round1(yen / 1e8).toFixed(1) + '億'; },
                                    font: { size: 11 }
                                },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: { stacked: true, grid: { display: false }, ticks: { font: { size: 12, weight: '400' }, color: '#1d1d1f' } }
                        }
                    }
                });
            }
        }

        // --- セグメント別グラフ（収益） ---
        if (cfg.segmentCharts) {
            cfg.segmentCharts.forEach(function (sc) {
                createCurrencyLineChart(sc.canvasId, sc.months, sc.datasets);
            });
        }

        // --- セグメント別グラフ（数量） ---
        if (cfg.operationCharts) {
            cfg.operationCharts.forEach(function (sc) {
                createQuantityLineChart(sc.canvasId, sc.months, sc.datasets);
            });
        }

        // --- 年度プルダウン連動 ---
        window.updateKpiMonthFormFiscalYear = function () {
            var mainForm = document.getElementById('mainGraphFiscalYearForm');
            if (mainForm) {
                var sel = mainForm.querySelector('select[name="fiscal_year_id"]');
                var hidden = document.getElementById('kpiMonthFormFiscalYear');
                if (sel && hidden) hidden.value = sel.value;
            }
        };
        var mainFyForm = document.getElementById('mainGraphFiscalYearForm');
        if (mainFyForm) {
            var sel = mainFyForm.querySelector('select[name="fiscal_year_id"]');
            if (sel) sel.addEventListener('change', window.updateKpiMonthFormFiscalYear);
        }

        // --- アコーディオン状態保持 ---
        function getOpenIds() {
            return Array.from(document.querySelectorAll('.accordion-collapse.show')).map(function (el) { return el.id; });
        }
        var urlIds = new URLSearchParams(window.location.search).get('open_accordion');
        if (urlIds) {
            urlIds.split(',').forEach(function (id) {
                var el = document.getElementById(id);
                if (el) {
                    new bootstrap.Collapse(el, { toggle: false }).show();
                    setTimeout(function () {
                        var hdr = el.previousElementSibling;
                        if (hdr) {
                            window.scrollTo({ top: window.pageYOffset + hdr.getBoundingClientRect().top - 100, behavior: 'instant' });
                        }
                    }, 100);
                }
            });
        }
        document.querySelectorAll('form').forEach(function (form) {
            var fySel = form.querySelector('select[name="fiscal_year_id"]');
            if (fySel && !form.id.includes('mainGraphFiscalYear')) {
                var ab = form.closest('.accordion-body');
                if (ab && ab.closest('.accordion-collapse')) {
                    fySel.addEventListener('change', function (e) {
                        e.preventDefault();
                        var hi = form.querySelector('input[name="open_accordion"]');
                        if (!hi) { hi = document.createElement('input'); hi.type = 'hidden'; hi.name = 'open_accordion'; form.appendChild(hi); }
                        hi.value = getOpenIds().join(',');
                        form.submit();
                    });
                    fySel.removeAttribute('onchange');
                }
            }
        });
    });
})();
