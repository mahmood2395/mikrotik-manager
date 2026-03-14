@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $router->name }}</div>
        <div class="page-subtitle font-mono">{{ $router->ip_address }} · {{ $router->group ?? 'no group' }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <form action="{{ route('routers.test', $router) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">⚡ Test Connection</button>
        </form>
        <a href="{{ route('monitoring.show', $router) }}" class="btn btn-ghost">◎ Monitor</a>
        <a href="{{ route('commands.index', $router) }}" class="btn btn-ghost">❯ Terminal</a>
        <a href="{{ route('router-scripts.index', $router) }}" class="btn btn-ghost">⌗ Scripts</a>
        <a href="{{ route('firewall.show', $router) }}" class="btn btn-ghost">🛡 Firewall</a>
        <a href="{{ route('routers.edit', $router) }}" class="btn btn-ghost">✎ Edit</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Connection Details</span>
            @if($router->is_active)
                <span class="badge badge-success">● Active</span>
            @else
                <span class="badge badge-danger">● Inactive</span>
            @endif
        </div>
        <div style="padding: 20px;">
            @foreach([
                'IP Address'   => $router->ip_address,
                'API Port'     => $router->api_port,
                'REST Port'    => $router->rest_port ?? 80,
                'Username'     => $router->username,
                'Group'        => $router->group ?? '—',
                'Last Seen'    => $router->last_seen ? $router->last_seen->diffForHumans() : 'Never',
            ] as $label => $value)
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border);">
                <span style="font-size: 12px; color: var(--text-muted);">{{ $label }}</span>
                <span style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--accent);">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Description</span>
        </div>
        <div style="padding: 20px; font-size: 13px; color: var(--text-muted); line-height: 1.6;">
            {{ $router->description ?? 'No description provided.' }}
        </div>
    </div>
</div>
@endsection