<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission 権限名
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 管理者は全ての権限を持つ
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 権限チェック
        if (!$user->hasPermission($permission)) {
            abort(403, 'この操作を実行する権限がありません。');
        }

        return $next($request);
    }
}
