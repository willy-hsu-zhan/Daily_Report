<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskReport extends Model
{
    use SoftDeletes;

    const PROGRESS_TYPE  = ['進行中', '已完成', 'Delay'];
    const PROGRESS_IP    = 0; // 進行中
    const PROGRESS_COMP  = 1; // 已完成
    const PROGRESS_DELAY = 2; // DELAY
    protected $table = 'task_report';
    protected $fillable = [
        'user_id',
        'project_category_id',
        'use_time',
        'description',
        'progress',
        'type',
        'report_date',
    ];

    public static function getFormatDescription(string $description) // insert to DB
    {
        $description = nl2br($description);

        $description = strpos($description, '<br>') !== false ? str_replace("<br />", "\n", $description): $description;

        return $description;
    }

    public static function getDisplayDescription(string $description) // display to html
    {
        return strip_tags($description);
    }

    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function projectSubCategory()
    {
        return $this->belongsTo(ProjectSubCategory::class, 'project_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(TaskReportImage::class, 'task_report_id');
    }
}
