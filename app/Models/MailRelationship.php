<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailRelationship extends Model
{
    protected $table = 'mail_relationships';
    
    protected $fillable = [
        'user_id',
        'relation_user_id',
    ];
}
