<div class="container">
    <div class="header">
        <div class=" mb-4 flexcenter">
            <h1 class="distribute">個人REPORT紀錄</h1>
        </div>
    </div>
    <div class="mb-4">
        <div>
            @if (isset($log_datas))
                <table class="table">
                    <thead class="thead-pink">
                        <tr>
                            <th>專案名稱</th>
                            <th>工作類別</th>
                            <th>工作內容</th>
                            <th>花費時間</th>
                            <th>進度</th>
                            <th>日期</th>
                            <th>修改</th>
                            <th>刪除</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($log_datas as $index => $log)
                            <tr wire:key="log-{{ $log->id }}">
                                <td>{{ $log->projectCategory?->category }}</td>
                                <td>{{ $log->type }}</td>
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
                                                            <img class="garbage-icon pointer-icon" wire:click="removeImageAction({{ $image->task_report_image_id }})" src="{{ asset('images/garbage.png') }}" href="#" alt="Remove Icon">
                                                            <li class="gallery__item">
                                                                <a href="{{ $image->s3_image_path }}" data-lightbox="image-{{ $index }}" data-title="{{ basename($image->s3_image_path) }}">
                                                                    <img src="{{ $image->s3_image_path }}" alt="Uploaded Image">
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
                                <td>
                                    <a wire:click="updateCategoryLink({{ $log }})">
                                        <div class="btn-edit">修改</div>
                                    </a>
                                </td>
                                <td>
                                    <a wire:click="deleteForm({{ $log->id }})">
                                        <div type="submit" class="btn-pink">刪除</div>
                                    </a>
                                </td>
                            </tr>
                            
                        @endforeach
                    </tbody>
                    
                </table>
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
                {{-- {{ $log_datas->links('vendor.pagination.report-pagination') }} <!-- 自定義分頁連結 -->  使用內建laravel pagination livewire會ERROR--}}
            @endif
        </div>
    </div>
</div>
