<?php

namespace App\Helpers;

class DashboardHelper
{
    /**
     * 前月比を計算（%）
     */
    public static function calculateMonthOverMonth(?float $current, ?float $previous): ?float
    {
        if ($current === null || $previous === null || $previous == 0) {
            return null;
        }
        
        return (($current - $previous) / $previous) * 100;
    }

    /**
     * 前月比の表示用文字列を取得
     */
    public static function formatMonthOverMonth(?float $mom): string
    {
        if ($mom === null) {
            return '-';
        }
        
        $sign = $mom >= 0 ? '+' : '';
        $rounded = round($mom, 1);
        return $sign . number_format($rounded, 1) . '%';
    }

    /**
     * 前月比のCSSクラスを取得（色分け用）
     */
    public static function getMomColorClass(?float $mom, bool $reverse = false): string
    {
        if ($mom === null) {
            return 'text-muted';
        }
        
        // reverse=trueの場合、プラスが悪い指標（例：原価、費用）
        if ($reverse) {
            return $mom > 0 ? 'text-danger' : ($mom < 0 ? 'text-success' : 'text-muted');
        }
        
        return $mom > 0 ? 'text-success' : ($mom < 0 ? 'text-danger' : 'text-muted');
    }

    /**
     * 前月比のアイコンを取得
     */
    public static function getMomIcon(?float $mom, bool $reverse = false): string
    {
        if ($mom === null || $mom == 0) {
            return '';
        }
        
        // reverse=trueの場合、プラスが悪い指標
        if ($reverse) {
            return $mom > 0 ? 'bi-arrow-up' : 'bi-arrow-down';
        }
        
        return $mom > 0 ? 'bi-arrow-up' : 'bi-arrow-down';
    }

    /**
     * 金額を千円単位でフォーマット（単位付き）
     */
    public static function formatCurrency(?float $value, int $decimals = 0): string
    {
        if ($value === null) {
            return '-';
        }
        
        $rounded = round($value, $decimals);
        
        return number_format($rounded, $decimals) . '千円';
    }

    /**
     * 金額を実数（円）でフォーマット（KPIカード用）
     */
    public static function formatCurrencyYen(?float $value, int $decimals = 0): string
    {
        if ($value === null) {
            return '-';
        }
        
        $yenValue = $value * 1000;
        $rounded = round($yenValue, $decimals);
        
        $formatted = number_format($rounded, $decimals);
        return $formatted . '<span style="font-size: 0.75rem;">円</span>';
    }

    /**
     * 金額を千円単位でフォーマット（単位なし）
     */
    public static function formatCurrencyWithoutUnit(?float $value, int $decimals = 0): string
    {
        if ($value === null) {
            return '-';
        }
        
        $rounded = round($value, $decimals);
        
        return number_format($rounded, $decimals);
    }

    /**
     * 数値をフォーマット
     */
    public static function formatNumber(?float $value, int $decimals = 0): string
    {
        if ($value === null) {
            return '-';
        }
        
        $rounded = round($value, $decimals);
        
        return number_format($rounded, $decimals);
    }

    /**
     * スパークラインデータを生成（直近6ヶ月）
     */
    public static function generateSparklineData(array $monthlyValues): array
    {
        // 直近6ヶ月のデータを取得
        $recent = array_slice($monthlyValues, -6);
        
        if (empty($recent)) {
            return [];
        }
        
        return $recent;
    }

    /**
     * ビジネスセグメントの分類
     */
    public static function categorizeBusinessSegment(string $categoryName): ?string
    {
        if ($categoryName === '全体') {
            return '収益';
        } elseif ($categoryName === '新規開業') {
            return '新規開業';
        } elseif ($categoryName === 'ランニング') {
            return 'ランニング';
        } elseif (in_array($categoryName, ['VRロイヤリティ', 'はぐWeb', 'はぐパス', 'はぐくみファイナンス'])) {
            return '新規事業';
        }
        
        return null;
    }
}

