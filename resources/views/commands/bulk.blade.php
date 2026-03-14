@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Bulk Commands</div>
        <div class="page-subtitle">Execute a command across multiple routers at once</div>
    </div>
</div>

<div class="card" style="max-width: 680px;">
    <div class="card-header">
        <span class="card-title">Command</span>
    </div>
    <div style="padding: 24px;">
        <form action="{{ route('commands.bulk.execute') }}" method="POST">
            @csrf

            <div style="margin-bottom: 20px;">
                <label class="label">RouterOS Command</label>
                <input type="text" name="command"
                       class="input input-mono"
                       placeholder="/system/resource/print">
            </div>

            <div style="margin-bottom: 20px;">
                <label class="label">Select Routers</label>
                <div style="border: 1px solid var(--border); border-radius: 8px; max-height: 240px; overflow-y: auto;">
                    @foreach($routers as $router)
                    <label style="display: flex; align-items: center; gap: 12px; padding: 10px 14px;
                                  border-bottom: 1px solid var(--border); cursor: pointer;
                                  transition: background 0.1s;"
                           onmouseover="this.style.background='var(--bg-card-hover)'"
                           onmouseout="this.style.background='transparent'">
                        <input type="checkbox" name="router_ids[]" value="{{ $router->id }}">
                        <span style="font-size: 13px;">
                            <span style="font-weight: 600;">{{ $router->name }}</span>
                            <span class="font-mono" style="color: var(--accent); font-size: 11px; margin-left: 10px;">{{ $router->ip_address }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                ▶ Execute on Selected Routers
            </button>
        </form>
    </div>
</div>
@endsection