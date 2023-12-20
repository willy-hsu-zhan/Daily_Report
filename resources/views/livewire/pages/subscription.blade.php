<div class="container">
    <section>
        <div class="col-md-3 mb-4 mt-5">
            @if(!empty($this->userName))
                    <div class="card mb-3">
                        <div class="profile">
                                <div class="profile-title">{{ $this->userDepartment }}</div>
                                <div>{{ $this->userName }}</div>
                        </div>
                    </div>
            @endif
            <div class="card">
                <div class="card-title text-center">
                    <h2>部門列表</h2>
                </div>
                <form class="d-flex mb-3" wire:submit.prevent="submitSearch">
                    <input class="form-control me-2" type="search" placeholder="Name or Email" aria-label="Search" wire:model="search">
                    <button class="btn btn-pink" type="submit">Search</button>
                </form>        
                <div class="accordion accordion-flush" id="accordionFlush">
                    @foreach($departments as $department => $departmentName)
                        <div class="accordion-item mb-2">
                            <div class="accordion-header" id="flush-heading{{ $loop->index }}">
                                <button class="accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse{{ $loop->index }}" aria-expanded="true" aria-controls="flush-collapse{{ $loop->index }}"> {{ $departmentName }}</button>
                            </div>
                            <div id="flush-collapse{{ $loop->index }}" class="accordion-collapse collapse" aria-labelledby="flush-heading{{ $loop->index }}" data-bs-parent="#accordionFlush">
                                <ul class="list-group">
                                    @foreach($users->where('department', $department) as $user)
                                        <a class="list-group-item" href="#" wire:click.prevent="toggleInitUserSelection('{{ $user->id }}')">{{ $user->name }}</a>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-9 mt-3 mb-4" wire:submit.prevent="submitForm">
            <div class="header">
                <div class=" mb-4 flexcenter">
                    <h1 class="distribute">訂閱系統</h1>
                    <div class="title-content">訂閱者所訂閱的專案或使用者，將於每日晚上九點將工作日誌傳送至訂閱者的信箱。<br>若需要修改訂閱內容，請先在左側列表選擇訂閱者，再修改該訂閱者的訂閱內容。</div>
                </div>
            </div>
            <form>
                <div class="card mb-3">
                    <div class="card-title text-center">
                        <h2>訂閱專案</h2>
                    </div>
                    <div>
                        <div class="left content flexcenter">
                            @foreach ($selectedProjects as $key => $project)
                                <div class="form-check" wire:key="project-{{ $project['id']}}">
                                    <input wire:model="selectedProjects.{{ $key }}" type="checkbox" id="{{ $project['id'] }}" name="projectdata[]" value="{{ $project['id'] }}" style="margin-right: 10px;" 
                                    @if($project['checked'] == true) checked @endif
                                    @if(empty($this->user)) disabled @endif>
                                    <label>{{ $project['category'] }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-title text-center">
                        <h2>訂閱使用者</h2>
                    </div>
                    <div>
                        <div class="left content flexcenter">
                            @foreach ($selectedUsers as $key => $user)
                                <div class="form-check" wire:key="user-{{ $user['id']}}">
                                    <input wire:model="selectedUsers.{{ $key }}" type="checkbox" id="{{ $user['id'] }}" name="userdata[]" value="{{ $user['id'] }}" style="margin-right: 10px;" 
                                    @if($user['checked'] == true) checked @endif
                                    @if(empty($this->user)) disabled @endif>
                                    <label>{{ $user['name'] }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flexcenter mt-3 mb-3">
                    <button type="submit" class="btn btn-blue col-md-6" @if(empty($this->user)) disabled @endif>儲存</button>
                </div>
                @if(empty($this->user))
                    <div class="alert alert-warning">請先於左側部門列表中選擇或搜尋一位使用者!</div>
                @endif
                @if (session()->has('errorMessage'))
                    <div class="alert alert-warning">
                        {{ session('errorMessage') }}
                    </div>
                @endif
                {{-- @if (session()->has('subscriptionMsgSuccess'))
                    <div class="alert alert-success">
                        {{ session('subscriptionMsgSuccess') }}
                    </div>
                @endif --}}
            </form>
        </div>
    </section>
</div>
