<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('service:clean-tmp-file')
            ->daily() // 每天清理暫存檔案
            ->timezone('Asia/Taipei')
            ->at('03:10')
            ->environments(['production']);

        $schedule->command('email:send-forget-mail')
            ->days([2, 3, 4, 5, 6]) // 星期二 ~ 星期六 email會抓取昨天未登日報的記錄
            ->timezone('Asia/Taipei')
            ->at('02:10')
            ->environments(['production']);
            
        $schedule->command('email:send-report-project-mail')
            ->days([2, 3, 4, 5, 6]) // 星期二 ~ 星期六 email會抓取昨天未登日報的記錄
            ->timezone('Asia/Taipei')
            ->at('01:10')
            ->environments(['production']);

        $schedule->command('email:send-report-user-mail')
            ->days([2, 3, 4, 5, 6]) // 星期二 ~ 星期六 email會抓取昨天未登日報的記錄
            ->timezone('Asia/Taipei')
            ->at('00:10')
            ->environments(['production']);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands/Service');
        $this->load(__DIR__.'/Commands/Restore');
        $this->load(__DIR__.'/Commands/Email');
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
