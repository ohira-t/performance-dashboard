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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->comment('親カテゴリID（階層構造）');
            $table->string('name', 100)->comment('カテゴリ名');
            $table->integer('sort_order')->default(0)->comment('表示順序');
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index('parent_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};























