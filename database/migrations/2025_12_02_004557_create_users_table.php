<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('google_id')->nullable()->unique()->comment('GoogleアカウントID');
            $table->string('name')->comment('ユーザー名');
            $table->string('email')->unique()->comment('メールアドレス');
            $table->string('avatar')->nullable()->comment('アバター画像URL');
            $table->boolean('is_active')->default(true)->comment('有効フラグ');
            $table->timestamp('last_login_at')->nullable()->comment('最終ログイン日時');
            $table->timestamps();
            
            $table->index('email');
            $table->index('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
