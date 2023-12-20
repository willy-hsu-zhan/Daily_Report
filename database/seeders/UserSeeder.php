<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class SeederClassName extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'testName',
            'email' => 'steven@example.com.tw',
            'password' => bcrypt('your_password_here'), // 設定你的密碼
            'google_account' => '101736188672743264185',
            'department' => 'art',
            'admin' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
