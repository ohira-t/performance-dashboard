<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 権限の定義
        $permissions = [
            // ダッシュボード
            ['name' => 'dashboard.view', 'display_name' => 'ダッシュボード閲覧', 'resource' => 'dashboard', 'action' => 'view'],
            
            // 月次実績入力
            ['name' => 'monthly_results.view', 'display_name' => '月次実績閲覧', 'resource' => 'monthly_results', 'action' => 'view'],
            ['name' => 'monthly_results.create', 'display_name' => '月次実績入力', 'resource' => 'monthly_results', 'action' => 'create'],
            ['name' => 'monthly_results.update', 'display_name' => '月次実績更新', 'resource' => 'monthly_results', 'action' => 'update'],
            ['name' => 'monthly_results.export', 'display_name' => '月次実績エクスポート', 'resource' => 'monthly_results', 'action' => 'export'],
            
            // マスタ管理
            ['name' => 'master.categories.view', 'display_name' => 'カテゴリー閲覧', 'resource' => 'master', 'action' => 'view', 'sub_resource' => 'categories'],
            ['name' => 'master.categories.create', 'display_name' => 'カテゴリー作成', 'resource' => 'master', 'action' => 'create', 'sub_resource' => 'categories'],
            ['name' => 'master.categories.update', 'display_name' => 'カテゴリー更新', 'resource' => 'master', 'action' => 'update', 'sub_resource' => 'categories'],
            ['name' => 'master.categories.delete', 'display_name' => 'カテゴリー削除', 'resource' => 'master', 'action' => 'delete', 'sub_resource' => 'categories'],
            
            ['name' => 'master.metrics.view', 'display_name' => '指標マスタ閲覧', 'resource' => 'master', 'action' => 'view', 'sub_resource' => 'metrics'],
            ['name' => 'master.metrics.create', 'display_name' => '指標マスタ作成', 'resource' => 'master', 'action' => 'create', 'sub_resource' => 'metrics'],
            ['name' => 'master.metrics.update', 'display_name' => '指標マスタ更新', 'resource' => 'master', 'action' => 'update', 'sub_resource' => 'metrics'],
            ['name' => 'master.metrics.delete', 'display_name' => '指標マスタ削除', 'resource' => 'master', 'action' => 'delete', 'sub_resource' => 'metrics'],
            
            ['name' => 'master.business_units.view', 'display_name' => '事業マスタ閲覧', 'resource' => 'master', 'action' => 'view', 'sub_resource' => 'business_units'],
            ['name' => 'master.business_units.create', 'display_name' => '事業マスタ作成', 'resource' => 'master', 'action' => 'create', 'sub_resource' => 'business_units'],
            ['name' => 'master.business_units.update', 'display_name' => '事業マスタ更新', 'resource' => 'master', 'action' => 'update', 'sub_resource' => 'business_units'],
            ['name' => 'master.business_units.delete', 'display_name' => '事業マスタ削除', 'resource' => 'master', 'action' => 'delete', 'sub_resource' => 'business_units'],
            
            // 事業別実績管理
            ['name' => 'business_units.view', 'display_name' => '事業別実績閲覧', 'resource' => 'business_units', 'action' => 'view'],
            ['name' => 'business_units.create', 'display_name' => '事業別実績入力', 'resource' => 'business_units', 'action' => 'create'],
            ['name' => 'business_units.update', 'display_name' => '事業別実績更新', 'resource' => 'business_units', 'action' => 'update'],
            ['name' => 'business_units.export', 'display_name' => '事業別実績エクスポート', 'resource' => 'business_units', 'action' => 'export'],
            
            // データエクスポート
            ['name' => 'export.all', 'display_name' => '全データエクスポート', 'resource' => 'export', 'action' => 'export'],
            
            // ユーザー管理
            ['name' => 'users.view', 'display_name' => 'ユーザー閲覧', 'resource' => 'users', 'action' => 'view'],
            ['name' => 'users.create', 'display_name' => 'ユーザー作成', 'resource' => 'users', 'action' => 'create'],
            ['name' => 'users.update', 'display_name' => 'ユーザー更新', 'resource' => 'users', 'action' => 'update'],
            ['name' => 'users.delete', 'display_name' => 'ユーザー削除', 'resource' => 'users', 'action' => 'delete'],
            ['name' => 'users.manage_roles', 'display_name' => 'ユーザー役割管理', 'resource' => 'users', 'action' => 'manage_roles'],
            
            // 顧客管理
            ['name' => 'customers.view', 'display_name' => '顧客閲覧', 'resource' => 'customers', 'action' => 'view'],
            ['name' => 'customers.create', 'display_name' => '顧客作成', 'resource' => 'customers', 'action' => 'create'],
            ['name' => 'customers.update', 'display_name' => '顧客更新', 'resource' => 'customers', 'action' => 'update'],
            ['name' => 'customers.delete', 'display_name' => '顧客削除', 'resource' => 'customers', 'action' => 'delete'],
            ['name' => 'customers.import', 'display_name' => '顧客インポート', 'resource' => 'customers', 'action' => 'import'],
            
            // 契約管理
            ['name' => 'contracts.view', 'display_name' => '契約閲覧', 'resource' => 'contracts', 'action' => 'view'],
            ['name' => 'contracts.create', 'display_name' => '契約作成', 'resource' => 'contracts', 'action' => 'create'],
            ['name' => 'contracts.update', 'display_name' => '契約更新', 'resource' => 'contracts', 'action' => 'update'],
            ['name' => 'contracts.delete', 'display_name' => '契約削除', 'resource' => 'contracts', 'action' => 'delete'],
            ['name' => 'contracts.import', 'display_name' => '契約インポート', 'resource' => 'contracts', 'action' => 'import'],
            ['name' => 'contracts.upload_document', 'display_name' => '契約書類アップロード', 'resource' => 'contracts', 'action' => 'upload_document'],
            
            // サービス種類管理
            ['name' => 'service_types.view', 'display_name' => 'サービス種類閲覧', 'resource' => 'service_types', 'action' => 'view'],
            ['name' => 'service_types.create', 'display_name' => 'サービス種類作成', 'resource' => 'service_types', 'action' => 'create'],
            ['name' => 'service_types.update', 'display_name' => 'サービス種類更新', 'resource' => 'service_types', 'action' => 'update'],

            // 請求管理
            ['name' => 'billings.view', 'display_name' => '請求閲覧', 'resource' => 'billings', 'action' => 'view'],
            ['name' => 'billings.create', 'display_name' => '請求作成', 'resource' => 'billings', 'action' => 'create'],
            ['name' => 'billings.update', 'display_name' => '請求更新', 'resource' => 'billings', 'action' => 'update'],
            ['name' => 'billings.delete', 'display_name' => '請求削除', 'resource' => 'billings', 'action' => 'delete'],
            ['name' => 'service_types.delete', 'display_name' => 'サービス種類削除', 'resource' => 'service_types', 'action' => 'delete'],
            
            // 売掛金管理
            ['name' => 'receivables.view', 'display_name' => '売掛金閲覧', 'resource' => 'receivables', 'action' => 'view'],
            ['name' => 'receivables.create', 'display_name' => '売掛金作成', 'resource' => 'receivables', 'action' => 'create'],
            ['name' => 'receivables.update', 'display_name' => '売掛金更新', 'resource' => 'receivables', 'action' => 'update'],
            ['name' => 'receivables.delete', 'display_name' => '売掛金削除', 'resource' => 'receivables', 'action' => 'delete'],
        ];

        // 権限を作成
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'display_name' => $perm['display_name'],
                    'resource' => $perm['resource'],
                    'action' => $perm['action'],
                ]
            );
        }

        // 役割の定義
        $roles = [
            [
                'name' => 'admin',
                'display_name' => '管理者',
                'description' => '全ての権限を持つ管理者',
                'is_system' => true,
                'permissions' => array_column($permissions, 'name'), // 全ての権限
            ],
            [
                'name' => 'manager',
                'display_name' => '部長・MGR',
                'description' => 'ダッシュボード・月次実績は閲覧のみ、事業別実績・顧客管理は個別設定可能',
                'is_system' => true,
                'permissions' => [
                    'dashboard.view',
                    'monthly_results.view',
                    'business_units.view',
                    'business_units.create',
                    'business_units.update',
                    'business_units.export',
                    'customers.view',
                    'customers.create',
                    'customers.update',
                    'customers.delete',
                    'customers.import',
                    'contracts.view',
                    'contracts.create',
                    'contracts.update',
                    'contracts.delete',
                    'contracts.import',
                    'contracts.upload_document',
                    'service_types.view',
                    'billings.view',
                    'billings.create',
                    'billings.update',
                    'billings.delete',
                    'receivables.view',
                    'receivables.create',
                    'receivables.update',
                    'receivables.delete',
                ],
            ],
            [
                'name' => 'user',
                'display_name' => 'ユーザー',
                'description' => '事業別実績・顧客管理は個別設定、ダッシュボード・月次実績・マスタ管理は不可',
                'is_system' => true,
                'permissions' => [
                    'business_units.view',
                    'business_units.create',
                    'business_units.update',
                    'business_units.export',
                    'customers.view',
                    'customers.create',
                    'customers.update',
                    'customers.delete',
                    'customers.import',
                    'contracts.view',
                    'contracts.create',
                    'contracts.update',
                    'contracts.delete',
                    'contracts.import',
                    'contracts.upload_document',
                    'service_types.view',
                    'billings.view',
                    'billings.create',
                    'billings.update',
                    'billings.delete',
                    'receivables.view',
                    'receivables.create',
                    'receivables.update',
                    'receivables.delete',
                ],
            ],
        ];

        // 役割を作成または更新
        foreach ($roles as $roleData) {
            $permissionNames = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // 権限を紐付け
            $permissions = Permission::whereIn('name', $permissionNames)->get();
            $role->permissions()->sync($permissions->pluck('id'));
        }

        // salesロールのユーザーをuserロールに移行してから削除
        $salesRole = Role::where('name', 'sales')->first();
        $userRole = Role::where('name', 'user')->first();
        
        if ($salesRole && $userRole) {
            // salesロールを持つユーザーを取得
            $usersWithSalesRole = \DB::table('user_roles')
                ->where('role_id', $salesRole->id)
                ->pluck('user_id');
            
            if ($usersWithSalesRole->isNotEmpty()) {
                // userロールに変更
                \DB::table('user_roles')
                    ->whereIn('user_id', $usersWithSalesRole)
                    ->where('role_id', $salesRole->id)
                    ->update(['role_id' => $userRole->id]);
                
                $this->command->info("salesロールの {$usersWithSalesRole->count()} ユーザーをuserロールに移行しました。");
            }
            
            // salesロールを削除
            $salesRole->permissions()->detach();
            $salesRole->delete();
            $this->command->info('salesロールを削除しました。');
        }

        $this->command->info('役割と権限のシードが完了しました。');
    }
}
