<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TaskReport;
use App\Models\TaskReportImage;
use App\Models\ProjectCategory;
use App\Models\ProjectSubCategory;
use Exception;
use Illuminate\Support\Facades\Date;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;
use Livewire\Attributes\On;

class DailyForm extends Component {
    use WithPagination;
    public $id;
    public $project;
    public $category;
    public $content;
    public $time;
    public $progress;
    public $date;
    public $project_data = [];
    public $progress_data = [];
    public $formData = [];
    public $initFormData = [];
    public $category_data = [];
    public $is_edited = false;
    public $image; // upload files
    public $temp_upload_image_index;
    public $s3_image_path;

    use WithFileUploads;

    public function __construct()
    {
        $projects = ProjectCategory::all();

        foreach( $projects as $project )
        {
            $this->project_data[$project->category] = $project->id;
        }

        foreach( TaskReport::PROGRESS_TYPE as $index => $project )
        {
            $this->progress_data[$project] = $index;
        }

        $project_sub_categorys = ProjectSubCategory::all()->groupBy('project_category_id');

        foreach( $project_sub_categorys as $key => $project_sub_category )
        {
            $this->category_data[$key] = $project_sub_category->pluck('sub_category')->toArray();
        }
    }
    public function mount()
    {
        $this->category_data = $this->initProjectChange($this->project);

        $projectIndex = $this->project_data[$this->project];
        if( $projectIndex !== false )
        {
            unset($this->project_data[$this->project]);
            $this->project_data = [$this->project => $projectIndex] + $this->project_data;
        }

        $categoryIndex = $this->category_data[$this->category];
        if( $categoryIndex !== false )
        {
            unset($this->category_data[$this->category]);
            $this->category_data = [$this->category => $categoryIndex] + $this->category_data;
        }

        $progressIndex = $this->progress_data[$this->progress];
        if( $progressIndex !== false )
        {
            unset($this->progress_data[$this->progress]);
            $this->progress_data = [$this->progress => $progressIndex] + $this->progress_data;
        }

        // temp fix resort $this->category_data
        $count = 0;
        foreach( $this->category_data as $index => $value )
        {
            $this->category_data[$index] = $count++;
        }

        $this->formData[] = [
            'project'  => $this->project_data,
            'category' => $this->category_data,
            'time'     => $this->time,
            'content'  => strip_tags($this->content), // 過濾br標籤
            'progress' => $this->progress_data,
            'date'     => $this->date,
            'images'   => [],
        ];

        $this->initFormData[] = [
            'project'  => $this->project_data,
            'category' => $this->category_data,
            'progress' => $this->progress_data,
            'date'     => Date::now()->format('Y-m-d'),
            'time'     => null,
            'content'  => null,
            'images'   => [],
        ];
    }

    public function saveUploadImageIndex($index)
    {
        $this->temp_upload_image_index = $index;
    }

    public function updatedImage()
    {
        $this->formData[$this->temp_upload_image_index]['images'][] = $this->image;

        $this->resetParams();
    }

    public function resetParams()
    {
        $this->image                   = null;
        $this->temp_upload_image_index = null;
        $this->s3_image_path           = null;
    }

    public function removeImage($form_index, $image_index)
    {
        $image = $this->formData[$form_index]['images'][$image_index];

        if( file_exists($image->path()) )
        {
            unlink($image->path()); // 刪除storage檔案
        }

        unset($this->formData[$form_index]['images'][$image_index]);
        $this->formData[$form_index]['images'] = array_values($this->formData[$form_index]['images']);
    }

    public function uploadToS3($image, $taskReportId) // 因應相同檔案名稱可能會存在於不同log紀錄，以user_id/log_id建立資料夾
    {
        $image_name = $image->getClientOriginalName();

        $s3_folder_path = '';

        $s3_folder_path .= config('app.env') == 'production' ? config('uploadfile.path.production') : config('uploadfile.path.dev');

        $s3_folder_path .= (Auth::id() . '/');

        $s3_folder_path .= ($taskReportId . '/');

        Storage::disk('s3')->makeDirectory($s3_folder_path);

        $image->storePubliclyAs($s3_folder_path, $image_name, 's3');

        $this->s3_image_path = config('aws.bucket_file_path') . $s3_folder_path . $image_name;
    }

    public function handleProjectChange($index)
    {
        $this->is_edited = true;

        $selectedProjectId = $this->formData[$index]['project'];

        $this->project = $selectedProjectId; //修改在ProjectChange時的預設值取這個值

        $this->formData[$index]['category']     = null;
        $this->initFormData[$index]['category'] = null;

        $this->formData[$index]['progress']     = null;
        $this->initFormData[$index]['progress'] = null;

        $projectSubCategories = ProjectSubCategory::where('project_category_id', $selectedProjectId)->get();

        $content = [];

        $count = 0; // temp fix
        foreach( $projectSubCategories as $projectSubCategory )
        {
            $content['未選擇']                             = -1; //暫時修改
            $content[$projectSubCategory->sub_category] = $count++;
        }

        $this->formData[$index]['category']     = $content;
        $this->initFormData[$index]['category'] = $content;

        foreach( TaskReport::PROGRESS_TYPE as $key => $project ) // 暫時修改
        {
            $this->initFormData[$index]['progress']['未選擇']    = -1;
            $this->formData[$index]['progress']['未選擇']        = -1;
            $this->initFormData[$index]['progress'][$project] = $key;
            $this->formData[$index]['progress'][$project]     = $key;
        }

        $this->dispatch('resetAllSelectOptions');
    }

