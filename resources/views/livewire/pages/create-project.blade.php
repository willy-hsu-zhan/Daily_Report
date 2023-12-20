<div class="warp container">
    <div class="header">
        <div class=" mb-4 flexcenter">
            <div class="col-md-8">
                <h1 class="distribute">新增專案</h1>
                <div class="title-content">在填寫工作日誌時，若發現列表中未擁有需要填報的專案類別<br>可在這裡自行新增專案類別，並勾選該專案所擁有的工作類型</div>
            </div>
        </div>
    </div>
    <form class="col-md-12 mb-4" wire:submit.prevent="submitForm">
        <div class="card">
            <div class="card-title text-center">
                <h2>新增專案</h2>
            </div>
            <div class="content">
                <div class="mx-4">
                    <section class="mt-3">
                        <div class="input-group mb-3 mr-2">
                            <span class="input-group-text">專案名稱</span>
                            <input type="text" class="form-control" wire:model="project" aria-label="Amount (to the nearest dollar">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">新增工作類別</span>
                            <input id="item" type="text" class="form-control" wire:model="newCategory">
                            <div class="btn btn-pink" wire:click="addSubCategory">新增</div>
                        </div>
                    </section>
                    <div class="mb-3">
                        <span class="input-group-text">工作類別</span>
                        <div class="input-group-text">
                            <div class="flex">
                                @foreach ($categories as $key => $category)
                                    <div>
                                        <div class="form-check">
                                            <input type="checkbox" id="{{ $key }}" name="{{ $category }}" wire:model="selectedCategories.{{ $key }}" style="margin-right: 10px;">
                                            <label wire:click="updateCategories('{{ $category }}')" data-category="{{ $category }}">{{ $category }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (session()->has('errorMessage'))
            <div class="alert alert-warning">
                @foreach (session('errorMessage') as $index => $message)
                    {{ $message }}
                    @if (count(session('errorMessage')) != $index + 1)
                        <br>
                    @endif
                @endforeach
            </div>
        @endif
        @if (session()->has('createProjectMsgSuccess'))
            <div class="alert alert-success">
                {{ session('createProjectMsgSuccess') }}
            </div>
        @endif
        <div class="flexcenter mt-3 mb-3"><button type="submit" class="btn btn-blue col-md-3">送出</button></div>
    </form>
</div>