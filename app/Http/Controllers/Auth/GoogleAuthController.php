<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Google認証へのリダイレクト
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Google認証のコールバック処理
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Google Workspaceのドメイン制限チェック
            $hostedDomain = config('services.google.hosted_domain');
            if ($hostedDomain && $googleUser->user['hd'] !== $hostedDomain) {
                return redirect()->route('login')
                    ->with('error', '許可されていないドメインです。');
            }

            // 事前登録されているユーザーを検索（メールアドレスで）
            $user = User::where('email', $googleUser->email)->first();

            // 事前登録されていない場合はログイン拒否
            if (!$user) {
                \Log::warning('未登録ユーザーのログイン試行', [
                    'email' => $googleUser->email,
                    'name' => $googleUser->name,
                ]);
                return redirect()->route('login')
                    ->with('error', 'このアカウントはシステムに登録されていません。管理者に連絡してください。');
            }

            // 無効なユーザーの場合はログイン拒否
            if (!$user->is_active) {
                return redirect()->route('login')
                    ->with('error', 'このアカウントは無効化されています。管理者に連絡してください。');
            }

            // Google IDを設定または更新
            $user->update([
                'google_id' => $googleUser->id,
                'name' => $googleUser->name,
                'avatar' => $googleUser->avatar,
                'last_login_at' => now(),
            ]);

            // ログイン
            Auth::login($user, true);

            // 役割に応じてリダイレクト先を変更
            $redirectRoute = $this->getRedirectRouteForUser($user);
            return redirect()->intended($redirectRoute);
        } catch (\Exception $e) {
            \Log::error('Google認証エラー: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')
                ->with('error', '認証に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * ユーザーの役割に応じたリダイレクト先を取得
     */
    private function getRedirectRouteForUser(User $user): string
    {
        return $user->getLoginRedirectUrl();
    }

    /**
     * ログアウト
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
