<?php

namespace Database\Seeders;

use App\Models\FiscalYear;
use Illuminate\Database\Seeder;

class FiscalYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存の年度を確認
        $existing = FiscalYear::count();
        
        if ($existing > 0) {
            $this->command->info('年度データは既に存在します。');
            return;
        }

        // 16期: 2024年7月1日〜2025年6月30日
        $fiscalYear16 = FiscalYear::create([
            'name' => '第16期',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => false,
        ]);

        // 17期: 2025年7月1日〜2026年6月30日（アクティブ）
        // 既存のアクティブ年度がある場合は変更しない
        $existingActive = FiscalYear::where('is_active', true)->first();
        $fiscalYear17 = FiscalYear::firstOrCreate(
            ['name' => '第17期'],
            [
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'is_active' => !$existingActive, // 既存のアクティブ年度がない場合のみtrue
            ]
        );
        
        // 既存のアクティブ年度がない場合のみ、第17期をアクティブにする
        if (!$existingActive && !$fiscalYear17->is_active) {
            $fiscalYear17->update(['is_active' => true]);
        }

        // 18期: 2026年7月1日〜2027年6月30日
        $fiscalYear18 = FiscalYear::create([
            'name' => '第18期',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);

        $this->command->info('年度データを作成しました:');
        $this->command->info("  - {$fiscalYear16->name} ({$fiscalYear16->start_date->format('Y-m-d')} ~ {$fiscalYear16->end_date->format('Y-m-d')})");
        $this->command->info("  - {$fiscalYear17->name} ({$fiscalYear17->start_date->format('Y-m-d')} ~ {$fiscalYear17->end_date->format('Y-m-d')}) [アクティブ]");
        $this->command->info("  - {$fiscalYear18->name} ({$fiscalYear18->start_date->format('Y-m-d')} ~ {$fiscalYear18->end_date->format('Y-m-d')})");
    }
}
