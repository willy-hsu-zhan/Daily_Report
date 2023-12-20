<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TaskReport;
use App\Models\ProjectCategory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\On;

class DepartmentReport extends Component
{
    public $department; // td , asd , art
    public $project_category_id;
    public $project; // project Name
    public $type; // log type all * yesterday lastWeek lastMonth
    public $perPage = 10;
    public $currentPage = 1;
    public $logs = []; // taskReport ORM
    protected $listeners = ['updatedPieChart' => '$refresh']; //接收到updatedPieChart觸發事件值以後 更新$this->type $this->project 並強制重新渲染


    #[On('updatedPieChart')]
    public function piechart($projectName = null, $type = null) //更新圖表用listener
    {
        $this->project = $projectName;

        $this->type = $type;

        $this->project_category_id = ProjectCategory::where('category', $this->project)->value('id');

        $this->loadData();

        $this->resetParam();
    }

    public function resetParam()
    {
        $this->currentPage = 1; // reset currentPage

        $this->perPage = 10; // reset currentPage

        $this->logs = [];
    }

    public function loadData()
    {
        $logs = [];

        $startOfWeek = Carbon::now()->startOfWeek()->subWeek()->timestamp; //上星期【
        $endOfWeek   = Carbon::now()->endOfWeek()->subWeek()->timestamp; //上星期

        $lastMonth    = Carbon::now()->subMonth(); //上個月
        $startOfMonth = $lastMonth->startOfMonth()->timestamp; // 上個月的第一天的timestamp
        $endOfMonth   = $lastMonth->endOfMonth()->timestamp; // 上個月的最後一天的timestamp

        $department = $this->department;

        $offset = ($this->currentPage - 1) * $this->perPage; // 分頁功能
        switch($this->type)
        {
            case 'lastMonth':
                $logs = TaskReport::whereHas('user', function ($query) use ($department) {
                    $query->where('department', $department);
                })
                    ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                    ->orderBy('report_date', 'desc')
                    ->skip($offset)
                    ->take($this->perPage)
                    ->get();
                break;
            case 'lastWeek':
                $logs = TaskReport::whereHas('user', function ($query) use ($department) {
                    $query->where('department', $department);
                })
                    ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                    ->orderBy('report_date', 'desc')
                    ->skip($offset)
                    ->take($this->perPage)
                    ->get();
                break;
        }

        return $logs;
    }

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
        $startOfWeek = Carbon::now()->startOfWeek()->subWeek()->timestamp; //上星期【
        $endOfWeek   = Carbon::now()->endOfWeek()->subWeek()->timestamp; //上星期

        $lastMonth    = Carbon::now()->subMonth(); //上個月
        $startOfMonth = $lastMonth->startOfMonth()->timestamp; // 上個月的第一天的timestamp
        $endOfMonth   = $lastMonth->endOfMonth()->timestamp; // 上個月的最後一天的timestamp

        $department = $this->department;

        $offset = ($this->currentPage - 1) * $this->perPage; // 分頁功能
        switch($this->type)
        {
            case 'lastMonth':
                $logs = TaskReport::whereHas('user', function ($query) use ($department) {
                    $query->where('department', $department);
                })
                    ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                    ->orderBy('report_date', 'desc')
                    ->get();
                break;
            case 'lastWeek':
                $logs = TaskReport::whereHas('user', function ($query) use ($department) {
                    $query->where('department', $department);
                })
                    ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                    ->orderBy('report_date', 'desc')
                    ->get();
                break;
        }

        $totalCount = count($logs);

        return ceil($totalCount / $this->perPage);
    }
    public function rendered() // 部門只有取上星期還有上個月的資料
    { // 之後移植到construct取model

        $data = [];

        $data['logs'] = [];

        $startOfWeek = Carbon::now()->startOfWeek()->subWeek()->timestamp; //上星期【
        $endOfWeek   = Carbon::now()->endOfWeek()->subWeek()->timestamp; //上星期

        $lastMonth    = Carbon::now()->subMonth(); //上個月
        $startOfMonth = $lastMonth->startOfMonth()->timestamp; // 上個月的第一天的timestamp
        $endOfMonth   = $lastMonth->endOfMonth()->timestamp; // 上個月的最後一天的timestamp

        $department = $this->department;

        $lastMonth_logs = TaskReport::whereHas('user', function ($query) use ($department) // 專案分類沒有分部門，ex 美術部門選擇前端也會show出來 可再創department -> category 
        {
            $query->where('department', $department);
        })
            ->with([
                'projectCategory',
                'projectCategory.projectSubCategory',
                'images' => function ($query) {
                    $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                },
            ])
            ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy('projectCategory.category')
            ->map(function ($logs) {
                return $logs->sum('use_time');
            })
            ->toArray();

        $data['logs']['lastMonth'] = $lastMonth_logs;

        $lastWeek_logs = TaskReport::whereHas('user', function ($query) use ($department) // 專案分類沒有分部門，ex 美術部門選擇前端也會show出來 可再創department -> category 
        {
            $query->where('department', $department);
        })
            ->with([
                'projectCategory',
                'projectCategory.projectSubCategory',
                'images' => function ($query) {
                    $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                },
            ])
            ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy('projectCategory.category')
            ->map(function ($logs) {
                return $logs->sum('use_time');
            })
            ->toArray();

        $data['logs']['lastWeek'] = $lastWeek_logs;

        $data['link'] = 'department-report'; // dispatch 補上link使content找到對應組件

        $this->dispatch('googleDraw', data: $data);
    }

    public function render()
    {
        $this->logs = $this->loadData();
        return view('livewire.pages.department-report');
    }
}