    public function initProjectChange($selectedProject)
    {
        $selectedProjectId = ProjectCategory::where('category', $selectedProject)->value('id');

        $projectSubCategories = ProjectSubCategory::where('project_category_id', $selectedProjectId)->get();

        $content = [];

        foreach( $projectSubCategories as $projectSubCategory )
        {
            $content[$projectSubCategory->sub_category] = $projectSubCategory->id;
        }

        return $content;
    }

    public function submitForm()
    {
        $errors = $this->validateFormData();

        if( empty($errors) )
        {
            $this->saveReports();

            $this->showUpdateDailySuccessAlert();

            //return Redirect::route('home');
            //session()->flash('updateReportMsgSuccess', '日報修改成功'); 走sweetAlert2
        }
        else
        {
            session()->flash('errorMessage', $errors);
        }
    }

    public function pushSweetAlert($data)
    {
        $this->dispatch('ShowSweetAlert', data: $data);
    }

    public function saveReports()
    {
        $taskReport = TaskReport::find($this->id);

        foreach( $this->formData as $index => $data )
        {
            $taskReport->project_category_id = $data['project'];
            $taskReport->use_time            = $data['time'];
            $taskReport->description         = TaskReport::getFormatDescription($data['content']);
            $taskReport->progress            = TaskReport::PROGRESS_TYPE[$data['progress']];
            $taskReport->type                = $data['category'];
            $taskReport->report_date         = strtotime($data['date']);
            $taskReport->save();
        }

        if( !empty($data['images']) )
        {
            foreach( $data['images'] as $image )
            {
                $this->uploadToS3($image, $taskReport->id);

                TaskReportImage::create([
                    'task_report_id' => $taskReport->id,
                    's3_image_path'  => $this->s3_image_path,
                ]);
            }
        }
    }

    public function validateFormData()
    {
        $errors = [];

        $progress_type_value = ['進行中' => 0, '已完成' => 1, 'Delay' => 2]; // temp fix

        foreach( $this->formData as $index => $data ) //加入使用者已選中選項
        {

            if( is_array($data['project']) && is_array($data['category']) && is_array($data['progress']) ) //全部都沒更改
            {
                $data['progress'] = $progress_type_value[$this->progress];
                $data['category'] = $this->category;
                $data['project']  = ProjectCategory::where('category', $this->project)->value('id');
            }

            if( is_array($data['project']) )
            {
                $data['project'] = ProjectCategory::where('category', $this->project)->value('id');

                if( is_array($data['category']) ) //只更改progress
                {
                    $data['category'] = $this->category;
                }

                if( is_array($data['progress']) ) //category
                {
                    $data['progress'] = $progress_type_value[$this->progress];
                }
            }

            $text = '';

            if( $data['category'] == -1 || is_array($data['category']) )
            {
                $errors[] = $text . '請選擇工作類別！';
            }

            if( $data['progress'] == -1 || is_array($data['progress']) )
            {
                $errors[] = $text . '請選擇工作進度！';
            }

            if( $data['time'] == null )
            {
                $errors[] = $text . '請輸入花費時間！';
            }

            if( !is_numeric($data['time']) && $data['time'] != null )
            {
                $errors[] = $text . '花費時間必須為數字！';
            }

            if( $data['date'] == null )
            {
                $errors[] = $text . '請輸入日期！';
            }

            if( isset($data['time']) && is_numeric($data['time']) && $data['time'] >= 16 )
            {
                $errors[] = $text . '花費時間時數大於16請重新輸入！';
            }

            if( empty(trim($data['content'])) )
            {
                $errors[] = $text . '請輸入工作內容！';
            }

            if( strlen($data['content']) >= 5000 )
            {
                $errors[] = $text . '工作內容超過最大輸入字數！';
            }

            if( !is_numeric($data['progress']) ) // 暫時修改
            {
                $errors[] = $text . '請選擇工作進度！';
            }

            if( $data['date'] != null && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) )
            {
                $errors[] = $text . '日期格式必須為年-月-日！';
            }
            else // 格式確認為YY-MM-DD 再來判斷日期合不合法 
            {
                try
                {
                    Carbon::parse($data['date'])->timestamp;
                }
                catch (Exception $e)
                {
                    $errors[] = '請輸入正確的日期格式！';
                }
            }

            $this->formData[$index] = $data;
        }

        return $errors;
    }

    public function showUpdateDailySuccessAlert()
    {
        $tag = 'sweetalert.update_daily_success';

        $data = [
            'title' => config($tag . '.' . 'title'),
            'text'  => config($tag . '.' . 'text'),
            'icon'  => config($tag . '.' . 'icon'),
            'redirect' => true,
            'tag'   => Str::afterLast($tag, '.')
        ];

        $this->pushSweetAlert($data);
    }

    // #[On('SweetAlertCallback')]
    // public function sweetAlertCallback($tag)
    // {
    // }

    public function render()
    {
        return view('livewire.components.daily-form');
    }
}