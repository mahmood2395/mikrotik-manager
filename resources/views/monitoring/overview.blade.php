@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Network Overview</div>
        <div class="page-subtitle">
            <span class="live-dot"></span>
            <span style="margin-left: 6px;">Live — updates every 60s</span>
        </div>
    </div>
    <button onclick="refreshAll()" class="btn btn-ghost">↻ Refresh</button>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;"
     id="routers-grid">
    @foreach($routers as $router)
    <div class="card" id="router-card-{{ $router->id }}">
        <div class="card-header">
            <div>
                <div style="font-weight: 600; font-size: 14px;">{{ $router->name }}</div>
                <div class="font-mono" style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                    {{ $router->ip_address }}
                </div>
            </div>
            <span id="status-{{ $router->id }}" class="badge badge-muted">Checking...</span>
        </div>

        <div style="padding: 16px; space-y: 10px;">
            <div style="margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-size: 11px; color: var(--text-muted);">CPU</span>
                    <span id="cpu-{{ $router->id }}" class="font-mono" style="font-size: 11px; color: var(--accent);">—</span>
                </div>
                <div class="progress">
                    <div id="cpu-bar-{{ $router->id }}" class="progress-bar" style="width: 0%; background: var(--accent);"></div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-size: 11px; color: var(--text-muted);">Memory</span>
                    <span id="mem-{{ $router->id }}" class="font-mono" style="font-size: 11px; color: #60a5fa;">—</span>
                </div>
                <div class="progress">
                    <div id="mem-bar-{{ $router->id }}" class="progress-bar" style="width: 0%; background: #60a5fa;"></div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between;">
                <span style="font-size: 11px; color: var(--text-muted);">Uptime</span>
                <span id="uptime-{{ $router->id }}" class="font-mono" style="font-size: 11px; color: var(--text-muted);">—</span>
            </div>
        </div>

        <div style="padding: 12px 16px; border-top: 1px solid var(--border);">
            <a href="{{ route('monitoring.show', $router) }}"
               style="display: block; text-align: center; font-size: 11px; font-family: 'JetBrains Mono', monospace;
                      color: var(--text-muted); text-decoration: none; transition: color 0.15s;"
               onmouseover="this.style.color='var(--accent)'"
               onmouseout="this.style.color='var(--text-muted)'">
                View Details →
            </a>
        </div>
    </div>
    @endforeach
</div>

<script>
    const allStatusUrl = "{{ route('monitoring.all-status') }}";

    async function refreshAll() {
        const res  = await fetch(allStatusUrl);
        const data = await res.json();

        for (const [routerId, info] of Object.entries(data)) {
            const statusEl = document.getElementById('status-' + routerId);
            if (!info.online) {
                statusEl.textContent  = '● Offline';
                statusEl.className    = 'badge badge-danger';
                continue;
            }

            statusEl.textContent = '● Online';
            statusEl.className   = 'badge badge-success';

            document.getElementById('cpu-' + routerId).textContent     = info.cpu + '%';
            document.getElementById('cpu-bar-' + routerId).style.width = info.cpu + '%';
            document.getElementById('mem-' + routerId).textContent     = info.memory + '%';
            document.getElementById('mem-bar-' + routerId).style.width = info.memory + '%';
            document.getElementById('uptime-' + routerId).textContent  = info.uptime;

            // Color CPU bar based on load
            const cpuBar = document.getElementById('cpu-bar-' + routerId);
            cpuBar.style.background = info.cpu > 80 ? 'var(--danger)' :
                                      info.cpu > 50 ? 'var(--warning)' : 'var(--accent)';
        }
    }

    refreshAll();
    setInterval(refreshAll, 60000);
</script>
@endsection