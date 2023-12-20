<div>
    <br><br><br>
    @props(['id', 'project', 'category', 'content', 'time', 'progress', 'date'])
    <div class="warp container">
        <form id="form" class="mb-2" wire:submit.prevent="submitForm">
            <div class="card">
                @foreach ($formData as $index => $data)
                    <div>
                        <div class="mb-2" id="ceil_{{ $index }}">
                            <div class="flex card-title text-center">
                                <a class="btn btn-close" href="javascript:void(0);">x</a>
                            </div>
                            <div class="content">
                                <div class="mx-4">
                                    <section class="mt-2 mb-2">
                                        <div class="col-md-7">
                                            <div class="input-group">
                                                <div class="input-group">
                                                    <span class="input-group-text">專案</span>
                                                    <select name="專案" id="0" class="input-group-btn" wire:model="formData.{{ $index }}.project" wire:change="handleProjectChange({{ $index }})">
                                                        {{-- <option value= -1>未選擇</option> --}}
                                                        @if(!is_numeric($data['project'])) {{-- 還沒選專案時 --}}
                                                            @foreach ($data['project'] as $key => $value)
                                                                <option value="{{ $value }}">{{ $key }}</option>
                                                            @endforeach
                                                        @else
                                                            @foreach ($initFormData[$index]['project'] as $key => $value)
                                                                <option value="{{ $value }}">{{ $key }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text">工作類別</span>
                                                    <select name="工作類別" id="0" class="input-group-btn" wire:model.debounce.0ms="formData.{{ $index }}.category">
                                                        {{-- <option value= -1>未選擇</option> --}}
                                                        @if (!is_null($data['category']) && !is_string($data['category'])) {{-- 還沒選專案時 --}}
                                                            @foreach ($data['category'] as $key => $value)
                                                                <option value="{{ $key  }}">{{ $key  }}</option>
                                                            @endforeach
                                                        @elseif (is_string($data['category']))
                                                            @foreach ($initFormData[$index]['category'] as $key => $value)
                                                                <option value="{{ $key }}">{{ $key }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text">進度</span>
                                                    <select name="進度" id="0" class="input-group-btn" wire:model.debounce.0ms="formData.{{ $index }}.progress">
                                                        {{-- <option value= -1>未選擇</option> --}}
                                                        @if(!is_numeric($data['progress'])) {{-- 還沒選專案時 --}}
                                                            @foreach ($data['progress'] as $key => $value)
                                                                <option value="{{ $value }}">{{ $key }}</option>
                                                            @endforeach
                                                        @else
                                                            @foreach ($initFormData[$index]['progress'] as $key => $value)
                                                                <option value="{{ $value }}">{{ $key }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text">花費時間</span>
                                            <input name="time" id="0" type="text" class="form-control" wire:model="formData.{{ $index }}.time">
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text">日期</span>
                                            <input name="date" id="0" type="text" class="form-control" wire:model="formData.{{ $index }}.date">
                                        </div>
                                    </section>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">工作內容</span>
                                        <textarea name="content" id="0" class="form-control" aria-label="With textarea" wire:model="formData.{{ $index }}.content"></textarea>
                                    </div>
                                </div>
                            </div>
                            @if (!is_null($data['images']))
                                @foreach($data['images'] as $image_index => $image)
                                    <div class="d-flex justify-content-center">
                                        <section class="gallery">
                                            <ul class="gallery__list">
                                                <img class="garbage-icon pointer-icon" wire:click="removeImage({{ $index }},{{ $image_index }})" src="{{ asset('images/garbage.png') }}" href="#" alt="Remove Icon">
                                                <li class="gallery__item">
                                                    <a href="{{ $image->temporaryUrl() }}" data-lightbox="image-{{ $index }}" data-title="{{ $image->getClientOriginalName() }}">
                                                        <img src="{{ $image->temporaryUrl() }}" alt="Uploaded Image" class="img-thumbnail">
                                                    </a>
                                                    <a>&nbsp</a>
                                                    <a>{{ $image->getClientOriginalName()}}</a>
                                                </li>
                                            </ul>
                                        </section>
                                    </div>
                                @endforeach
                            @endif
                            <div class="center mt-4 mb-4">
                                <label for="image_{{ $index }}" class="btn btn-blue col-md-3">
                                    <input wire:click="saveUploadImageIndex({{ $index }})" id="image_{{ $index }}" type="file" accept="{{ implode(',', config('uploadfile.accept_file_extension')) }}"  wire:model="image" style="display: none;">新增圖片
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="center mt-4 mb-4">
                <button type="submit" class="btn btn-blue col-md-3" wire:loading wire:target="image" disabled>
                    <a>上傳中 ... </a>
                </button>
                <button type="submit" class="btn btn-blue col-md-3" wire:loading.remove wire:target="image">
                    <a>修改欄位</a>
                </button>
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
            @if (session()->has('updateReportMsgSuccess'))
                <div class="alert alert-success">
                    {{ session('updateReportMsgSuccess') }}
                </div>
            @endif
            @if (session()->has('updateReportMsgFail'))
                <div class="alert alert-danger">
                    {{ session('updateReportMsgFail') }}
                </div>
            @endif
        </form>
    </div>
</div>
