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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('権限名（例: dashboard.view）');
            $table->string('display_name')->comment('表示名');
            $table->string('resource')->comment('リソース（dashboard/monthly_results/master/business_units）');
            $table->string('action')->comment('アクション（view/create/update/delete/export）');
            $table->text('description')->nullable()->comment('説明');
            $table->timestamps();
            
            $table->index(['resource', 'action']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
