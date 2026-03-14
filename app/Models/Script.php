<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Script extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'content',
    ];

    public function executions()
    {
        return $this->hasMany(ScriptExecution::class);
    }
}