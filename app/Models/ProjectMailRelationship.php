<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMailRelationship extends Model
{
    protected $table = 'project_mail_relationships';

    protected $fillable = [
        'user_id',
        'project_category_id',
        'active',
    ];

    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }
}
