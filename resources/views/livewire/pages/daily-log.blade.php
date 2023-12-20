<div class="warp container">
    <a class="col-md-8"
        href='https://docs.google.com/spreadsheets/d/12D0cCXiaD-9L82hYbp5dfCvMLWZjZNDZLlhFLzZSdiw/edit#gid=1590681959'>
        Bug 回報
    </a>
    <div class="header">
        <div class=" mb-4 flexcenter">
            <div class="col-md-8">
                <h1 class="distribute">工作日誌</h1>
            </div>
            <div class="title-content">
                填寫工作日誌對每天工作情況的記錄做總結，以了解當日工作重點<br>請確實填寫每個欄位，以便掌握工作內容與進度<br>若需要填入多筆不同類別內容，可新增欄位填寫<br>如果請假的話也請在專案選擇"請假"並且填上其他資料送出
            </div>
        </div>
    </div>
    <form id="form" class="mb-2" wire:submit.prevent="submitForm">
        @foreach ($formData as $index => $data)
            <div wire:key="{{ $index }}">
                <div class="card">
                    <div>
                        <div class="mb-2" id="ceil_{{ $loop->index }}">
                            <div class="flex card-title text-center">
                                <a class="btn btn-close" href="javascript:void(0);" wire:click="removeField({{ $index }})">x</a>第{{ $loop->index + 1 }}項
                            </div>
                            <div class="content">
                                <div class="mx-4">
                                    <section class="mt-2 mb-2">
                                        <div class="col-md-7">
                                            <div class="input-group">
                                                <div class="input-group">
                                                    <span class="input-group-text">專案</span>
                                                    <select name="專案" id="0" class="input-group-btn" wire:model.debounce.0ms="formData.{{ $index }}.project" wire:change="handleProjectChange({{ $index }})">
                                                        <option value= -1>未選擇</option>
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
                                                    <select name="工作類別" id="0" class="input-group-btn" wire:model="formData.{{ $index }}.category">
                                                        <option value= -1>未選擇</option>
                                                        @if (!is_null($data['category']) && !is_string($data['category'])) {{-- 還沒選專案時 --}}
                                                            @foreach ($data['category'] as $key => $value)
                                                                <option value="{{ $key }}">{{ $key }}</option>
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
                                                    <select name="進度" id="0" class="input-group-btn" wire:model="formData.{{ $index }}.progress">
                                                        <option value= -1>未選擇</option>
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
                                            <input name="time" id="0" type="text" class="form-control" wire:model.debounce.0ms="formData.{{ $index }}.time">
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text">日期</span>
                                            <input name="date" id="0" type="text" class="form-control" wire:model="formData.{{ $index }}.date">
                                        </div>
                                    </section>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">工作內容</span>
                                        <textarea style="height: {{$data['textAreaHeight']}}px;" name="content" id="0" class="form-control" aria-label="With textarea" wire:model.debounce.0ms="formData.{{ $index }}.content"></textarea>
                                    </div>
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
                        @if (count($formData) == $loop->index + 1 ) {{-- 最後一筆 --}}
                            <div class="d-flex justify-content-center">
                                <div class="col-md-2 flexcenter mt-2 mb-2">
                                    <label for="image_{{ $index }}" class="btn btn-blue col-md-12">
                                        <input wire:click="saveUploadImageIndex({{ $index }})" id="image_{{ $index }}" type="file" accept="{{ implode(',', config('uploadfile.accept_file_extension')) }}" wire:model="image" style="display: none;">上傳圖片
                                    </label>
                                </div>
                                <div class="col-md-2 flexcenter mt-2 mb-2">
                                    <div class="btn btn-blue col-md-12" wire:click="addField">新增欄位</div>
                                </div>
                            </div>
                        @else
                            <div class="center mt-4 mb-4">
                                <label for="image_{{ $index }}" class="btn btn-blue col-md-3">
                                    <input wire:click="saveUploadImageIndex({{ $index }})" id="image_{{ $index }}" type="file" accept="{{ implode(',', config('uploadfile.accept_file_extension')) }}"  wire:model="image" style="display: none;">新增圖片
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <br>
        @endforeach
        <div class="center mt-4 mb-4">
            <button type="submit" class="btn btn-blue col-md-3" wire:loading wire:target="image" disabled>
                <a>上傳中 ... </a>
            </button>
            <button type="submit" class="btn btn-blue col-md-3" wire:loading.remove wire:target="image">
                <a>送出</a>
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
    <div class="header">
        <div class=" mb-4 flexcenter">
            <h1 class="distribute">昨天的工作日誌</h1>
        </div>
    </div>
    <div class="mb-4">
        <div>
            @if (isset($logs))
                <table class="table">
                    <thead class="thead-pink">
                        <tr>
                            <th>專案名稱</th>
                            <th>工作類別</th>
                            <th>工作內容</th>
                            <th>花費時間</th>
                            <th>進度</th>
                            <th>日期</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>{{ $log->projectCategory?->category }}</td>
                                <td>{{ $log?->type }}</td>
                                <td>
                                    <div class="parent-flex-container">
                                        <div class="flex-container">
                                            <p class="text-left">{!! nl2br($log->description) !!}</p>
                                        </div>
                                        @if($log->images)
                                            <div class="flex-container">
                                                @foreach($log->images as $image_index => $image)
                                                    <section class="gallery">
                                                        <ul class="gallery__list">
                                                            <li class="gallery__item">
                                                                <a href="{{ $image->s3_image_path }}" data-lightbox="image-{{ $image_index }}" data-title="{{ basename($image->s3_image_path) }}">
                                                                    <img style="max-width: 200px; max-height: 200px;" src="{{ $image->s3_image_path }}" alt="Uploaded Image">
                                                                </a>
                                                                <a>&nbsp</a>
                                                                <a>
                                                                    {{ \Illuminate\Support\Str::limit(pathinfo($image->s3_image_path)['filename'], 20, $end='... ') . '.' . pathinfo($image->s3_image_path)['extension'] }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </section>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $log->use_time }}</td>
                                <td>{{ $log->progress }}</td>
                                <td>{{ date('Y-m-d', $log->report_date) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flexcenter">
                    <div style="display: flex;"><button class="btn btn-pink" disabled="">上一頁</button>
                        <div class="mx-5 flexcenter">第1/1頁</div>
                        <button class="btn btn-pink" disabled="false">下一頁</button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
