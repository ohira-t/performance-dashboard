<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLiteの場合はテーブルを再作成する必要がある
            // RefreshDatabaseを使用するテストでは初期マイグレーションで対応
            return;
        }
        
        // MySQL/MariaDBの場合
        // 既存のユニークインデックスを削除
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
        });

        // google_idをnullableに変更
        DB::statement('ALTER TABLE `users` MODIFY `google_id` VARCHAR(255) NULL');

        // ユニークインデックスを再作成（null値は複数許可される）
        Schema::table('users', function (Blueprint $table) {
            $table->unique('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            return;
        }
        
        // MySQL/MariaDBの場合
        // 既存のユニークインデックスを削除
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
        });

        // null値を処理（既存のnull値がある場合はエラーになる可能性があります）
        // 注意: 既存のnull値がある場合は、事前に処理が必要です
        DB::statement('ALTER TABLE `users` MODIFY `google_id` VARCHAR(255) NOT NULL');

        // ユニークインデックスを再作成
        Schema::table('users', function (Blueprint $table) {
            $table->unique('google_id');
        });
    }
};
