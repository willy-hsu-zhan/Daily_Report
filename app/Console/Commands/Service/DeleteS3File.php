<?php

namespace App\Console\Commands\Service;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\TaskReport;
use App\Models\TaskReportImage;
use Exception;
use Illuminate\Support\Facades\Log;

class DeleteS3File extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:delete-s3-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if( !(config('app.env') && config('uploadfile.path.production') && config('uploadfile.path.dev')) )
        {
            $this->info('please config:clear config:cache first !'); // 防止沒cache值接刪錯誤路徑的檔案 DB髒掉
            return;
        }

        $base_folder_path = config('app.env') == 'production' ? config('uploadfile.path.production') : config('uploadfile.path.dev');

        // 正常刪除的狀況下

        $deletedTaskReports = TaskReport::onlyTrashed()->get();

        foreach( $deletedTaskReports as $deletedTaskReport )
        {
            $log_folder_path = $deletedTaskReport->user_id . '/' . $deletedTaskReport->id . '/';

            $s3_folder_path = $base_folder_path . $log_folder_path;

            $this->deleteFilePathFromS3($s3_folder_path);

            $taskReportId = $deletedTaskReport->id;

            $taskReportImages = TaskReportImage::where('task_report_id', $taskReportId)->whereNotNull('s3_image_path')->get();

            if( !empty($taskReportImages) )
            {
                foreach( $taskReportImages as $taskReportImage )
                {
                    $this->info('delete taskReportImageId: ' . $taskReportImage->id . ' success!');

                    $taskReportImage->s3_image_path = null; // 資料表同步 將刪除的資料path欄位為空

                    $taskReportImage->save();

                    $taskReportImage->delete();
                }
            }
        }

        // 手動刪除TaskReport資料表的狀況下

        $taskReportIds = TaskReport::all()->pluck('id')->toArray();

        $taskReportImages = TaskReportImage::whereNotIn('task_report_id', $taskReportIds)->whereNotNull('s3_image_path')->get(); // taskReportImages的taskReportId不存在於TaskReport表中

        if( !empty($taskReportImages) )
        {
            foreach( $taskReportImages as $taskReportImage )
            {
                $userId = $taskReportImage->taskReport->user->id;

                $log_folder_path = $userId . '/' . $taskReportImage->id . '/';

                $s3_folder_path = $base_folder_path . $log_folder_path;

                $this->deleteFilePathFromS3($s3_folder_path);

                $taskReportImage->s3_image_path = null; // 資料表同步 將刪除的資料path欄位為空

                $taskReportImage->save();

                $this->info('not found taskReportId: ' . $taskReportImage->task_report_id . ' delete taskReportImageId: ' . $taskReportImage->id);

                $taskReportImage->delete();
            }
        }

        // 只刪除圖片不刪除log狀況下

        $deletedTaskReportImages = TaskReportImage::onlyTrashed()->whereNotNull('s3_image_path')->get();

        foreach( $deletedTaskReportImages as $deletedTaskReportImage )
        {
            $userId = $deletedTaskReportImage->taskReport->user->id;

            $log_folder_path = $userId . '/' . $deletedTaskReportImage->id . '/';

            $s3_folder_path = $base_folder_path . $log_folder_path;

            $this->deleteFilePathFromS3($s3_folder_path);

            $deletedTaskReportImage->s3_image_path = null;

            $deletedTaskReportImage->save();

            $this->info('delete taskReportImageId: ' . $deletedTaskReportImage->id);

            $deletedTaskReportImage->delete();
        }
    }

    public function deleteFilePathFromS3($s3_folder_path)
    {
        $disk = Storage::disk('s3');

        if( $disk->exists($s3_folder_path) )
        {
            try
            {
                $disk->deleteDirectory($s3_folder_path);

                $this->info('delete file s3_folder_path: ' . $s3_folder_path . ' success!');
            }
            catch (Exception $e)
            {
                $this->error('delete s3_folder_path: ' . $s3_folder_path . ' failed! ' . $e->getMessage());

                Log::info('delete file s3_folder_path: ' . $s3_folder_path . ' failed! ' . $e->getMessage());
            }
        }
    }
}
