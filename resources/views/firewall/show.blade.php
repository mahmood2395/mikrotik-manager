@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Firewall</div>
        <div class="page-subtitle font-mono">{{ $router->name }} · {{ $router->ip_address }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="fetchRules()" class="btn btn-ghost">↻ Refresh</button>
        <a href="{{ route('routers.show', $router) }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 4px; margin-bottom: 20px; background: var(--bg-card);
            border: 1px solid var(--border); border-radius: 10px; padding: 4px; width: fit-content;">
    @foreach(['filter' => 'Filter', 'nat' => 'NAT', 'mangle' => 'Mangle'] as $key => $label)
    <button onclick="switchTab('{{ $key }}')"
            id="tab-{{ $key }}"
            style="padding: 7px 18px; border-radius: 7px; font-size: 12px; font-weight: 600;
                   border: none; cursor: pointer; transition: all 0.15s; font-family: 'Sora', sans-serif;
                   background: {{ $tab === $key ? 'var(--accent)' : 'transparent' }};
                   color: {{ $tab === $key ? 'var(--bg-base)' : 'var(--text-muted)' }};">
        {{ $label }}
    </button>
    @endforeach
</div>

<!-- Add rule -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <span class="card-title">Add Filter Rule</span>
    </div>
    <div style="padding: 20px;">
        <form action="{{ route('firewall.store', $router) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 12px;">
                <div>
                    <label class="label">Chain</label>
                    <select name="chain" class="input">
                        <option>input</option>
                        <option>forward</option>
                        <option>output</option>
                    </select>
                </div>
                <div>
                    <label class="label">Action</label>
                    <select name="action" class="input">
                        <option>accept</option>
                        <option>drop</option>
                        <option>reject</option>
                        <option>log</option>
                    </select>
                </div>
                <div>
                    <label class="label">Src Address</label>
                    <input type="text" name="src_address" class="input input-mono" placeholder="0.0.0.0/0">
                </div>
                <div>
                    <label class="label">Dst Address</label>
                    <input type="text" name="dst_address" class="input input-mono" placeholder="0.0.0.0/0">
                </div>
                <div>
                    <label class="label">Protocol</label>
                    <select name="protocol" class="input">
                        <option value="">any</option>
                        <option>tcp</option>
                        <option>udp</option>
                        <option>icmp</option>
                    </select>
                </div>
                <div>
                    <label class="label">Dst Port</label>
                    <input type="text" name="dst_port" class="input input-mono" placeholder="80,443">
                </div>
                <div>
                    <label class="label">Comment</label>
                    <input type="text" name="comment" class="input" placeholder="Optional">
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        + Add Rule
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Rules table -->
<div class="card">
    <div id="fw-loading" style="padding: 32px; text-align: center; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; font-size: 12px;">
        Loading rules...
    </div>
    <div id="fw-error" style="display: none; padding: 24px; text-align: center; color: var(--danger); font-family: 'JetBrains Mono', monospace; font-size: 12px;"></div>
    <table id="fw-table" style="display: none;">
        <thead>
            <tr>
                <th>#</th>
                <th>Chain</th>
                <th>Action</th>
                <th>Src Address</th>
                <th>Dst Address</th>
                <th>Protocol</th>
                <th>Comment</th>
                <th>Toggle</th>
            </tr>
        </thead>
        <tbody id="fw-body"></tbody>
    </table>
</div>

<script>
    let currentTab = '{{ $tab }}';
    const baseUrl   = '/routers/{{ $router->id }}/firewall';
    const toggleUrl = '{{ route("firewall.toggle", $router) }}';

    function actionColor(action) {
        const map = {
            accept:     'badge-success',
            drop:       'badge-danger',
            reject:     'badge-warning',
            log:        'badge-blue',
            masquerade: 'badge-blue',
        };
        return map[action] || 'badge-muted';
    }

    async function toggleRule(id, chain, disabled) {
        const res  = await fetch(toggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id, chain, disable: !disabled })
        });
        const json = await res.json();
        if (json.success) fetchRules();
        else alert('Error: ' + json.error);
    }

    async function fetchRules() {
        document.getElementById('fw-loading').style.display = 'block';
        document.getElementById('fw-table').style.display   = 'none';
        document.getElementById('fw-error').style.display   = 'none';

        try {
            const res  = await fetch(`${baseUrl}/${currentTab}/data`);
            const json = await res.json();

            if (!json.success) {
                document.getElementById('fw-loading').style.display = 'none';
                document.getElementById('fw-error').style.display   = 'block';
                document.getElementById('fw-error').textContent     = json.error;
                return;
            }

            document.getElementById('fw-body').innerHTML = json.rules.map((rule, i) => `
                <tr style="${rule.disabled === 'true' ? 'opacity: 0.4;' : ''}">
                    <td class="font-mono" style="font-size: 11px; color: var(--text-muted);">${i}</td>
                    <td class="font-mono" style="font-size: 12px;">${rule.chain || '—'}</td>
                    <td><span class="badge ${actionColor(rule.action)}">${rule.action || '—'}</span></td>
                    <td class="font-mono" style="font-size: 11px; color: var(--text-muted);">${rule['src-address'] || 'any'}</td>
                    <td class="font-mono" style="font-size: 11px; color: var(--text-muted);">${rule['dst-address'] || 'any'}</td>
                    <td style="font-size: 12px; color: var(--text-muted);">${rule.protocol || 'any'}</td>
                    <td style="font-size: 11px; color: var(--text-muted); font-style: italic;">${rule.comment || ''}</td>
                    <td>
                        <button onclick="toggleRule('${rule['.id']}', '${currentTab}', ${rule.disabled === 'true'})"
                                class="btn ${rule.disabled === 'true' ? 'btn-ghost' : 'btn-danger'}"
                                style="padding: 4px 10px; font-size: 11px;">
                            ${rule.disabled === 'true' ? 'Enable' : 'Disable'}
                        </button>
                    </td>
                </tr>
            `).join('') || `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);font-size:12px;font-family:'JetBrains Mono',monospace;">No rules found</td></tr>`;

            document.getElementById('fw-loading').style.display = 'none';
            document.getElementById('fw-table').style.display   = 'table';

        } catch(e) {
            document.getElementById('fw-loading').style.display = 'none';
            document.getElementById('fw-error').style.display   = 'block';
            document.getElementById('fw-error').textContent     = e.message;
        }
    }

    function switchTab(tab) {
        currentTab = tab;
        ['filter', 'nat', 'mangle'].forEach(t => {
            const el = document.getElementById('tab-' + t);
            if (t === tab) {
                el.style.background = 'var(--accent)';
                el.style.color      = 'var(--bg-base)';
            } else {
                el.style.background = 'transparent';
                el.style.color      = 'var(--text-muted)';
            }
        });
        fetchRules();
    }

    fetchRules();
</script>
@endsection