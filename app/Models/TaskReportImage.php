<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskReportImage extends Model
{
    use SoftDeletes;
    protected $table = 'task_report_image';
    protected $fillable = [
        'task_report_id',
        's3_image_path'
    ];

    public function taskReport()
    {
        return $this->belongsTo(TaskReport::class, 'task_report_id');
    }

    public function getFileName()
    {
        return pathinfo($this->s3_image_path)['filename'];
    }

    public function getFileNameWithExtension()
    {
        return pathinfo($this->s3_image_path)['basename'];
    }

}
