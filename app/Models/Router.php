<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Router extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'ip_address',
        'api_port',
        'rest_port',
        'username',
        'password',
        'group',
        'description',
        'is_active',
        'last_seen',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
        'api_port'  => 'integer',
    ];

    // Scope to get only active routers
    // Usage: Router::active()->get()
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function commandLogs()
    {
        return $this->hasMany(CommandLog::class);
    }

    public function scriptExecutions()
    {
        return $this->hasMany(ScriptExecution::class);
    }
}