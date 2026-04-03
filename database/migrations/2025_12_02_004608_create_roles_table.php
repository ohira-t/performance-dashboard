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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('役割名');
            $table->string('display_name')->comment('表示名');
            $table->text('description')->nullable()->comment('説明');
            $table->boolean('is_system')->default(false)->comment('システム役割フラグ（削除不可）');
            $table->timestamps();
            
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
