<?php

namespace App\Console\Commands\Email;

use Illuminate\Console\Command;
use Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\TaskReport;
use App\Models\User;
use Carbon\Carbon;

class SendForgetMail extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-forget-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send email for people who forget to key in daily-report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday               = Carbon::yesterday(); //昨天

        $yesterdayStartTimestamp = $yesterday->startOfDay()->timestamp;

        $yesterdayEndTimestamp   = $yesterday->endOfDay()->timestamp;

        $forgetUserIds = TaskReport::whereBetween('report_date', [$yesterdayStartTimestamp, $yesterdayEndTimestamp])->pluck('user_id');

        $users = User::where('department', '!=', 'guest')
            ->whereNotIn('id', $forgetUserIds)
            ->select('id', 'email', 'name')
            ->get();

        $mailInfo = $yesterday->toDateString() . ' 沒有report的紀錄 麻煩請補登';

        $mail = ['mail_info' => $mailInfo];

        $excludedEmail = config('excludedemail');

        foreach( $users as $user )
        {
            if( in_array($user->mail, $excludedEmail) )
            {
                continue;
            }

            try
            {
                Mail::send('mailtemplate', $mail, function ($mail) use ($user, $yesterday) {
                    $mail->to($user->email);
                    $mail->subject($yesterday->toDateString() . ' 沒有report紀錄');
                });

                $this->info('send to ' . $user->name . ' forget mail successed');
            }
            catch (Exception $e)
            {
                Log::info('Sending Forget Mail to user_id : ' . $user->id . ' failed! error: ' . $e->getMessage());
            }
        }
    }
}
