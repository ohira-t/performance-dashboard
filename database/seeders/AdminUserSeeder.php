<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 環境変数から管理者のメールアドレスを取得
        $adminEmail = env('ADMIN_EMAIL');
        
        if (!$adminEmail) {
            $this->command->warn('ADMIN_EMAIL環境変数が設定されていません。');
            $this->command->info('以下のコマンドで管理者ユーザーを作成できます:');
            $this->command->info('php artisan tinker');
            $this->command->info('$user = \App\Models\User::create(["email" => "your-email@example.com", "name" => "Your Name", "is_active" => true]);');
            $this->command->info('$user->roles()->attach(\App\Models\Role::where("name", "admin")->first()->id);');
            return;
        }

        // 既にユーザーが存在するかチェック
        $existingUser = User::where('email', $adminEmail)->first();
        
        if ($existingUser) {
            $this->command->info("ユーザー {$adminEmail} は既に存在します。");
            
            // 管理者ロールが付与されているかチェック
            if (!$existingUser->hasRole('admin')) {
                $adminRole = Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $existingUser->roles()->attach($adminRole->id);
                    $this->command->info("管理者ロールを付与しました。");
                }
            } else {
                $this->command->info("既に管理者ロールが付与されています。");
            }
            return;
        }

        // 管理者ユーザーを作成
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('管理者ロールが見つかりません。先にRolePermissionSeederを実行してください。');
            return;
        }

        $user = User::create([
            'email' => $adminEmail,
            'name' => env('ADMIN_NAME', 'Administrator'),
            'is_active' => true,
        ]);

        $user->roles()->attach($adminRole->id);

        $this->command->info("管理者ユーザー {$adminEmail} を作成しました。");
    }
}
