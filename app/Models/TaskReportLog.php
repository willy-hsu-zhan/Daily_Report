<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskReportLog extends Model
{
    use SoftDeletes;
    const PROGRESS_TYPE  = ['進行中', '已完成' ,'Delay'];
    const PROGRESS_IP    = 0; // 進行中
    const PROGRESS_COMP  = 1; // 已完成
    const PROGRESS_DELAY = 2; // DELAY
    protected $table = 'task_report_log';
    protected $fillable = [
        'user_id',
        'project_category_id',
        'use_time',
        'description',
        'progress',
        'type',
        'report_date',
    ];

    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function projectSubCategory()
    {
        return $this->belongsTo(ProjectSubCategory::class, 'project_category_id');
    }
}
