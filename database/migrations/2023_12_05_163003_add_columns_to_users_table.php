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
        Schema::table('users', function (Blueprint $table) {
            $table->after('remember_token', function (Blueprint $table) {
                $table->string('google_account', 30)->nullable();
                
            });
            // $table->enum('department', ['guest', 'td', 'ad', 'qa', 'csd', 'art', 'pd', 'pd&qa'])
            //         ->default('guest')
            //         ->after('google_account')
            //         ->comment('td = 工程 ad = 行政 qa = 品管 csd = 行銷 art = 美術 pd = 企劃');
            //     $table->integer('admin')->default(0)->after('department');
            // $table->string('google_account', 30)->after('remember_token')->nullable();
            // $table->enum('department', ['guest', 'td', 'ad', 'qa', 'csd', 'art', 'pd', 'pd&qa'])
            //     ->default('guest')
            //     ->after('google_account')
            //     ->comment('td = 工程 ad = 行政 qa = 品管 csd = 行銷 art = 美術 pd = 企劃');
            // $table->integer('admin')->default(0)->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_account', 'department', 'admin']);
        });
    }
};
