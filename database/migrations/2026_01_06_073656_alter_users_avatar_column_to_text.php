<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * avatarカラムをVARCHAR(255)からTEXTに変更
     * GoogleアバターURLが255文字を超えることがあるため
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('avatar')->nullable()->comment('アバター画像URL')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->comment('アバター画像URL')->change();
        });
    }
};
