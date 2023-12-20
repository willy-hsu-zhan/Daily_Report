<?php

namespace App\Console\Commands\Email;

use Illuminate\Console\Command;
use DB;
use Mail;
use App\Models\User;
use App\Models\ProjectMailRelationship;
use App\Models\ProjectCategory;
use App\Models\TaskReport;
use App\Models\TaskReportImage;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class SendReportProjectMail extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-report-project-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send mail to user who subscript projects';

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
            $projectMailRelationShip = ProjectMailRelationship::where('user_id', $user->id)->first(); // 訂閱專案ids

            if( $projectMailRelationShip != null && !empty($projectMailRelationShip->relation_project_category_id) ) // 掃到的user_id 有訂閱其他專案
            {
                $mail = '';

                $mail_info = '';
                // 如果有訂閱兩個項目以上再作 explode ',' 不然會報錯，1個也統一成陣列單一元素
                $relationProjectIds = str_contains($projectMailRelationShip->relation_project_category_id, ',') ?
                                        explode(',', $projectMailRelationShip->relation_project_category_id) : [$projectMailRelationShip->relation_project_category_id];

                foreach( $relationProjectIds as $projectId ) // 個別專案個別寄一封信
                {
                    if( empty($projectId) )
                    {
                        continue;
                    }

                    $mail_info = '';

                    $mail_info .= '<table id="present_table" border="1"cellspacing="1" margin-left="auto" margin-right="auto" align="center" style="table-layout:fixed;" width="80%">
                                        <tr>
                                            <th style = "font-size: 1em; background-color: #d0d7de; width: 20%;">專案名稱</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de; width: 20%;">USER</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de;width: 25%;">工作類別</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de;width: 40%;">工作內容</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de;width: 15%;">花費時間</th>
                                            <th style = "font-size: 1em; background-color: #d0d7de;width: 15%;">進度</th>
                                        </tr>'; // mail_info head

                    $taskReports = DB::table('task_report')
                        ->join('users', 'task_report.user_id', '=', 'users.id')
                        ->join('project_category as p', 'task_report.project_category_id', '=', 'p.id')
                        ->where('task_report.project_category_id', $projectId)
                        ->whereBetween('task_report.report_date', [$yesterdayStartTimestamp, $yesterdayEndTimestamp])
                        ->whereNull('task_report.deleted_at')
                        ->select('users.name', 'p.category', 'task_report.id', 'task_report.description', 'task_report.progress', 'task_report.report_date', 'task_report.type', 'task_report.use_time')
                        ->orderBy('task_report.type')
                        ->get();


                    if( $taskReports->isEmpty() ) // collect 要用 isEmpty() 方法檢查null
                    {
                        //$mail_info .= '</table><br>';
                        continue; // 該用戶訂閱的專案當天都沒人撰寫 直接略過找下個專案
                    }

                    foreach( $taskReports as $taskReport )
                    {
                        $taskReportImages = TaskReportImage::where('task_report_id', $taskReport->id)
                            ->whereNull('deleted_at')
                            ->get();

                        $mail_info .= '<tr>
                                            <th style = "font-size: 0.8em;">' . $taskReport->category . '</th>'; // 對應專案名稱欄位

                        $mail_info .= '<th style = "font-size: 0.8em;">' . $taskReport->name . '</th>'; // 對應USER欄位

                        $mail_info .= '<th style = "font-size: 0.8em;">' . $taskReport->type . '</th>'; // 對應工作類別欄位

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

                        $mail_info .= '<th style = "font-size: 0.8em;">' . $taskReport->use_time . 'hr' . '</th>';


                        //如果進度delay的話把字標記成紅字
                        if( $taskReport->progress == 'Delay' )
                        {
                            $mail_info .= '<th style = "font-size: 0.8em; color: red">' . $taskReport->progress . '</th>
                                        </tr>'; // 對應進度欄位
                        }
                        else
                        {
                            $mail_info .= '<th style = "font-size: 0.8em;">' . $taskReport->progress . '</th>
                                        </tr>'; // 對應進度欄位
                        }
                    }

                    $mail_info .= '</table><br>'; // mail_info tail

                    $mail = ['mail_info' => $mail_info];

                    $projectName = ProjectCategory::where('id', $projectId)->value('category');

                    try
                    {
                        Mail::send('mailtemplate', $mail, function ($mail) use ($yesterday, $projectName, $user) {
                            $mail->to($user->email);
                            $mail->subject($projectName . ' ' . $yesterday->toDateString());
                        });

                        $this->info('send to ' . $user->name . ' relation projects : ' . $projectName . ' successed');
                    }
                    catch (Exception $e)
                    {
                        Log::info('Sending Report-Project Mail to user_id : ' . $user->id . ' failed! error: ' . $e->getMessage());
                    }

                    sleep(1); // mail send 停止1秒再寄送防止跳出多421錯誤
                }
            }
        }
    }
}
