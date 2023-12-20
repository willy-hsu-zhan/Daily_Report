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
        Schema::table('task_report', function (Blueprint $table) {
            $table->renameColumn('project_id', 'project_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_report', function (Blueprint $table) {
            $table->renameColumn('project_category_id', 'project_id');
        });
    }
};
