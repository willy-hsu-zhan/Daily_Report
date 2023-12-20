<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\TaskReport;
use App\Models\ProjectCategory;
use App\Models\ProjectSubCategory;
use Illuminate\Support\Facades\Storage;
use App\Models\TaskReportImage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Renderless;

class DailyLog extends Component {

    use WithPagination;

    use WithFileUploads;
    public $formData = [];
    public $initFormData = [];
    public $project_data = [];
    public $progress_data = [];
    public $temp_upload_image_index;
    public $s3_image_path;
    public $baseTextAreaHeight = 100;
    public $image; // upload files
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
    }

    public function mount()
    {
        $this->initFormData[] = [
            'project'        => $this->project_data,
            'progress'       => $this->progress_data,
            'category'       => [],
            'date'           => Date::now()->format('Y-m-d'),
            'time'           => null,
            'content'        => null,
            'images'         => [],
            'textAreaHeight' => $this->baseTextAreaHeight,
        ];

        $this->formData[] = [
            'project'        => $this->project_data,
            'progress'       => $this->progress_data,
            'category'       => [],
            'date'           => Date::now()->format('Y-m-d'),
            'time'           => null,
            'content'        => null,
            'images'         => [],
            'textAreaHeight' => $this->baseTextAreaHeight,
        ];
    }

    public function addField()
    {
        $this->formData[max(array_keys($this->formData)) + 1] = [
            'project'        => $this->project_data,
            'progress'       => $this->progress_data,
            'date'           => Date::now()->format('Y-m-d'),
            'category'       => [],
            'time'           => null,
            'content'        => null,
            'images'         => [],
            'textAreaHeight' => $this->baseTextAreaHeight,
        ];

        $this->initFormData[max(array_keys($this->initFormData)) + 1] = [
            'project'        => $this->project_data,
            'progress'       => $this->progress_data,
            'date'           => Date::now()->format('Y-m-d'),
            'category'       => [],
            'time'           => null,
            'content'        => null,
            'images'         => [],
            'textAreaHeight' => $this->baseTextAreaHeight,
        ];

        $this->showTotalTimeAlert();

        $this->setTextAreaHeight();
    }

    public function showTotalTimeAlert()
    {
        $tag = 'sweetalert.totaltime';

        $data = [
            'title' => config($tag . '.' . 'title.head') . $this->calculateTotalTime() . config($tag . '.' . 'title.tail'),
            'text'  => config($tag . '.' . 'text'),
            'icon'  => config($tag . '.' . 'icon'),
            'timer' => config($tag . '.' . 'timer'),
            'tag'   => Str::afterLast($tag, '.')
        ];

        $this->pushSweetAlert($data);
    }

    public function pushSweetAlert($data)
    {
        $this->dispatch('ShowSweetAlert', data: $data);
    }

    public function removeField($index)
    {
        if( count($this->formData) > 1 && count($this->initFormData) > 1 )
        {
            unset($this->formData[$index]);

            unset($this->initFormData[$index]);
        }

        $this->setTextAreaHeight();
    }

    public function saveUploadImageIndex($index) // 上傳圖片時先儲存對應表的index
    {
        $this->temp_upload_image_index = $index;
    }

    public function updatedImage() // 上傳圖片候用的func
    {
        $this->formData[$this->temp_upload_image_index]['images'][] = $this->image;

        $this->resetParams();
    }
    public function removeImage($form_index, $image_index)
    {
        $image = $this->formData[$form_index]['images'][$image_index];

        if( file_exists($image->path()) )
        {
            unlink($image->path());
        }

        unset($this->formData[$form_index]['images'][$image_index]);
        $this->formData[$form_index]['images'] = array_values($this->formData[$form_index]['images']);
    }

    public function resetParams()
    {
        $this->image                   = null;
        $this->temp_upload_image_index = null;
        $this->s3_image_path           = null;
    }

    public function calculateTotalTime()
    {
        $totalTime = 0;

        foreach( $this->formData as $data )
        {
            if( !empty($data['time']) && is_numeric($data['time']) )
            {
                $totalTime += $data['time'];
            }
        }
        return round($totalTime, 2);
    }

    public function handleProjectChange($index)
    {
        $selectedProjectId = $this->formData[$index]['project'];

        $projectSubCategories = ProjectSubCategory::where('project_category_id', $selectedProjectId)->get();

        $data = [];

        foreach( $projectSubCategories as $projectSubCategory )
        {
            $data[$projectSubCategory->sub_category] = $projectSubCategory->id;
        }

        if( isset($this->formData[$index]['category']) )
        {
            unset($this->formData[$index]['category']);
        }

        if( isset($this->initFormData[$index]['category']) )
        {
            unset($this->initFormData[$index]['category']);
        }

        if( isset($this->formData[$index]['progress']) )
        {
            unset($this->formData[$index]['progress']);
        }

        if( isset($this->formData[$index]['progress']) )
        {
            unset($this->formData[$index]['progress']);
        }

        $this->formData[$index]['category'] = $data;

        $this->initFormData[$index]['category'] = $data;

        $this->initFormData[$index]['progress'] = $this->progress_data;

        $this->formData[$index]['progress'] = $this->progress_data;

        $this->dispatch('resetSelectOptions', $index);
    }

    public function submitForm()
    {
        $errors = $this->validateFormData();

        if( empty($errors) )
        {
            if( $this->calculateTotalTime() < 8.0 )
            {
                $this->showConfirmUploadDailyAlert();
            }
            else
            {
                $this->saveReports();
            }
        }
        else
        {
            session()->flash('errorMessage', $errors);
        }
    }

    public function showConfirmUploadDailyAlert()
    {
        $tag = 'sweetalert.confirm_whether_to_upload_daily';

        $dismiss_tag = 'sweetalert.not_create_daily';

        $data = [
            'title'   => config($tag . '.' . 'title.head') . $this->calculateTotalTime() . config($tag . '.' . 'title.tail'), // data 可以寫在trait包成定義格式
            'text'    => config($tag . '.' . 'text'),
            'icon'    => config($tag . '.' . 'icon'),
            'dismiss' => [
                'title' => config($dismiss_tag . '.' . 'title'),
                'text'  => config($dismiss_tag . '.' . 'text'),
                'icon'  => config($dismiss_tag . '.' . 'icon'),
            ],
            'tag'     => Str::afterLast($tag, '.')  // confirm_whether_to_upload
        ];

        $this->pushSweetAlert($data);
    }
    public function validateFormData()
    {
        $errors = [];

        $index = 0; // 因應RemoveField不會array_value重新排序index，所以使用自定義index

        foreach( $this->formData as $data )
        {
            $text = '第' . $index + 1 . '欄位';

            if( $data['time'] == null )
            {
                $errors[] = $text . '請輸入花費時間！';
            }

            if( isset($data['time']) && is_numeric($data['time']) && $data['time'] >= 16 )
            {
                $errors[] = $text . '花費時間大於16請重新輸入！';
            }

            if( isset($data['time']) && is_numeric($data['time']) && $data['time'] <= 0 )
            {
                $errors[] = $text . '花費時間不可為0或是負數，請重新輸入！';
            }

            if( !empty($data['time']) && !is_numeric($data['time']) )
            {
                $errors[] = $text . '花費時間請輸入數字！';
            }

            if( !is_numeric($data['project']) )
            {
                $errors[] = $text . '請選擇專案！';
            }

            if( !isset($data['category']) || !is_string($data['category']) )
            {
                $errors[] = $text . '請選擇工作類別！';
            }

            if( !is_string($data['progress']) )
            {
                $errors[] = $text . '請選擇工作進度！';
            }

            if( empty(trim($data['content'])) )
            {
                $errors[] = $text . '請輸入工作內容！';
            }

            if( strlen($data['content']) >= 5000 )
            {
                $errors[] = $text . '工作內容超過最大輸入字數！';
            }

            if( $data['project'] == -1 ) // 選擇別的選項後再選擇"未選擇"
            {
                $errors[] = $text . '請選擇專案！';
            }

            if( $data['category'] == -1 ) // 選擇別的選項後再選擇"未選擇"
            {
                $errors[] = $text . '請選擇工作類別！';
            }

            if( $data['progress'] == -1 ) // 選擇別的選項後再選擇"未選擇"
            {
                $errors[] = $text . '請選擇工作進度！';
            }

            if( $data['date'] == null )
            {
                $errors[] = $text . '請輸入日期！';
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
                    $errors[] = $text . '請輸入正確的日期格式！';
                }
            }

            ++$index;
        }

        return $errors;
    }

    #[On('SweetAlertCallback')] // 成功才會回傳
    public function sweetAlertCallback($tag)
    {
        switch($tag)
        {
            case 'confirm_whether_to_upload_daily':
                $this->saveReports();
                break;
            default:
                break;
        }
    }

    public function showCreateDailySuccessAlert()
    {
        $tag = 'sweetalert.create_daily_success';

        $data = [
            'title'    => config($tag . '.' . 'title'),
            'text'     => config($tag . '.' . 'text'),
            'icon'     => config($tag . '.' . 'icon'),
            'redirect' => true,
            'tag'      => Str::afterLast($tag, '.')  // create_daily_success
        ];

        $this->pushSweetAlert($data);
    }
    // addField removeField setTextAreaHeight()
    public function setTextAreaHeight()
    {
        foreach( $this->formData as $key => $data )
        {
            $text = $this->formData[$key]['content'];

            $totalHeight = 0;

            $baseHeight = $this->baseTextAreaHeight;

            $lineHeight = $this->calculateLineHeight($text, $baseHeight);

            $totalHeight += $lineHeight;

            $this->formData[$key]['textAreaHeight'] = $totalHeight;
        }


    }

    public function calculateLineHeight($text, $baseHeight) // 基於chorme字型大小為'中'去做計算 基底的textArea可以塞四行字
    {
        if( empty($text) )
        {
            return $baseHeight;
        }

        $lines = explode("\n", $text);

        $lineHeight = 0;

        $oneLineHeight = 28;

        $addHeight = 0;

        $lineCount = count($lines);

        foreach( $lines as $line )
        {
            $length = 0;

            $strArr = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY); // 計算全形or半形長度，將line字節拆成Unicode字元儲存在strArr陣列中 //以全形為主，半形兩個字不等於一個全形字的寬度

            foreach( $strArr as $char )
            {
                $length += mb_strwidth($char, 'UTF-8'); // 判斷字的寬度
            }

            if( $length > 114 )
            {
                $addHeight += ($oneLineHeight * ((int)($length / 114)) - 1); // 如果每一行的長度大於 114，則增加一行字的高度

                $lineCount += (int)($length / 114);
            }

            $addHeight += $oneLineHeight;
        }

        if( $lineCount > 4 )
        {
            $addHeight -= $oneLineHeight * 4;

            $lineHeight += $addHeight;
        }

        $lineHeight += $baseHeight;

        return $lineHeight;
    }

    public function saveReports()
    {
        try
        {
            foreach( $this->formData as $data )
            {
                $taskReport = TaskReport::create([
                    'user_id'             => Auth::id(),
                    'project_category_id' => $data['project'],
                    'use_time'            => $data['time'],
                    'description'         => TaskReport::getFormatDescription($data['content']), // 儲存換行符號
                    'progress'            => TaskReport::PROGRESS_TYPE[$data['progress']],
                    'type'                => $data['category'], // type = project_sub_category -> sub_category 可只儲存 project_sub_category_id 替代type較合適
                    'report_date'         => Carbon::parse($data['date'])->addHours(8)->timestamp // 暫時修復 之前舊寫法都是存當天8點的時間於report_date，created_at = null，抓時間已created_at較合適
                ]);

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

        }
        catch (Exception $e)
        {
            Log::info($e->getMessage());
            session()->flash('updateReportMsgFail', '日報上傳失敗');
        }

        $this->showCreateDailySuccessAlert();
    }

    public function uploadToS3($image, $taskReportId) // 因應相同檔案名稱可能會存在於不同log紀錄，以user_id/log_id建立資料夾
    {
        $image_name = $image->getClientOriginalName();

        $s3_folder_path = '';

        $s3_folder_path .= config('app.env') == 'production' ? config('uploadfile.path.production') : config('uploadfile.path.dev');

        $s3_folder_path .= (Auth::id() . '/');

        $s3_folder_path .= ($taskReportId . '/'); // 可以寫在trait s3_folder_path

        Storage::disk('s3')->makeDirectory($s3_folder_path);

        $image->storePubliclyAs($s3_folder_path, $image_name, 's3');

        $this->s3_image_path = config('aws.bucket_file_path') . $s3_folder_path . $image_name;
    }

    public function render()
    {
        // get History
        $yesterday      = Carbon::yesterday(); //昨天
        $yesterdayStart = $yesterday->startOfDay()->timestamp;
        $yesterdayEnd   = $yesterday->endOfDay()->timestamp;

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
            ->get();

        return view('livewire.pages.daily-log', ['logs' => $logs]);
    }
}