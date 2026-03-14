@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Router Scripts</div>
        <div class="page-subtitle font-mono">Live on {{ $router->name }} · {{ $router->ip_address }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <form action="{{ route('router-scripts.import-all', $router) }}" method="POST">
            @csrf
            <button type="submit"
                    onclick="return confirm('Import all scripts into app DB?')"
                    class="btn btn-primary">
                ↓ Import All
            </button>
        </form>
        <a href="{{ route('routers.show', $router) }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

@if(empty($routerScripts))
    <div class="card" style="padding: 48px; text-align: center; color: var(--text-muted);">
        <div class="font-mono" style="font-size: 13px;">No scripts found on this router</div>
    </div>
@else
<div class="card" style="margin-bottom: 20px;">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Comment</th>
                <th>In App DB</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($routerScripts as $script)
            <tr>
                <td class="font-mono" style="color: var(--accent); font-size: 12px;">{{ $script['name'] }}</td>
                <td style="color: var(--text-muted); font-size: 12px;">{{ $script['comment'] ?? '—' }}</td>
                <td>
                    @if(in_array($script['name'], $appScriptNames))
                        <span class="badge badge-success">✓ Synced</span>
                    @else
                        <span class="badge badge-warning">Not imported</span>
                    @endif
                </td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <form action="{{ route('router-scripts.import', $router) }}" method="POST">
                            @csrf
                            <input type="hidden" name="name" value="{{ $script['name'] }}">
                            <button type="submit" class="btn btn-ghost" style="padding: 5px 10px; font-size: 11px;">
                                ↓ Import
                            </button>
                        </form>
                        <a href="{{ route('router-scripts.compare', ['name' => $script['name']]) }}"
                           class="btn btn-ghost" style="padding: 5px 10px; font-size: 11px;">
                            ⇄ Compare
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- Push app script to router -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Push App Script to This Router</span>
    </div>
    <div style="padding: 20px;">
        <form action="{{ route('router-scripts.push', $router) }}" method="POST"
              style="display: flex; gap: 10px;">
            @csrf
            <select name="script_id" class="input" style="flex: 1;">
                <option value="">Select a script from app DB...</option>
                @foreach(\App\Models\Script::orderBy('name')->get() as $appScript)
                    <option value="{{ $appScript->id }}">{{ $appScript->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">↑ Push to Router</button>
        </form>
    </div>
</div>
@endsection