<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageController extends Controller
{
    /**
     * さくらインターネット対応: storage/app/public のファイルを配信
     * storage:link が使えない場合の代替手段
     */
    public function file(Request $request, string $path): BinaryFileResponse
    {
        // セキュリティ: パストラバーサル攻撃を防ぐ
        $path = str_replace('..', '', $path);
        $path = ltrim($path, '/');
        
        $fullPath = Storage::disk('public')->path($path);
        
        if (!file_exists($fullPath)) {
            abort(404, 'ファイルが見つかりません。');
        }

        // MIMEタイプを判定
        $mimeType = mime_content_type($fullPath);
        
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
        ]);
    }
}























