<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProjectCategory;
use App\Models\MailRelationship;
use App\Models\ProjectMailRelationship;
use App\Models\TaskReport;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class AllReports extends Component
{
    use WithPagination;

    public $relationProjectIds = [];
    public $relationUserIds = [];

    public function mount()
    {
        $mailRelationShip = MailRelationship::where('user_id', Auth::id())->first();

        $projectMailRelationShip = ProjectMailRelationship::where('user_id', Auth::id())->first();

        if( !empty($projectMailRelationShip) )
        {
            if( str_contains($projectMailRelationShip->relation_project_category_id, ',') )
            {
                $this->relationProjectIds = explode(',', $projectMailRelationShip->relation_project_category_id);
            }
            else //single
            {
                $this->relationProjectIds = [$projectMailRelationShip->relation_project_category_id];
            }
        }

        if( !empty($mailRelationShip) )
        {
            if( str_contains($mailRelationShip->relation_user_id, ',') )
            {
                $this->relationUserIds = explode(',', $mailRelationShip->relation_user_id);
            }
            else //single
            {
                $this->relationUserIds = [$mailRelationShip->relation_user_id];
            }
        }
    }
    public function linkUser(int $userId)
    {
        $data       = [];
        $data['id'] = $userId;
        $this->dispatch('updateComponent', link: 'user-report', data: $data);
    }
    public function linkProject(int $projectId)
    {
        $data       = [];
        $data['id'] = $projectId;
        $this->dispatch('updateComponent', link: 'project-report', data: $data);
    }
    public function linkDepartment(int $departmentId)
    {
        $data               = [];
        $data['department'] = User::getDepartmentNumToStrValue($departmentId); // string
        $this->dispatch('updateComponent', link: 'department-report', data: $data); 
    }

    public function render()
    {

        $sub_projects = ProjectCategory::whereIn('id', $this->relationProjectIds)->get();

        $sub_users = User::whereIn('id', $this->relationUserIds)->get();

        $department = User::getDepartmentOptions([Auth::user()->department]); // key => value // td => 工程部門

        $user_department_name = implode(array_values($department));

        $user_department_value = User::getDepartmentStrToNumValue(implode(array_keys($department)));

        return view('livewire.pages.all-reports', [
            'projects'              => $sub_projects,
            'users'                 => $sub_users,
            'user_department_name'  => $user_department_name,
            'user_department_value' => $user_department_value
        ]);
    }
}
