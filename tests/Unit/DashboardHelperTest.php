<?php

namespace Tests\Unit;

use App\Helpers\DashboardHelper;
use PHPUnit\Framework\TestCase;

class DashboardHelperTest extends TestCase
{
    // =========================================================================
    // calculateMonthOverMonth
    // =========================================================================

    public function test_calculate_mom_normal_increase(): void
    {
        // 100 → 120 = +20%
        $result = DashboardHelper::calculateMonthOverMonth(120.0, 100.0);
        $this->assertEqualsWithDelta(20.0, $result, 0.0001);
    }

    public function test_calculate_mom_normal_decrease(): void
    {
        // 100 → 80 = -20%
        $result = DashboardHelper::calculateMonthOverMonth(80.0, 100.0);
        $this->assertEqualsWithDelta(-20.0, $result, 0.0001);
    }

    public function test_calculate_mom_no_change(): void
    {
        $result = DashboardHelper::calculateMonthOverMonth(100.0, 100.0);
        $this->assertEqualsWithDelta(0.0, $result, 0.0001);
    }

    public function test_calculate_mom_negative_values(): void
    {
        // -100 → -80: ((-80)-(-100))/(-100)*100 = 20/(-100)*100 = -20%
        // 分母が負のため、赤字縮小でも数学的にはマイナスになる
        $result = DashboardHelper::calculateMonthOverMonth(-80.0, -100.0);
        $this->assertEqualsWithDelta(-20.0, $result, 0.0001);
    }

    public function test_calculate_mom_negative_to_more_negative(): void
    {
        // -100 → -120: ((-120)-(-100))/(-100)*100 = (-20)/(-100)*100 = +20%
        $result = DashboardHelper::calculateMonthOverMonth(-120.0, -100.0);
        $this->assertEqualsWithDelta(20.0, $result, 0.0001);
    }

    public function test_calculate_mom_positive_to_negative(): void
    {
        // 100 → -50 = -150%
        $result = DashboardHelper::calculateMonthOverMonth(-50.0, 100.0);
        $this->assertEqualsWithDelta(-150.0, $result, 0.0001);
    }

    public function test_calculate_mom_previous_is_zero(): void
    {
        $result = DashboardHelper::calculateMonthOverMonth(100.0, 0.0);
        $this->assertNull($result);
    }

    public function test_calculate_mom_current_is_null(): void
    {
        $result = DashboardHelper::calculateMonthOverMonth(null, 100.0);
        $this->assertNull($result);
    }

    public function test_calculate_mom_previous_is_null(): void
    {
        $result = DashboardHelper::calculateMonthOverMonth(100.0, null);
        $this->assertNull($result);
    }

    public function test_calculate_mom_both_null(): void
    {
        $result = DashboardHelper::calculateMonthOverMonth(null, null);
        $this->assertNull($result);
    }

    public function test_calculate_mom_small_values(): void
    {
        // 0.5 → 0.75 = +50%
        $result = DashboardHelper::calculateMonthOverMonth(0.75, 0.5);
        $this->assertEqualsWithDelta(50.0, $result, 0.0001);
    }

    public function test_calculate_mom_large_values(): void
    {
        // 1000000 → 1500000 = +50%
        $result = DashboardHelper::calculateMonthOverMonth(1500000.0, 1000000.0);
        $this->assertEqualsWithDelta(50.0, $result, 0.0001);
    }

    // =========================================================================
    // formatMonthOverMonth
    // =========================================================================

    public function test_format_mom_positive(): void
    {
        $this->assertSame('+20.0%', DashboardHelper::formatMonthOverMonth(20.0));
    }

    public function test_format_mom_negative(): void
    {
        $this->assertSame('-15.3%', DashboardHelper::formatMonthOverMonth(-15.3));
    }

    public function test_format_mom_zero(): void
    {
        $this->assertSame('+0.0%', DashboardHelper::formatMonthOverMonth(0.0));
    }

    public function test_format_mom_null(): void
    {
        $this->assertSame('-', DashboardHelper::formatMonthOverMonth(null));
    }

    public function test_format_mom_rounds_to_one_decimal(): void
    {
        // 12.456 → 12.5（四捨五入）
        $this->assertSame('+12.5%', DashboardHelper::formatMonthOverMonth(12.456));
    }

    public function test_format_mom_rounds_down(): void
    {
        // 12.44 → 12.4
        $this->assertSame('+12.4%', DashboardHelper::formatMonthOverMonth(12.44));
    }

    public function test_format_mom_negative_rounds(): void
    {
        // -7.85 → round(-7.85, 1) = -7.9
        $this->assertSame('-7.9%', DashboardHelper::formatMonthOverMonth(-7.85));
    }

