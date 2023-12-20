<div class="warp container">
    <div class="header">
        <div class=" mb-4 flexcenter">
            <div class="col-md-8">
                <h1>全報表</h1>
                <div class="title-content">有訂閱及自己部門的工作日誌在這裡以圖表方式呈現<br>點擊圖表內容以查看詳細資料</div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="center">
            <div class="col-md-4">
                <div class="bg-color-1">
                    <h1>個人類別報表</h1>
                </div>
                @if (!empty($users))
                    @foreach ($users as $user)
                        <a class="data-link" href="javascript:void(0);" wire:click="linkUser({{ $user->id }})">{{ $user->name }}</a>
                    @endforeach
                @endif
            </div>
            <div class="col-md-4">
                <div class="bg-color-1">
                    <h1>專案類別報表</h1>
                </div>
                @if (!empty($projects))
                    @foreach ($projects as $project)
                        <a class="data-link" wire:click="linkProject({{ $project->id }})">{{ $project->category }}</a>
                    @endforeach
                @endif
            </div>
            <div class="col-md-4">
                <div class="bg-color-1">
                    <h1>部門類別報表</h1>
                </div>
                <a class="data-link" wire:click="linkDepartment({{ $user_department_value }})">{{ $user_department_name }}</a>
            </div>
        </div>
    </div>
</div>