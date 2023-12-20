<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCategory extends Model
{
    protected $table = 'project_category';

    protected $fillable = [
        'parent',
        'category',
        'user_id',
    ];

    public function projectMailRelationships()
    {
        return $this->hasMany(ProjectMailRelationships::class, 'project_category_id');
    }

    public function projectSubCategory()
    {
        return $this->hasMany(ProjectSubCategory::class, 'project_category_id');
    }

    public function taskReport()
    {
        return $this->hasMany(TaskReport::class, 'project_category_id');
    }
}
