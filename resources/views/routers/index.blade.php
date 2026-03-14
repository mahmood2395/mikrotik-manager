@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Routers</div>
        <div class="page-subtitle">{{ $routers->count() }} device(s) registered</div>
    </div>
    <a href="{{ route('routers.create') }}" class="btn btn-primary">
        + Add Router
    </a>
</div>

@if($routers->isEmpty())
    <div class="card" style="padding: 48px; text-align: center; color: var(--text-muted);">
        <div style="font-size: 32px; margin-bottom: 12px;">⬡</div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 13px;">No routers registered yet</div>
        <a href="{{ route('routers.create') }}" class="btn btn-primary" style="margin-top: 16px; display: inline-flex;">
            Add your first router
        </a>
    </div>
@else
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>IP Address</th>
                <th>Group</th>
                <th>Status</th>
                <th>Last Seen</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($routers as $router)
            <tr>
                <td style="font-weight: 600;">{{ $router->name }}</td>
                <td>
                    <span class="font-mono" style="color: var(--accent); font-size: 12px;">
                        {{ $router->ip_address }}:{{ $router->api_port }}
                    </span>
                </td>
                <td>
                    @if($router->group)
                        <span class="badge badge-blue">{{ $router->group }}</span>
                    @else
                        <span style="color: var(--text-muted);">—</span>
                    @endif
                </td>
                <td>
                    @if($router->is_active)
                        <span class="badge badge-success">● Active</span>
                    @else
                        <span class="badge badge-danger">● Inactive</span>
                    @endif
                </td>
                <td style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted);">
                    {{ $router->last_seen ? $router->last_seen->diffForHumans() : 'Never' }}
                </td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('routers.show', $router) }}" class="btn btn-ghost" style="padding: 5px 10px; font-size: 11px;">View</a>
                        <a href="{{ route('routers.edit', $router) }}" class="btn btn-ghost" style="padding: 5px 10px; font-size: 11px;">Edit</a>
                        <form action="{{ route('routers.destroy', $router) }}" method="POST"
                              onsubmit="return confirm('Delete this router?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 11px;">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection