<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProjectSubCategory;
use App\Models\ProjectCategory;
use App\Models\MailRelationship;
use App\Models\ProjectMailRelationship;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class Subscription extends Component
{

    public $selectedUsers = []; //該使用者訂閱用戶的選項
    public $selectedProjects = []; //該使用者訂閱專案的選項
    public $initSelectedUsers = []; //該使用者訂閱專案的初始選項
    public $initSelectedProjects = []; //該使用者訂閱專案的初始選項
    public $user; //該使用者
    public $userName; //該使用者名稱
    public $userDepartment; //該使用者部門
    public $relation_user_id;
    public $relation_project_category_id;
    public $search; //搜尋字串
    protected $listeners = ['refreshComponent' => '$refresh'];
    public $users; //User ORM

    public function mount()
    {
        $users = User::where('department', '!=', 'guest')->get();

        foreach( $users as $user )
        {
            $this->selectedUsers[]     = [
                'id'      => $user->id,
                'name'    => $user->name,
                'checked' => false
            ];
            $this->initSelectedUsers[] = [
                'id'      => $user->id,
                'name'    => $user->name,
                'checked' => false
            ];
        }

        $projectCategorys = ProjectCategory::all();

        foreach( $projectCategorys as $projectCategory )
        {
            $this->selectedProjects[]     = [
                'id'       => $projectCategory->id,
                'category' => $projectCategory->category,
                'checked'  => false
            ];
            $this->initSelectedProjects[] = [
                'id'       => $projectCategory->id,
                'category' => $projectCategory->category,
                'checked'  => false
            ];
        }

        $this->users = $users;
    }

    public function resetSelectedUserOptions()
    {
        $users = User::where('department', '!=', 'guest')->get();

        $this->selectedUsers = [];

        foreach( $users as $user )
        {
            $this->selectedUsers[]     = [
                'id'      => $user->id,
                'name'    => $user->name,
                'checked' => false
            ];
            $this->initSelectedUsers[] = [
                'id'      => $user->id,
                'name'    => $user->name,
                'checked' => false
            ];
        }
    }

    public function resetSelectedProjectOptions()
    {
        $projectCategorys = ProjectCategory::all();

        $this->selectedProjects = [];

        foreach( $projectCategorys as $projectCategory )
        {
            $this->selectedProjects[]     = [
                'id'       => $projectCategory->id,
                'category' => $projectCategory->category,
                'checked'  => false
            ];
            $this->initSelectedProjects[] = [
                'id'       => $projectCategory->id,
                'category' => $projectCategory->category,
                'checked'  => false
            ];
        }
    }

    public function resetState()
    {
        $this->resetSelectedUserOptions();

        $this->resetSelectedProjectOptions();

        $this->resetParams();
    }

    public function setUserSelectedUserOptions()
    {
        $mailRelationShip = MailRelationship::where('user_id', $this->user->id)->first();

        if( !empty($mailRelationShip) )
        {
            $relationUserIds = [];

            if( str_contains($mailRelationShip->relation_user_id, ',') )
            {
                $relationUserIds = explode(',', $mailRelationShip->relation_user_id);
            }
            else //single
            {
                $relationUserIds = [$mailRelationShip->relation_user_id];
            }

            foreach( $this->selectedUsers as $index => $user )
            {
                if( in_array($user['id'], $relationUserIds) )
                {
                    $this->selectedUsers[$index]['checked'] = true;
                }
                else
                {
                    $this->selectedUsers[$index]['checked'] = false;
                }
            }
        }
    }

    public function setUserSelectedProjectOptions()
    {
        $projectMailRelationShip = ProjectMailRelationship::where('user_id', $this->user->id)->first();

        if( !empty($projectMailRelationShip) )
        {
            $relationProjectIds = [];

            if( str_contains($projectMailRelationShip->relation_project_category_id, ',') )
            {
                $relationProjectIds = explode(',', $projectMailRelationShip->relation_project_category_id);
            }
            else //single
            {
                $relationProjectIds = [$projectMailRelationShip->relation_project_category_id];
            }

            foreach( $this->selectedProjects as $index => $project )
            {
                if( in_array($project['id'], $relationProjectIds) )
                {
                    $this->selectedProjects[$index]['checked'] = true;
                }
                else
                {
                    $this->selectedProjects[$index]['checked'] = false;
                }
            }
        }
    }

    public function toggleInitUserSelection($userId)
    {
        $user = User::find($userId);

        $this->user = $user;

        $this->userName = $user->name;

        $this->userDepartment = User::getDepartmentName($user->department);

        $this->resetSelectedUserOptions();

        $this->resetSelectedProjectOptions();

        $this->setUserSelectedUserOptions();

        $this->setUserSelectedProjectOptions();
    }

    public function resortSelectedOptions()
    {
        foreach( $this->selectedProjects as $index => $project ) // resort
        {
            if( !is_array($project) ) // 表示選擇的選項
            {
                $selectStatus                              = $this->selectedProjects[$index]; // true.false SWAP
                $this->selectedProjects[$index]            = $this->initSelectedProjects[$index];
                $this->selectedProjects[$index]['checked'] = $selectStatus;
            }
        }

        foreach( $this->selectedUsers as $index => $user )
        {
            if( !is_array($user) ) // 表示選擇的選項
            {
                $selectStatus                           = $this->selectedUsers[$index]; // true.false SWAP
                $this->selectedUsers[$index]            = $this->initSelectedUsers[$index];
                $this->selectedUsers[$index]['checked'] = $selectStatus;
            }
        }
    }

    public function resetParams()
    {
        $this->relation_user_id = null;

        $this->relation_project_category_id = null;

        $this->user = null;

        $this->userName = null;

        $this->userDepartment = null;
    }

    public function setCombineRelationOptions()
    {
        $relation_user_id = "";

        $relation_project_category_id = "";

        foreach( $this->selectedProjects as $index => $project ) // 用implode去做可以簡化
        {
            if( $project['checked'] )
            {
                $relation_project_category_id .= $project['id'];
                $relation_project_category_id .= ",";
            }
        }

        foreach( $this->selectedUsers as $index => $user ) // 用implode去做可以簡化
        {
            if( $user['checked'] )
            {
                $relation_user_id .= $user['id'];
                $relation_user_id .= ",";
            }
        }

        if( strlen($relation_user_id) > 1 ) // 用implode去做可以簡化
        {
            $relation_user_id = substr($relation_user_id, 0, -1); // 去除最後一個逗點
        }

        if( strlen($relation_project_category_id) > 1 ) // 用implode去做可以簡化
        {
            $relation_project_category_id = substr($relation_project_category_id, 0, -1); // 去除最後一個逗點
        }

        $this->relation_user_id = $relation_user_id;

        $this->relation_project_category_id = $relation_project_category_id;
    }

    public function saveRelationShips()
    {
        $mailRelationShip = MailRelationship::where('user_id', $this->user->id)->first();

        if( !empty($mailRelationShip) )
        {
            $mailRelationShip->relation_user_id = $this->relation_user_id;

            $mailRelationShip->save();
        }
        else // DB 無使用者資料 create
        {
            MailRelationship::create([
                'user_id'          => $this->user->id,
                'relation_user_id' => $this->relation_user_id,
            ]);
        }

        $projectMailRelationShip = ProjectMailRelationship::where('user_id', $this->user->id)->first();

        if( !empty($projectMailRelationShip) )
        {
            $projectMailRelationShip->relation_project_category_id = $this->relation_project_category_id;

            $projectMailRelationShip->save();
        }
        else // DB 無使用者資料 create
        {
            ProjectMailRelationship::create([
                'user_id'                      => $this->user->id,
                'relation_project_category_id' => $this->relation_project_category_id
            ]);
        }

        //session()->flash('subscriptionMsgSuccess', '訂閱資訊更新成功');
    }

    public function submitForm()
    {
        $this->resortSelectedOptions(); // 送出表單後值發生改變，恢復送出表單前，使用者選擇選項對應view的狀態

        $this->setCombineRelationOptions(); // 根據form組出使用者所選的訂閱專案 or User 字串存進DB

        $this->saveRelationShips();

        $this->showUpdateSubscriptionSuccessAlert();

        $this->resetState();
    }

    public function submitSearch()
    {
        if( !empty($this->search) )
        {
            $usersByName = User::where('name', 'like', '%' . $this->search . '%')->where('department', '!=', 'guest');

            $usersByEmail = User::where('email', 'like', $this->search . '%')->where('department', '!=', 'guest');

            $users = $usersByName->union($usersByEmail)->get();

            if( !$users->isEmpty() )
            {
                $this->users = $users;
            }
            else
            {
                session()->flash('errorMessage', '搜尋未匹配任何使用者！');
            }
        }
        else
        {
            $users = User::where('department', '!=', 'guest')->get();

            $this->users = $users;
        }
    }

    public function showUpdateSubscriptionSuccessAlert()
    {
        $tag = 'sweetalert.update_subscription_success';

        $data = [
            'title' => config($tag . '.' . 'title'),
            'text'  => config($tag . '.' . 'text'),
            'icon'  => config($tag . '.' . 'icon'),
            'tag'   => Str::afterLast($tag, '.')
        ];

        $this->pushSweetAlert($data);
    }

    public function pushSweetAlert($data)
    {
        $this->dispatch('ShowSweetAlert', data: $data);
    }

    public function render()
    {
        $users = $this->users;

        $projectCategory = ProjectCategory::all();

        $departments = $users->pluck('department')->unique()->toArray();

        $departments = User::getDepartmentOptions($departments); // key => value // td => 工程部門

        return view('livewire.pages.subscription', [
            'users'       => $users,
            'projects'    => $projectCategory,
            'departments' => $departments
        ]);
    }
}
