<div class="warp container">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>

    <div class="header">
        <div class=" mb-4 flexcenter">
            <div class="col-md-8">
                <h1 class="distribute">{{ $projectName }}報表</h1>
                <div class="title-content">個人每日填寫的工作日誌在這裡以圖表方式呈現<br>點擊圖表內容以查看詳細資料</div>
            </div>
        </div>
    </div>
    <div class="card mb-4 content">
        <div class="flexcenter mb-3" bis_skin_checked="1">
            @if($selected_category_switch == 'personal')
                <a class="data-link-hold mx-2" wire:click="changeCategoryType('personal')">個人類別</a>
                <a class="data-link-large mx-2" wire:click="changeCategoryType('task')">工作類別</a>
            @elseif ($selected_category_switch == 'task')
                <a class="data-link-large mx-2" wire:click="changeCategoryType('personal')">個人類別</a>
                <a class="data-link-hold mx-2" wire:click="changeCategoryType('task')">工作類別</a>
            @endif
        </div>
        <div class="" bis_skin_checked="1">
            <div class="carousel-item active">
                <div id="custom-background">
                    <div class="center">
                        <div class="col-md-6" bis_skin_checked="1">
                            <div class="bg-color-1" bis_skin_checked="1">昨天的資料</div>
                            <div style="height: 350px" id="yesterday-piechart"></div>
                        </div>
                        <div class="col-md-6" bis_skin_checked="1">
                            <div class="bg-color-1" bis_skin_checked="1">上星期的資料</div>
                            <div style="height: 350px" id="lastWeek-piechart"></div>
                        </div>
                        <div class="col-md-6 mt-3" bis_skin_checked="1">
                            <div class="bg-color-1" bis_skin_checked="1">上個月的資料</div>
                            <div style="height: 350px" id="lastMonth-piechart"></div>
                        </div>
                        <div class="col-md-6 mt-3" bis_skin_checked="1">
                            <div class="bg-color-1" bis_skin_checked="1">所有歷史資料</div>
                            <div style="height: 350px" id="all-piechart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-log" wire:model="type">
            @if (!empty($type))
                <div class="flexcenter">
                    <h1>{{ $userName }}</h1>
                </div>
                <div>
                    <table class="table">
                        <thead class="thead-pink">
                            <tr>
                                <th>日期</th>
                                <th>USER</th>
                                <th>花費時間</th>
                                <th>工作內容</th>
                                <th>進度</th>
                                <th>工作類別</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ date('Y-m-d', $log->report_date) }}</td>
                                    <td>{{ $log->user?->name }}</td>
                                    <td>{{ $log->use_time }}</td>
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
                                                                        <img src="{{ $image->s3_image_path }}" alt="Uploaded Image" class="img-thumbnail">
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
                                    <td>{{ $log->progress }}</td>
                                    <td>{{ $log?->type }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="flexcenter">
                        <div style="display: flex;">
                            @if ($currentPage > 1)
                                <button class="btn btn-pink" wire:click="previousPage">
                                    <a class="pagination-link" href="#" aria-label="previous">&laquo; 上一頁</a>
                                </button>
                            @else
                                <button class="btn btn-pink" disabled>上一頁</button>
                            @endif
                    
                            <div class="mx-5 flexcenter">第{{ $currentPage }}/{{ $this->maxPage() }}頁</div>
                    
                            @if ($currentPage < $this->maxPage())
                                <button class="btn btn-pink" wire:click="nextPage">
                                    <a class="pagination-link" href="#" aria-label="next">下一頁 &raquo;</a>
                                </button>
                            @else
                                <button class="btn btn-pink" disabled>下一頁</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>