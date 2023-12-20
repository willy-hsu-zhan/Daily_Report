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
        Schema::create('task_report_image', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_report_id');
            $table->string('s3_image_path');
            $table->timestamps();

            // 定義外鍵關係
            $table->foreign('task_report_id')->references('id')->on('task_report')->onDelete('cascade');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_report_image');
    }
};
