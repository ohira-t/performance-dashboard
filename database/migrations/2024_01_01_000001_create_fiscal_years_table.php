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
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('年度名（例：第17期）');
            $table->date('start_date')->comment('年度開始日');
            $table->date('end_date')->comment('年度終了日');
            $table->boolean('is_active')->default(false)->comment('アクティブ年度フラグ');
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_years');
    }
};























