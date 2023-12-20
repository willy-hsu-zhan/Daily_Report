<?php

namespace App\Console\Commands\Email;

use Illuminate\Console\Command;
use DB;
use Mail;
use App\Models\User;
use App\Models\MailRelationship;
use App\Models\TaskReportImage;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class SendReportUserMail extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-report-user-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send mail to user who subscript users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday(); //昨天

        $yesterdayStartTimestamp = $yesterday->startOfDay()->timestamp;

        $yesterdayEndTimestamp = $yesterday->endOfDay()->timestamp;

        $users = User::where('department', '!=', 'guest')->get();

        // Query = 1 + n * ( 1 + 1 + 1 + 1 )

        foreach( $users as $user )
        {
            $mailRelationShip = MailRelationship::where('user_id', $user->id)->first();

            if( $mailRelationShip != null && !empty($mailRelationShip->relation_user_id) ) // 掃到的user_id 有訂閱其他user
            {
                $mail = '';

                $mail_info = '';

                // 如果有訂閱兩個項目以上再作 explode ',' 不然會報錯，1個也統一成陣列單一元素
                $relationUserIds = str_contains($mailRelationShip->relation_user_id, ',') ? explode(',', $mailRelationShip->relation_user_id) : [$mailRelationShip->relation_user_id];

                foreach( $relationUserIds as $userId ) // 訂閱之使用者資訊統整至同一封信
                {
                    if( empty($userId) ) // !empty($mailRelationShip->relation_user_id) 這段就會檔 不一定需要
                    {
                        continue;
                    }

                    $relationUser = User::find($userId);

                    $relationUserNickName = User::getUserNickName($relationUser);

                    $mail_info .= '<table id="present_table" border="1"cellspacing="1" margin-left="auto" margin-right="auto" align="center" style="table-layout:fixed;" width="80%">
                                        <tr>
                                            <th style = "font-size: 1em; background-color: #d0d7de; width: 20%;">' . $relationUserNickName . '</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de;width: 25%;">專案名稱</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de;width: 40%;">工作內容</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de;width: 15%;">花費時間</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de;width: 15%;">進度</th>
                                        </tr>'; // mail_info head

                    $taskReports = DB::table('task_report')
                        ->join('users', 'task_report.user_id', '=', 'users.id')
                        ->join('project_category as p', 'task_report.project_category_id', '=', 'p.id')
                        ->where('task_report.user_id', $userId)
                        ->whereBetween('task_report.report_date', [$yesterdayStartTimestamp, $yesterdayEndTimestamp])
                        ->whereNull('task_report.deleted_at')
                        ->select('users.name', 'p.category', 'task_report.id', 'task_report.description', 'task_report.progress', 'task_report.report_date', 'task_report.type', 'task_report.use_time')
                        ->orderBy('task_report.type')
                        ->get();

                    if( $taskReports->isEmpty() )
                    {
                        $mail_info .= '</table><br>';
                        continue; // 該用戶訂閱的專案當天都沒人撰寫 直接略過找下個專案
                    }

                    foreach( $taskReports as $taskReport )
                    {
                        $taskReportImages = TaskReportImage::where('task_report_id', $taskReport->id)
                            ->whereNull('deleted_at')
                            ->get();

                        $mail_info .= '<tr>
                                            <th></th>'; // sub head

                        $mail_info .= '<th style = "font-size: 0.8em;">' . $taskReport->category . '</th>';

                        $mail_info .= '<th style = "font-size: 0.8em; word-break: break-all;">'; // 對應工作內容欄位

                        $mail_info .= nl2br(strip_tags($taskReport->description));

                        if( !$taskReportImages->isEmpty() )
                        {
                            foreach( $taskReportImages as $image )
                            {
                                $mail_info .= '<br>';
                                $mail_info .= '<a href="' . $image->s3_image_path . '" target="_blank">';
                                $mail_info .= '<img src="' . $image->s3_image_path . '" alt="Uploaded Image" style="max-width: 200px; max-height: 200px;">';
                                $mail_info .= '</a>';
                            }
                        }

                        $mail_info .= '</th>';

                        $mail_info .= '<th style = "font-size: 0.8em;">' . $taskReport->use_time . 'hr</th>';

                        //如果進度delay的話把字標記成紅字
                        if( $taskReport->progress == 'Delay' )
                        {
                            $mail_info .= '<th style = "font-size: 0.8em; color: red">' . $taskReport->progress . '</th>
                                        </tr>';
                        }
                        else
                        {
                            $mail_info .= '<th style = "font-size: 0.8em;">' . $taskReport->progress . '</th>
                                        </tr>';
                        }
                    }

                    $mail_info .= '</table><br>'; // mail_info tail
                }

                $mail = ['mail_info' => $mail_info];

                try
                {
                    Mail::send('mailtemplate', $mail, function ($mail) use ($yesterday, $user) { // 自己訂閱的用戶信寄給自己
                        $mail->to($user->email);
                        $mail->subject($yesterday->toDateString());
                    });

                    $this->info('send to ' . $user->name . ' relation user_id：' . $userId . ' successed');
                }
                catch (Exception $e)
                {
                    Log::info('Sending Report-User Mail to user_id : ' . $user->id . ' failed! error: ' . $e->getMessage());
                }

                sleep(1); // mail send 停止1秒再寄送防止跳出多421錯誤
            }
        }
    }
}
