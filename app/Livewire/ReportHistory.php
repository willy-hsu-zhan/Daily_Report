<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TaskReport;
use App\Models\TaskReportImage;
use App\Models\TaskReportLog;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Exception;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class ReportHistory extends Component {
    use WithPagination;

    public $perPage = 10;
    public $currentPage = 1;
    public $log_datas;
    public $logId; // taskReportId
    public $taskReportImageId;

    public function nextPage()
    {
        if( $this->currentPage < $this->maxPage() )
        {
            $this->currentPage++;
        }
    }

    public function previousPage()
    {
        if( $this->currentPage > 1 )
        {
            $this->currentPage--;
        }
    }

    public function maxPage()
    {
        $totalCount = TaskReport::where('user_id', Auth::id())->count();
        return ceil($totalCount / $this->perPage);
    }

    public function fetchData()
    {
        $offset = ($this->currentPage - 1) * $this->perPage;

        $logs = TaskReport::where('user_id', Auth::id())
            ->with([
                'projectCategory',
                'projectCategory.projectSubCategory',
                'images' => function ($query) {
                    $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                },
            ])
            ->orderBy('report_date', 'desc')
            ->skip($offset)
            ->take($this->perPage)
            ->get();
        return $logs;
    }

    public function removeImageAction($taskReportImageId)
    {
        $this->taskReportImageId = $taskReportImageId;

        $this->showConfirmDeleteImageAlert();
    }

    public function showConfirmDeleteImageAlert()
    {
        $taskReportImage = TaskReportImage::find($this->taskReportImageId);
        $imageName       = basename($taskReportImage?->s3_image_path);

        $tag = 'sweetalert.confirm_whether_to_delete_image';

        $dismiss_tag = 'sweetalert.not_delete_image';

        $data = [
            'title'   => config($tag . '.' . 'title.head') . $imageName . config($tag . '.' . 'title.tail'),
            'text'    => config($tag . '.' . 'text'),
            'icon'    => config($tag . '.' . 'icon'),
            'dismiss' => [
                'title' => config($dismiss_tag . '.' . 'title'),
                'text'  => config($dismiss_tag . '.' . 'text'),
                'icon'  => config($dismiss_tag . '.' . 'icon'),
            ],
            'tag'     => Str::afterLast($tag, '.')
        ];

        $this->pushSweetAlert($data);
    }

    public function showConfirmDeleteDailyAlert()
    {
        $tag = 'sweetalert.confirm_whether_to_delete_daily';

        $dismiss_tag = 'sweetalert.not_delete_daily';

        $data = [
            'title'   => config($tag . '.' . 'title'),
            'text'    => config($tag . '.' . 'text'),
            'icon'    => config($tag . '.' . 'icon'),
            'dismiss' => [
                'title' => config($dismiss_tag . '.' . 'title'),
                'text'  => config($dismiss_tag . '.' . 'text'),
                'icon'  => config($dismiss_tag . '.' . 'icon'),
            ],
            'tag'     => Str::afterLast($tag, '.')
        ];

        $this->pushSweetAlert($data);
    }

    public function pushSweetAlert($data)
    {
        $this->dispatch('ShowSweetAlert', data: $data);
    }

    #[On('updateImageReportAlertToastCallback')]
    public function removeImage()
    {
        $taskReportImage = TaskReportImage::find($this->taskReportImageId);
        $taskReportImage->delete();

        $this->showDeleteImageSuccessAlert();
    }

    #[On('SweetAlertCallback')] // 成功才會回傳
    public function sweetAlertCallback($tag)
    {
        switch($tag)
        {
            case 'confirm_whether_to_delete_image':
                $this->removeImage();
                break;
            case 'confirm_whether_to_delete_daily':
                $this->deleteReport();
                break;
        }
    }

    public function updateCategoryLink($data) //換連結改動
    {
        $this->dispatch('updateComponent', link: 'daily-form', data: $data);
    }

    public function deleteForm($logId)
    {
        $this->logId = $logId;

        $this->showConfirmDeleteDailyAlert();
    }


    public function deleteReport()
    {
        try
        {
            $taskReport = TaskReport::find($this->logId);

            TaskReportLog::create([
                'user_id'             => $taskReport->user_id,
                'project_category_id' => $taskReport->project_category_id,
                'use_time'            => $taskReport->use_time,
                'description'         => $taskReport->description,
                'progress'            => $taskReport->progress,
                'type'                => $taskReport->type,
                'report_date'         => $taskReport->report_date
            ]);

            $taskReport->delete();

            $taskReportImages = TaskReportImage::where('task_report_id', $this->logId)->get();

            if( !empty($taskReportImage) )
            {
                foreach( $taskReportImages as $taskReportImage )
                {
                    $taskReportImage->delete(); // 實際上不刪除S3路徑的檔案供物刪查找用
                }
            }

            $this->showDeleteDailySuccessAlert();
            //session()->flash('updateReportMsgSuccess', '日報刪除成功'); 走sweetAlert2
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage());
            session()->flash('updateReportMsgFail', '日報刪除失敗');
        }

        $this->logId = null; //reset logId
    }

    public function showDeleteDailySuccessAlert()
    {
        $tag = 'sweetalert.delete_daily_success';

        $data = [
            'title'   => config($tag . '.' . 'title'),
            'text'    => config($tag . '.' . 'text'),
            'icon'    => config($tag . '.' . 'icon'),
            'tag'     => Str::afterLast($tag, '.')
        ];

        $this->pushSweetAlert($data);
    }

    public function showDeleteImageSuccessAlert()
    {
        $tag = 'sweetalert.update_daily_success';

        $data = [
            'title'   => config($tag . '.' . 'title'),
            'text'    => config($tag . '.' . 'text'),
            'icon'    => config($tag . '.' . 'icon'),
            'tag'     => Str::afterLast($tag, '.')
        ];

        $this->pushSweetAlert($data);
    }

    public function render()
    {
        $this->log_datas = $this->fetchData();

        return view('livewire.pages.report-history');
    }
}
