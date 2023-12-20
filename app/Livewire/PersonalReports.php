<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TaskReport;
use App\Models\ProjectCategory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\On;

class PersonalReports extends Component {
    use WithPagination;

    public $project_category_id;
    public $title; // project Name 顯示於底下的title
    public $type; // log type all * yesterday lastWeek lastMonth
    public $perPage = 10;
    public $currentPage = 1;
    public $logs = []; // taskReport ORM // render用logs (會吃到當前分頁總比數 #perPage)
    protected $listeners = ['updatedPieChart' => '$refresh']; //接收到updatedPieChart觸發事件值以後 更新$this->type $this->project 並強制重新渲染
    public $is_ignore_query_project_id = false;

    #[On('updatedPieChart')]
    public function piechart($projectName = null, $type = null) //更新圖表用listener
    {
        $this->title = $projectName; // 顯示於底下的title為回傳之專案名稱

        $this->type = $type;

        $this->project_category_id = ProjectCategory::where('category', $projectName)->value('id');

        $this->resetParam();

        $this->loadData();
    }

    public function resetParam()
    {
        $this->currentPage = 1; // reset currentPage

        $this->perPage = 10; // reset currentPage

        $this->logs = [];

        $this->is_ignore_query_project_id = false; // 預設顯示專案欄位為關閉的
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
        $yesterday      = Carbon::yesterday(); //昨天
        $yesterdayStart = $yesterday->startOfDay()->timestamp;
        $yesterdayEnd   = $yesterday->endOfDay()->timestamp;

        $startOfWeek = Carbon::now()->startOfWeek()->subWeek()->timestamp; //上星期【
        $endOfWeek   = Carbon::now()->endOfWeek()->subWeek()->timestamp; //上星期

        $lastMonth    = Carbon::now()->subMonth(); //上個月
        $startOfMonth = $lastMonth->startOfMonth()->timestamp; // 上個月的第一天的timestamp
        $endOfMonth   = $lastMonth->endOfMonth()->timestamp; // 上個月的最後一天的timestamp

        //暫時修正 之後抽func
        if( !$this->is_ignore_query_project_id ) // 暫時這樣子修 之後再抽func
        {
            switch($this->type)
            {
                case 'all':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                        ->where('project_category_id', $this->project_category_id)
                        ->orderBy('report_date', 'desc')
                        ->get();
                    break;
                case 'lastMonth':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                        ->where('project_category_id', $this->project_category_id)
                        ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                        ->orderBy('report_date', 'desc')
                        ->get();
                    break;
                case 'lastWeek':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                        ->where('project_category_id', $this->project_category_id)
                        ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                        ->orderBy('report_date', 'desc')
                        ->get();

                    break;
                case 'yesterday':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                        ->where('project_category_id', $this->project_category_id)
                        ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
                        ->orderBy('report_date', 'desc')
                        ->get();
                    break;
            }
        }
        else
        {
            switch($this->type)
            {
                case 'all':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                        ->orderBy('report_date', 'desc')
                        ->get();
                    break;
                case 'lastMonth':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                        ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                        ->orderBy('report_date', 'desc')
                        ->get();
                    break;
                case 'lastWeek':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                        ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                        ->orderBy('report_date', 'desc')
                        ->get();

                    break;
                case 'yesterday':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                        ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
                        ->orderBy('report_date', 'desc')
                        ->get();
                    break;
            }
        }

        $totalCount = count($logs);

        return ceil($totalCount / $this->perPage);
    }

    public function rendered()
    { // 之後移植到construct取model
        /**
         * yesterday 
         * lastWeek
         * lastMonth
         * all
         */
        $data = [];

        $data['logs'] = [];

        $yesterday      = Carbon::yesterday(); //昨天
        $yesterdayStart = $yesterday->startOfDay()->timestamp;
        $yesterdayEnd   = $yesterday->endOfDay()->timestamp;

        $startOfWeek = Carbon::now()->startOfWeek()->subWeek()->timestamp; //上星期【
        $endOfWeek   = Carbon::now()->endOfWeek()->subWeek()->timestamp; //上星期

        $lastMonth    = Carbon::now()->subMonth(); //上個月
        $startOfMonth = $lastMonth->startOfMonth()->timestamp; // 上個月的第一天的timestamp
        $endOfMonth   = $lastMonth->endOfMonth()->timestamp; // 上個月的最後一天的timestamp

        $all_logs = TaskReport::where('user_id', Auth::id())
            ->with(['projectCategory', 'projectCategory.projectSubCategory'])
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy('projectCategory.category')
            ->map(function ($logs) {
                return $logs->sum('use_time');
            })
            ->toArray();
        /**array:5
         "VAS" => 11
        "ALS" => 45
        "ALE" => 80
        "VAE" => 24
        "SGK" => 1
        ] */

        $data['logs']['all'] = $all_logs;

        $lastMonth_logs = TaskReport::where('user_id', Auth::id())
            ->with(['projectCategory', 'projectCategory.projectSubCategory'])
            ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy('projectCategory.category')
            ->map(function ($logs) {
                return $logs->sum('use_time');
            })
            ->toArray();

        $data['logs']['lastMonth'] = $lastMonth_logs;

        $lastWeek_logs = TaskReport::where('user_id', Auth::id())
            ->with(['projectCategory', 'projectCategory.projectSubCategory'])
            ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy('projectCategory.category')
            ->map(function ($logs) {
                return $logs->sum('use_time');
            })
            ->toArray();

        $data['logs']['lastWeek'] = $lastWeek_logs;

        $yesterday_logs = TaskReport::where('user_id', Auth::id())
            ->with(['projectCategory', 'projectCategory.projectSubCategory'])
            ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy('projectCategory.category')
            ->map(function ($logs) {
                return $logs->sum('use_time');
            })
            ->toArray();

        $data['logs']['yesterday'] = $yesterday_logs;

        $data['link'] = 'personal-reports'; // dispatch 補上link使content找到對應組件

        $this->dispatch('googleDraw', data: $data);
    }

