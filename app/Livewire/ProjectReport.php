<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TaskReport;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\On;

class ProjectReport extends Component {
    public $id; // project_categort_id 之後統一
    public $user_id; // project_categort_id 之後統一
    public $selected_category_switch; // 默認選項為個人類別
    //public $category_type_switch = ['personal', 'task']; // 個人類別,工作類別 // 類別擴充 暫定只有可能有這兩樁 
    public $project_category_id;
    public $userName; // user Name
    public $type; // log type all * yesterday lastWeek lastMonth
    public $perPage = 10;
    public $currentPage = 1;
    public $projectName;
    public $subCategoryName;
    public $logs = []; // taskReport ORM
    protected $listeners = ['updatedPieChartUsers' => '$refresh', 'updatedPieChartSubCategory' => '$refresh']; //接收到updatedPieChart觸發事件值以後 更新$this->type $this->project 並強制重新渲染

    public function mount()
    {
        $this->selected_category_switch = "personal"; // 默認選項為個人類別
        $this->project_category_id      = $this->id; //content傳進來給定的初始值
        $this->projectName              = ProjectCategory::find($this->project_category_id)->value('category'); //content傳進來給定的初始值
    }

    #[On('updatedPieChartUsers')]
    public function piechartUsers($userName = null, $type = null) //更新圖表用listener //type為日期種類
    {
        $this->userName = $userName;

        $this->type = $type;

        $this->user_id = User::where('name', $this->userName)->value('id');

        $this->resetParam();

        $this->loadData();
    }

    public function resetParam()
    {
        $this->currentPage = 1; // reset currentPage

        $this->perPage = 10; // reset currentPage

        $this->logs = [];
    }

    #[On('updatedPieChartSubCategory')]
    public function piechartSubCategory($subCategoryName = null, $type = null) //更新圖表用listener //type為日期種類
    {
        $this->subCategoryName = $subCategoryName; // ## subCategoryName 對應 taskReport -> type 之後設立taskReport ORM 對應 subCategoryName -> type 方法 使參數較易解讀

        $this->type = $type; //type為日期種類

        $this->loadData();
    }

