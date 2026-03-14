<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandLog extends Model
{
    protected $fillable = [
        'router_id',
        'command',
        'response',
        'status',
        'error',
        'execution_time',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}