    public function loadData()
    {
        $logs = [];

        $yesterday      = Carbon::yesterday(); //昨天
        $yesterdayStart = $yesterday->startOfDay()->timestamp;
        $yesterdayEnd   = $yesterday->endOfDay()->timestamp;

        $startOfWeek = Carbon::now()->startOfWeek()->subWeek()->timestamp; //上星期【
        $endOfWeek   = Carbon::now()->endOfWeek()->subWeek()->timestamp; //上星期

        $lastMonth    = Carbon::now()->subMonth(); //上個月
        $startOfMonth = $lastMonth->startOfMonth()->timestamp; // 上個月的第一天的timestamp
        $endOfMonth   = $lastMonth->endOfMonth()->timestamp; // 上個月的最後一天的timestamp

        $offset = ($this->currentPage - 1) * $this->perPage; // 分頁功能

        if( !$this->is_ignore_query_project_id ) // 暫時這樣子修 之後再抽func
        {
            switch($this->type)
            {
                case 'all':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with([
                            'projectCategory',
                            'projectCategory.projectSubCategory',
                            'images' => function ($query) {
                                $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                            },
                        ])
                        ->where('project_category_id', $this->project_category_id)
                        ->orderBy('report_date', 'desc')
                        ->skip($offset)
                        ->take($this->perPage)
                        ->get();
                    break;
                case 'lastMonth':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with([
                            'projectCategory',
                            'projectCategory.projectSubCategory',
                            'images' => function ($query) {
                                $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                            },
                        ])
                        ->where('project_category_id', $this->project_category_id)
                        ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                        ->orderBy('report_date', 'desc')
                        ->skip($offset)
                        ->take($this->perPage)
                        ->get();
                    break;
                case 'lastWeek':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with([
                            'projectCategory',
                            'projectCategory.projectSubCategory',
                            'images' => function ($query) {
                                $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                            },
                        ])
                        ->where('project_category_id', $this->project_category_id)
                        ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                        ->orderBy('report_date', 'desc')
                        ->skip($offset)
                        ->take($this->perPage)
                        ->get();

                    break;
                case 'yesterday':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with([
                            'projectCategory',
                            'projectCategory.projectSubCategory',
                            'images' => function ($query) {
                                $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                            },
                        ])
                        ->where('project_category_id', $this->project_category_id)
                        ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
                        ->orderBy('report_date', 'desc')
                        ->skip($offset)
                        ->take($this->perPage)
                        ->get();
                    break;
            }
        }
        else
        {
            switch($this->type)
            {
                case 'all':
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
                    break;
                case 'lastMonth':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with([
                            'projectCategory',
                            'projectCategory.projectSubCategory',
                            'images' => function ($query) {
                                $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                            },
                        ])
                        ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                        ->orderBy('report_date', 'desc')
                        ->skip($offset)
                        ->take($this->perPage)
                        ->get();
                    break;
                case 'lastWeek':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with([
                            'projectCategory',
                            'projectCategory.projectSubCategory',
                            'images' => function ($query) {
                                $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                            },
                        ])
                        ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                        ->orderBy('report_date', 'desc')
                        ->skip($offset)
                        ->take($this->perPage)
                        ->get();

                    break;
                case 'yesterday':
                    $logs = TaskReport::where('user_id', Auth::id())
                        ->with([
                            'projectCategory',
                            'projectCategory.projectSubCategory',
                            'images' => function ($query) {
                                $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                            },
                        ])
                        ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
                        ->orderBy('report_date', 'desc')
                        ->skip($offset)
                        ->take($this->perPage)
                        ->get();
                    break;
            }
        }

        return $logs;
    }

    public function loadYesterdayData()
    {
        $this->resetParam();
        $this->type                       = 'yesterday';
        $this->title                      = '昨天的資料';
        $this->is_ignore_query_project_id = true;
    }

    public function loadLastWeekData()
    {
        $this->resetParam();
        $this->type                       = 'lastWeek';
        $this->title                      = '上星期的資料';
        $this->is_ignore_query_project_id = true;
    }

    public function loadLastMonthData()
    {
        $this->resetParam();
        $this->type                       = 'lastMonth';
        $this->title                      = '上個月的資料';
        $this->is_ignore_query_project_id = true;
    }

    public function loadAllData()
    {
        $this->resetParam();
        $this->type                       = 'all';
        $this->title                      = '所有歷史資料';
        $this->is_ignore_query_project_id = true;
    }

    public function render()
    {
        $this->logs = $this->loadData(); // 使用 loadData 儲存在logs中 reportHistory是用fetchData 要統一 //first googleChart render 會跑兩次查詢待優化

        return view('livewire.pages.personal-reports', ['logs' => $this->logs]);
    }
}
