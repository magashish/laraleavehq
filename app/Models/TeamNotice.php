<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamNotice extends Model
{
    protected $fillable = ['created_by', 'target_user_id', 'message'];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
