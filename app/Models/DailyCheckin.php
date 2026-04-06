<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyCheckin extends Model
{
    protected $fillable = ['user_id', 'date', 'checked_in_at', 'signed_out_at'];

    protected $casts = [
        'date'          => 'date',
        'checked_in_at' => 'datetime',
        'signed_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