    public function changeCategoryType($newType) // 暫時把值寫死在前端 因為目前類別只有可能有這兩種 personal task
    {
        $this->selected_category_switch = $newType;
        //reset value 切換類別順便把初始值設為空 使googleChart圖表不要show出來
        $this->logs            = [];
        $this->currentPage     = 1; // 分頁指定頁重設
        $this->userName        = null;
        $this->user_id         = null;
        $this->subCategoryName = null;
        $this->type            = null;
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
    public function fetchData()
    {
        $offset = ($this->currentPage - 1) * $this->perPage;

        $logs = TaskReport::where('user_id', Auth::id())
            ->with(['projectCategory', 'projectCategory.projectSubCategory'])
            ->orderBy('report_date', 'desc')
            ->skip($offset)
            ->take($this->perPage)
            ->get();

        return $logs;
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

        switch($this->type)
        {
            case 'all':
                $logs = TaskReport::with(['projectCategory', 'projectCategory.projectSubCategory'])
                    ->where('project_category_id', $this->project_category_id)
                    ->orderBy('report_date', 'desc');
                break;
            case 'lastMonth':
                $logs = TaskReport::with(['projectCategory', 'projectCategory.projectSubCategory'])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                    ->orderBy('report_date', 'desc');
                break;
            case 'lastWeek':
                $logs = TaskReport::with(['projectCategory', 'projectCategory.projectSubCategory'])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                    ->orderBy('report_date', 'desc');
                break;
            case 'yesterday':
                $logs = TaskReport::with(['projectCategory', 'projectCategory.projectSubCategory'])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
                    ->orderBy('report_date', 'desc');
                break;
        }
        if( $this->selected_category_switch == 'personal' )
        {
            $logs = $logs->where('user_id', $this->user_id)->get();
        }
        else if( $this->selected_category_switch == 'task' ) // 子類別 在 takeReport的type內
        {
            $logs = $logs->where('type', $this->subCategoryName)->get(); // ## subCategoryName 對應 taskReport -> type 之後設立taskReport ORM 對應 subCategoryName -> type 方法 使參數較易解讀
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

        $startOfWeek = Carbon::now()->startOfWeek()->subWeek()->timestamp; //上星期
        $endOfWeek   = Carbon::now()->endOfWeek()->subWeek()->timestamp; //上星期

        $lastMonth    = Carbon::now()->subMonth(); //上個月
        $startOfMonth = $lastMonth->startOfMonth()->timestamp; // 上個月的第一天的timestamp
        $endOfMonth   = $lastMonth->endOfMonth()->timestamp; // 上個月的最後一天的timestamp

        if( $this->selected_category_switch == 'personal' )
        {
            //"姓名" => count
            $all_logs = TaskReport::where('project_category_id', $this->project_category_id)
                ->with(['projectCategory', 'projectCategory.projectSubCategory', 'user']) // Load the user relationship
                ->orderBy('report_date', 'desc')
                ->get()
                ->groupBy('user_id')
                ->mapWithKeys(function ($logs, $userId) {
                    $user     = User::find($userId);
                    $userName = $user?->name;
                    return [$userName => $logs->sum('use_time')];
                })
                ->toArray();

            $data['logs']['all'] = $all_logs;

            $lastMonth_logs = TaskReport::where('project_category_id', $this->project_category_id)
                ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                ->orderBy('report_date', 'desc')
                ->get()
                ->groupBy('user_id')
                ->mapWithKeys(function ($logs, $userId) {
                    $user     = User::find($userId);
                    $userName = $user?->name;
                    return [$userName => $logs->sum('use_time')];
                })
                ->toArray();

            $data['logs']['lastMonth'] = $lastMonth_logs;

            $lastWeek_logs = TaskReport::where('project_category_id', $this->project_category_id)
                ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                ->orderBy('report_date', 'desc')
                ->get()
                ->groupBy('user_id')
                ->mapWithKeys(function ($logs, $userId) {
                    $user     = User::find($userId);
                    $userName = $user?->name;
                    return [$userName => $logs->sum('use_time')];
                })
                ->toArray();

            $data['logs']['lastWeek'] = $lastWeek_logs;

            $yesterday_logs = TaskReport::where('project_category_id', $this->project_category_id)
                ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
                ->orderBy('report_date', 'desc')
                ->get()
                ->groupBy('user_id')
                ->mapWithKeys(function ($logs, $userId) {
                    $user     = User::find($userId);
                    $userName = $user?->name;
                    return [$userName => $logs->sum('use_time')];
                })
                ->toArray();

            $data['logs']['yesterday'] = $yesterday_logs;

            $data['link'] = 'project-reports'; // dispatch 補上link使content找到對應組件

            $this->dispatch('googleDrawUsers', data: $data); // 之後改成 googleDraw + data['type'] 統一 差異在返回dispatch給定的格式
        }
        else if( $this->selected_category_switch == 'task' )
        {
            $all_logs = TaskReport::where('project_category_id', $this->project_category_id)
                ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                ->orderBy('report_date', 'desc')
                ->get()
                ->groupBy('type')
                ->map(function ($logs) {
                    return $logs->sum('use_time');
                })
                ->toArray();
            /**array:5
             "後端" => 11
            "前端" => 45
            ] */

            $data['logs']['all'] = $all_logs;

            $lastMonth_logs = TaskReport::where('project_category_id', $this->project_category_id)
                ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                ->orderBy('report_date', 'desc')
                ->get()
                ->groupBy('type')
                ->map(function ($logs) {
                    return $logs->sum('use_time');
                })
                ->toArray();

            $data['logs']['lastMonth'] = $lastMonth_logs;

            $lastWeek_logs = TaskReport::where('project_category_id', $this->project_category_id)
                ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                ->orderBy('report_date', 'desc')
                ->get()
                ->groupBy('type')
                ->map(function ($logs) {
                    return $logs->sum('use_time');
                })
                ->toArray();

            $data['logs']['lastWeek'] = $lastWeek_logs;

            $yesterday_logs = TaskReport::where('project_category_id', $this->project_category_id)
                ->with(['projectCategory', 'projectCategory.projectSubCategory'])
                ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
                ->orderBy('report_date', 'desc')
                ->get()
                ->groupBy('type')
                ->map(function ($logs) {
                    return $logs->sum('use_time');
                })
                ->toArray();

            $data['logs']['yesterday'] = $yesterday_logs;

            $data['link'] = 'project-reports'; // dispatch 補上link使content找到對應組件

            $this->dispatch('googleDrawSubCategory', data: $data);
        }
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
        switch($this->type)
        {
            case 'all':
                $logs = TaskReport::with([
                    'projectCategory',
                    'projectCategory.projectSubCategory',
                    'images' => function ($query) {
                        $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                    },
                ])
                    ->where('project_category_id', $this->project_category_id)
                    ->orderBy('report_date', 'desc');
                break;
            case 'lastMonth':
                $logs = TaskReport::with([
                    'projectCategory',
                    'projectCategory.projectSubCategory',
                    'images' => function ($query) {
                        $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                    },
                ])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$startOfMonth, $endOfMonth])
                    ->orderBy('report_date', 'desc');
                break;
            case 'lastWeek':
                $logs = TaskReport::with([
                    'projectCategory',
                    'projectCategory.projectSubCategory',
                    'images' => function ($query) {
                        $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                    },
                ])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
                    ->orderBy('report_date', 'desc');
                break;
            case 'yesterday':
                $logs = TaskReport::with([
                    'projectCategory',
                    'projectCategory.projectSubCategory',
                    'images' => function ($query) {
                        $query->select('id as task_report_image_id', 'task_report_id', 's3_image_path');
                    },
                ])
                    ->where('project_category_id', $this->project_category_id)
                    ->whereBetween('report_date', [$yesterdayStart, $yesterdayEnd])
                    ->orderBy('report_date', 'desc');
                break;
        }

        if( $this->type != null )
        {
            if( $this->selected_category_switch == 'personal' )
            {
                $logs = $logs->where('user_id', $this->user_id)->skip($offset)->take($this->perPage)->get();
            }
            else if( $this->selected_category_switch == 'task' ) // 子類別 在 takeReport的type內
            {
                $logs = $logs->where('type', $this->subCategoryName)->skip($offset)->take($this->perPage)->get(); // ## subCategoryName 對應 taskReport -> type 之後設立taskReport ORM 對應 subCategoryName -> type 方法 使參數較易解讀
            }
        }

        return $logs;
    }
    public function render()
    {
        $this->logs = $this->loadData(); // 使用 loadData 儲存在logs中 reportHistory是用fetchData 要統一 //first googleChart render 會跑兩次查詢待優化
        return view('livewire.pages.project-report', ['logs' => $this->logs]);
    }
}