    // =========================================================================
    // getMomColorClass
    // =========================================================================

    public function test_color_class_positive_normal(): void
    {
        $this->assertSame('text-success', DashboardHelper::getMomColorClass(10.0));
    }

    public function test_color_class_negative_normal(): void
    {
        $this->assertSame('text-danger', DashboardHelper::getMomColorClass(-10.0));
    }

    public function test_color_class_zero(): void
    {
        $this->assertSame('text-muted', DashboardHelper::getMomColorClass(0.0));
    }

    public function test_color_class_null(): void
    {
        $this->assertSame('text-muted', DashboardHelper::getMomColorClass(null));
    }

    public function test_color_class_positive_reversed(): void
    {
        // reverse=true: プラスは悪い（費用増加）→ danger
        $this->assertSame('text-danger', DashboardHelper::getMomColorClass(10.0, true));
    }

    public function test_color_class_negative_reversed(): void
    {
        // reverse=true: マイナスは良い（費用削減）→ success
        $this->assertSame('text-success', DashboardHelper::getMomColorClass(-10.0, true));
    }

    // =========================================================================
    // getMomIcon
    // =========================================================================

    public function test_icon_positive(): void
    {
        $this->assertSame('bi-arrow-up', DashboardHelper::getMomIcon(10.0));
    }

    public function test_icon_negative(): void
    {
        $this->assertSame('bi-arrow-down', DashboardHelper::getMomIcon(-10.0));
    }

    public function test_icon_zero(): void
    {
        $this->assertSame('', DashboardHelper::getMomIcon(0.0));
    }

    public function test_icon_null(): void
    {
        $this->assertSame('', DashboardHelper::getMomIcon(null));
    }

    // =========================================================================
    // formatCurrency（千円単位・単位付き）
    // =========================================================================

    public function test_format_currency_positive(): void
    {
        $this->assertSame('1,234千円', DashboardHelper::formatCurrency(1234.0));
    }

    public function test_format_currency_with_decimals(): void
    {
        $this->assertSame('1,234.5千円', DashboardHelper::formatCurrency(1234.5, 1));
    }

    public function test_format_currency_negative(): void
    {
        $this->assertSame('-500千円', DashboardHelper::formatCurrency(-500.0));
    }

    public function test_format_currency_null(): void
    {
        $this->assertSame('-', DashboardHelper::formatCurrency(null));
    }

    public function test_format_currency_zero(): void
    {
        $this->assertSame('0千円', DashboardHelper::formatCurrency(0.0));
    }

    public function test_format_currency_rounds(): void
    {
        // 1234.56 → 小数0桁 → 1,235
        $this->assertSame('1,235千円', DashboardHelper::formatCurrency(1234.56));
    }

    public function test_format_currency_large_number(): void
    {
        $this->assertSame('1,234,567千円', DashboardHelper::formatCurrency(1234567.0));
    }

    // =========================================================================
    // formatCurrencyYen（円単位・千円から変換）
    // =========================================================================

    public function test_format_currency_yen_basic(): void
    {
        // 1000千円 → 1,000,000円
        $result = DashboardHelper::formatCurrencyYen(1000.0);
        $this->assertSame('1,000,000<span style="font-size: 0.75rem;">円</span>', $result);
    }

    public function test_format_currency_yen_null(): void
    {
        $this->assertSame('-', DashboardHelper::formatCurrencyYen(null));
    }

    public function test_format_currency_yen_zero(): void
    {
        $result = DashboardHelper::formatCurrencyYen(0.0);
        $this->assertSame('0<span style="font-size: 0.75rem;">円</span>', $result);
    }

    public function test_format_currency_yen_small(): void
    {
        // 1.5千円 → 1,500円
        $result = DashboardHelper::formatCurrencyYen(1.5);
        $this->assertSame('1,500<span style="font-size: 0.75rem;">円</span>', $result);
    }

    public function test_format_currency_yen_negative(): void
    {
        // -500千円 → -500,000円
        $result = DashboardHelper::formatCurrencyYen(-500.0);
        $this->assertSame('-500,000<span style="font-size: 0.75rem;">円</span>', $result);
    }

    // =========================================================================
    // formatCurrencyWithoutUnit
    // =========================================================================

    public function test_format_currency_without_unit(): void
    {
        $this->assertSame('1,234', DashboardHelper::formatCurrencyWithoutUnit(1234.0));
    }

    public function test_format_currency_without_unit_null(): void
    {
        $this->assertSame('-', DashboardHelper::formatCurrencyWithoutUnit(null));
    }

