<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptExecution extends Model
{
    protected $fillable = [
        'script_id',
        'router_id',
        'status',
        'output',
        'error',
        'execution_time',
    ];

    public function script()
    {
        return $this->belongsTo(Script::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}