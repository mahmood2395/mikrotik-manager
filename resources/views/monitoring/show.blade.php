@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $router->name }}</div>
        <div class="page-subtitle font-mono">
            <span class="live-dot"></span>
            <span style="margin-left: 6px;">{{ $router->ip_address }} · live monitoring</span>
        </div>
    </div>
    <div style="display: flex; gap: 8px; align-items: center;">
        <span id="last-updated" style="font-size: 11px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted);"></span>
        <button onclick="fetchData()" class="btn btn-ghost">↻ Refresh</button>
        <a href="{{ route('routers.show', $router) }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

<!-- Loading -->
<div id="loading" style="text-align: center; padding: 64px; color: var(--text-muted);">
    <div style="font-family: 'JetBrains Mono', monospace; font-size: 13px;">
        Connecting to {{ $router->ip_address }}...
    </div>
</div>

<!-- Error -->
<div id="error" style="display: none;">
    <div class="card" style="padding: 32px; text-align: center; border-color: var(--danger);">
        <div style="color: var(--danger); font-family: 'JetBrains Mono', monospace; font-size: 13px;"
             id="error-message"></div>
    </div>
</div>

<!-- Dashboard -->
<div id="dashboard" style="display: none;">

    <!-- Resource cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px;">

        <!-- CPU -->
        <div class="card" style="padding: 20px;">
            <div style="font-size: 10px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">CPU Load</div>
            <div id="cpu-load" style="font-size: 32px; font-weight: 700; color: var(--accent); font-family: 'JetBrains Mono', monospace; margin-bottom: 10px;">—</div>
            <div class="progress">
                <div id="cpu-bar" class="progress-bar" style="width: 0%; background: var(--accent);"></div>
            </div>
        </div>

        <!-- Memory -->
        <div class="card" style="padding: 20px;">
            <div style="font-size: 10px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Memory</div>
            <div id="memory-load" style="font-size: 32px; font-weight: 700; color: #60a5fa; font-family: 'JetBrains Mono', monospace; margin-bottom: 10px;">—</div>
            <div class="progress">
                <div id="memory-bar" class="progress-bar" style="width: 0%; background: #60a5fa;"></div>
            </div>
        </div>

        <!-- Uptime -->
        <div class="card" style="padding: 20px;">
            <div style="font-size: 10px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Uptime</div>
            <div id="uptime" style="font-size: 18px; font-weight: 700; color: var(--text); font-family: 'JetBrains Mono', monospace; margin-top: 6px;">—</div>
        </div>

        <!-- Version -->
        <div class="card" style="padding: 20px;">
            <div style="font-size: 10px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">RouterOS</div>
            <div id="version" style="font-size: 18px; font-weight: 700; color: var(--text); font-family: 'JetBrains Mono', monospace; margin-top: 6px;">—</div>
        </div>

    </div>

    <!-- Interfaces -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <span class="card-title">Interfaces</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>MAC Address</th>
                    <th>MTU</th>
                    <th>RX / TX</th>
                </tr>
            </thead>
            <tbody id="interfaces-table">
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px;font-family:'JetBrains Mono',monospace;font-size:12px;">Loading...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- IP Addresses -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">IP Addresses</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Address</th>
                    <th>Interface</th>
                    <th>Network</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="addresses-table">
                <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px;font-family:'JetBrains Mono',monospace;font-size:12px;">Loading...</td></tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    const dataUrl = "{{ route('monitoring.data', $router) }}";

    function formatBytes(bytes) {
        if (!bytes || bytes == 0) return '0 B';
        bytes = parseInt(bytes);
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
    }

    function statusBadge(running, disabled) {
        if (disabled === 'true') return '<span class="badge badge-muted">Disabled</span>';
        if (running === 'true') return '<span class="badge badge-success">● Running</span>';
        return '<span class="badge badge-danger">● Down</span>';
    }

    async function fetchData() {
        try {
            const res  = await fetch(dataUrl);
            const json = await res.json();

            if (!json.success) { showError(json.error); return; }

            document.getElementById('loading').style.display   = 'none';
            document.getElementById('error').style.display     = 'none';
            document.getElementById('dashboard').style.display = 'block';

            const r        = json.data.resources;
            const totalMem = parseInt(r['total-memory'] || 0);
            const freeMem  = parseInt(r['free-memory'] || 0);
            const memPct   = totalMem ? Math.round(((totalMem - freeMem) / totalMem) * 100) : 0;
            const cpuLoad  = parseInt(r['cpu-load'] || 0);

            document.getElementById('cpu-load').textContent    = cpuLoad + '%';
            document.getElementById('memory-load').textContent = memPct + '%';
            document.getElementById('uptime').textContent      = r['uptime'] || '—';
            document.getElementById('version').textContent     = r['version'] || '—';

            const cpuBar = document.getElementById('cpu-bar');
            cpuBar.style.width      = cpuLoad + '%';
            cpuBar.style.background = cpuLoad > 80 ? 'var(--danger)' :
                                      cpuLoad > 50 ? 'var(--warning)' : 'var(--accent)';

            document.getElementById('memory-bar').style.width = memPct + '%';

            // Interfaces
            document.getElementById('interfaces-table').innerHTML =
                json.data.interfaces.map(i => `
                    <tr style="${i.disabled === 'true' ? 'opacity: 0.4;' : ''}">
                        <td class="font-mono" style="color: var(--accent); font-size: 12px;">${i.name || '—'}</td>
                        <td style="color: var(--text-muted); font-size: 12px;">${i.type || '—'}</td>
                        <td>${statusBadge(i.running, i.disabled)}</td>
                        <td class="font-mono" style="font-size: 11px; color: var(--text-muted);">${i['mac-address'] || '—'}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">${i.mtu || '—'}</td>
                        <td class="font-mono" style="font-size: 11px;">
                            <span style="color: var(--accent);">↓ ${formatBytes(i['rx-byte'])}</span>
                            <span style="color: #60a5fa; margin-left: 10px;">↑ ${formatBytes(i['tx-byte'])}</span>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:20px;font-size:12px;">No interfaces</td></tr>';

            // IP Addresses
            document.getElementById('addresses-table').innerHTML =
                json.data.addresses.map(a => `
                    <tr>
                        <td class="font-mono" style="color: var(--accent); font-size: 12px;">${a.address || '—'}</td>
                        <td class="font-mono" style="font-size: 12px; color: var(--text-muted);">${a.interface || '—'}</td>
                        <td class="font-mono" style="font-size: 12px; color: var(--text-muted);">${a.network || '—'}</td>
                        <td>${a.disabled === 'true'
                            ? '<span class="badge badge-muted">Disabled</span>'
                            : '<span class="badge badge-success">Active</span>'}</td>
                    </tr>
                `).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:20px;font-size:12px;">No addresses</td></tr>';

            document.getElementById('last-updated').textContent =
                'updated ' + new Date().toLocaleTimeString();

        } catch(e) { showError(e.message); }
    }

    function showError(msg) {
        document.getElementById('loading').style.display   = 'none';
        document.getElementById('dashboard').style.display = 'none';
        document.getElementById('error').style.display     = 'block';
        document.getElementById('error-message').textContent = '✕ ' + msg;
    }

    fetchData();
    setInterval(fetchData, 30000);
</script>
@endsection