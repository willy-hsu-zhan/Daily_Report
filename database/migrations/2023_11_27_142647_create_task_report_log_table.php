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
        Schema::create('task_report_log', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('project_category_id')->nullable();
            $table->float('use_time')->nullable();
            $table->string('description')->nullable();
            $table->enum('progress', ['進行中', '已完成', 'Delay'])->default('進行中');
            $table->string('type')->nullable();
            $table->integer('report_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_report_log');
    }
};
