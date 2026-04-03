<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * さくらインターネット対応: storage/app/public のファイルへのURLを取得
     * storage:link が使えない場合でも動作する
     */
    public static function publicUrl(string $path): string
    {
        // public/ プレフィックスを削除
        $path = str_replace('public/', '', $path);
        
        // さくらインターネットでは、storage/app/public を直接公開できない場合があるため、
        // コントローラー経由でアクセスするURLを返す
        return route('storage.file', ['path' => $path]);
    }

    /**
     * ファイルが存在するかチェック
     */
    public static function exists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * ファイルのフルパスを取得
     */
    public static function fullPath(string $path): string
    {
        return Storage::disk('public')->path($path);
    }
}