    public function test_format_currency_without_unit_with_decimals(): void
    {
        $this->assertSame('1,234.56', DashboardHelper::formatCurrencyWithoutUnit(1234.56, 2));
    }

    // =========================================================================
    // formatNumber
    // =========================================================================

    public function test_format_number_basic(): void
    {
        $this->assertSame('1,234', DashboardHelper::formatNumber(1234.0));
    }

    public function test_format_number_null(): void
    {
        $this->assertSame('-', DashboardHelper::formatNumber(null));
    }

    public function test_format_number_zero(): void
    {
        $this->assertSame('0', DashboardHelper::formatNumber(0.0));
    }

    public function test_format_number_negative(): void
    {
        $this->assertSame('-1,234', DashboardHelper::formatNumber(-1234.0));
    }

    public function test_format_number_with_decimals(): void
    {
        $this->assertSame('1,234.57', DashboardHelper::formatNumber(1234.567, 2));
    }

    // =========================================================================
    // generateSparklineData
    // =========================================================================

    public function test_sparkline_returns_last_6_months(): void
    {
        $data = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120];
        $result = DashboardHelper::generateSparklineData($data);
        $this->assertSame([70, 80, 90, 100, 110, 120], $result);
    }

    public function test_sparkline_fewer_than_6(): void
    {
        $data = [10, 20, 30];
        $result = DashboardHelper::generateSparklineData($data);
        $this->assertSame([10, 20, 30], $result);
    }

    public function test_sparkline_empty(): void
    {
        $result = DashboardHelper::generateSparklineData([]);
        $this->assertSame([], $result);
    }

    public function test_sparkline_exactly_6(): void
    {
        $data = [10, 20, 30, 40, 50, 60];
        $result = DashboardHelper::generateSparklineData($data);
        $this->assertSame([10, 20, 30, 40, 50, 60], $result);
    }

    // =========================================================================
    // categorizeBusinessSegment
    // =========================================================================

    public function test_categorize_zentai(): void
    {
        $this->assertSame('収益', DashboardHelper::categorizeBusinessSegment('全体'));
    }

    public function test_categorize_shinki_kaigyo(): void
    {
        $this->assertSame('新規開業', DashboardHelper::categorizeBusinessSegment('新規開業'));
    }

    public function test_categorize_running(): void
    {
        $this->assertSame('ランニング', DashboardHelper::categorizeBusinessSegment('ランニング'));
    }

    public function test_categorize_new_businesses(): void
    {
        $this->assertSame('新規事業', DashboardHelper::categorizeBusinessSegment('VRロイヤリティ'));
        $this->assertSame('新規事業', DashboardHelper::categorizeBusinessSegment('はぐWeb'));
        $this->assertSame('新規事業', DashboardHelper::categorizeBusinessSegment('はぐパス'));
        $this->assertSame('新規事業', DashboardHelper::categorizeBusinessSegment('はぐくみファイナンス'));
    }

    public function test_categorize_unknown(): void
    {
        $this->assertNull(DashboardHelper::categorizeBusinessSegment('その他'));
    }

    // =========================================================================
    // 統合テスト: calculateMonthOverMonth + formatMonthOverMonth
    // =========================================================================

    public function test_end_to_end_mom_calculation_and_format(): void
    {
        // 100 → 123.4 = +23.4%
        $mom = DashboardHelper::calculateMonthOverMonth(123.4, 100.0);
        $formatted = DashboardHelper::formatMonthOverMonth($mom);
        $this->assertSame('+23.4%', $formatted);
    }

    public function test_end_to_end_mom_decrease_and_format(): void
    {
        // 1000 → 850 = -15.0%
        $mom = DashboardHelper::calculateMonthOverMonth(850.0, 1000.0);
        $formatted = DashboardHelper::formatMonthOverMonth($mom);
        $this->assertSame('-15.0%', $formatted);
    }

    public function test_end_to_end_mom_null_previous(): void
    {
        $mom = DashboardHelper::calculateMonthOverMonth(100.0, null);
        $formatted = DashboardHelper::formatMonthOverMonth($mom);
        $this->assertSame('-', $formatted);
    }

    public function test_end_to_end_mom_with_color_and_icon(): void
    {
        // 500 → 600 = +20%
        $mom = DashboardHelper::calculateMonthOverMonth(600.0, 500.0);
        $this->assertSame('text-success', DashboardHelper::getMomColorClass($mom));
        $this->assertSame('bi-arrow-up', DashboardHelper::getMomIcon($mom));

        // 費用が増加した場合（reverse）
        $this->assertSame('text-danger', DashboardHelper::getMomColorClass($mom, true));
    }
}
