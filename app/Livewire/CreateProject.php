<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProjectSubCategory;
use App\Models\ProjectCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class CreateProject extends Component
{
    use WithPagination;

    public $project = ''; // 專案名稱
    public $newCategory = '';
    public $categories = ['前端', '後端', '企劃', 'QA', '行銷', '其他'];
    public $selectedCategories = [];

    public function updateCategories($category)
    {
        $key = array_search($category, $this->selectedCategories);
        if ($key !== false) {
            unset($this->selectedCategories[$key]);
        }
        $this->selectedCategories = array_values($this->selectedCategories);
    }

    public function addSubCategory()
    {
        $errors = [];

        if(empty($this->newCategory)){
            $errors[] = '新增工作類別不能為空！';
        }

        if(in_array($this->newCategory, $this->categories)){
            $errors[] = '工作類別已存在！';
        }

        if(empty($errors)){
            $this->categories[] = $this->newCategory;
            //$this->newCategory = '';
        }
        else
        {
            session()->flash('errorMessage', $errors);
        }
    }

    public function submitForm()
    {
        $selectedIndexes = array_keys(array_filter($this->selectedCategories));

        $selectedCategories = array_map(function($index) {
            return $this->categories[$index];
        }, $selectedIndexes);

        $errors = [];

        if (empty($this->project)) {
            $errors[] = '專案名稱不能為空！';
        }

        if (empty($selectedCategories)) {
            $errors[] = '至少需要選擇一個工作類別！';
        }

        if (!empty($errors)) {
            session()->flash('errorMessage', $errors);
        } 
        else 
        {
            // 查詢該工作類別是否已存在資料庫
            if ($projectCategory = ProjectCategory::where('category', $this->project)->first()) {
                $projectCategoryId = $projectCategory->id;

                foreach ($selectedCategories as $category) {
                    $existingSubCategory = ProjectSubCategory::where('project_category_id', $projectCategoryId)
                        ->where('sub_category', $category)
                        ->first();

                    if (!$existingSubCategory) {
                        ProjectSubCategory::create([
                            'project_category_id' => $projectCategoryId,
                            'sub_category' => $category,
                        ]);
                    }
                    else
                    {
                        $errors[] = $category . ' 類別已存在於專案' . $this->project . '，故不再新增此類別！';
                        session()->flash('errorMessage', $errors);
                    }
                }
            } 
            else 
            {
                $projectCategory = ProjectCategory::create(['category' => $this->project]);
                $projectCategoryId = $projectCategory->id;

                foreach ($selectedCategories as $category) {
                    ProjectSubCategory::create([
                        'project_category_id' => $projectCategoryId,
                        'sub_category' => $category,
                    ]);
                }
            }
            $this->showCreateProhectSuccessAlert();
            //session()->flash('createProjectMsgSuccess', '專案類別上傳成功'); 走sweetAlert2
        }
    }

    public function showCreateProhectSuccessAlert()
    {
        $tag = 'sweetalert.create_daily_success';

        $data = [
            'title' => config($tag . '.' . 'title'),
            'text'  => config($tag . '.' . 'text'),
            'icon'  => config($tag . '.' . 'icon'),
            'redirect' => true,
            'tag'   => Str::afterLast($tag, '.')  // create_daily_success
        ];

        $this->pushSweetAlert($data);
    }

    public function pushSweetAlert($data)
    {
        $this->dispatch('ShowSweetAlert', data: $data);
    }

    public function render()
    {
        return view('livewire.pages.create-project');
    }
}
