<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSubCategory extends Model
{
    protected $table = 'project_sub_category';
    protected $fillable = [
        'project_category_id',
        'sub_category',
        'user_id',
    ];
    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }
}
