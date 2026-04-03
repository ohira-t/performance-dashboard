<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * 文字列をサニタイズ（XSS対策）
     * HTMLタグを除去し、特殊文字をエスケープ
     */
    public static function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        
        // HTMLタグを除去
        $value = strip_tags($value);
        
        // 制御文字を除去（改行・タブは許可）
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // 前後の空白を除去
        $value = trim($value);
        
        return $value;
    }
    
    /**
     * 配列内の全ての文字列をサニタイズ
     */
    public static function sanitizeArray(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return self::sanitizeString($value);
            }
            if (is_array($value)) {
                return self::sanitizeArray($value);
            }
            return $value;
        }, $data);
    }
    
    /**
     * URLをサニタイズ（javascript:などの危険なスキームを除去）
     */
    public static function sanitizeUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }
        
        $url = trim($url);
        
        // 許可するスキームのみ
        $allowedSchemes = ['http', 'https'];
        $parsed = parse_url($url);
        
        if (isset($parsed['scheme']) && !in_array(strtolower($parsed['scheme']), $allowedSchemes)) {
            return null;
        }
        
        // スキームがない場合はhttps://を追加
        if (!isset($parsed['scheme'])) {
            $url = 'https://' . ltrim($url, '/');
        }
        
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    
    /**
     * 数値のみを許可
     */
    public static function sanitizeNumeric(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        // カンマや通貨記号を除去
        $value = str_replace([',', '¥', '￥', '$', '%'], '', trim($value));
        
        if (!is_numeric($value)) {
            return null;
        }
        
        return (float) $value;
    }
}

