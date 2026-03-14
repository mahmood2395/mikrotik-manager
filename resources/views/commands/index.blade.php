@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Terminal</div>
        <div class="page-subtitle font-mono">{{ $router->name }} · {{ $router->ip_address }}</div>
    </div>
    <a href="{{ route('routers.show', $router) }}" class="btn btn-ghost">← Back</a>
</div>

<!-- Command input -->
<div class="card" style="margin-bottom: 20px;">
    <div style="padding: 20px;">
        <form action="{{ route('commands.execute', $router) }}" method="POST">
            @csrf
            <label class="label">RouterOS Command</label>
            <div style="display: flex; gap: 10px;">
                <input type="text" name="command"
                       class="input input-mono"
                       placeholder="/ip/address/print"
                       value="{{ old('command') }}"
                       autofocus>
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">▶ Run</button>
            </div>
        </form>

        <!-- Quick commands -->
        <div style="margin-top: 14px;">
            <div class="label" style="margin-bottom: 8px;">Quick Commands</div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                @foreach([
                    '/system/resource/print',
                    '/interface/print',
                    '/ip/address/print',
                    '/ip/route/print',
                    '/ip/firewall/filter/print',
                    '/system/identity/print',
                ] as $quick)
                <button onclick="document.querySelector('[name=command]').value='{{ $quick }}'"
                        style="background: var(--bg-base); border: 1px solid var(--border); color: var(--text-muted);
                               border-radius: 6px; padding: 4px 10px; font-size: 11px; font-family: 'JetBrains Mono', monospace;
                               cursor: pointer; transition: all 0.15s;"
                        onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                        onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
                    {{ $quick }}
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Results -->
<div style="space-y: 12px;">
    @forelse($logs as $log)
    <div class="card" style="margin-bottom: 12px;">
        <div class="card-header">
            <span class="font-mono" style="font-size: 12px; color: var(--accent);">{{ $log->command }}</span>
            <div style="display: flex; align-items: center; gap: 12px;">
                @if($log->status === 'success')
                    <span class="badge badge-success">{{ $log->execution_time }}ms</span>
                @else
                    <span class="badge badge-danger">Failed</span>
                @endif
                <span style="font-size: 11px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted);">
                    {{ $log->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
        <div style="padding: 16px; overflow-x: auto;">
            @if($log->status === 'failed')
                <p style="color: var(--danger); font-family: 'JetBrains Mono', monospace; font-size: 12px;">{{ $log->error }}</p>
            @elseif(empty($log->response))
                <p style="color: var(--text-muted); font-size: 12px; font-style: italic;">No output</p>
            @else
                <table>
                    <thead>
                        <tr>
                            @foreach(array_keys($log->response[0] ?? []) as $key)
                                <th>{{ $key }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($log->response as $row)
                        <tr>
                            @foreach($row as $value)
                                <td class="font-mono" style="font-size: 12px;">{{ $value }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @empty
        <div class="card" style="padding: 48px; text-align: center; color: var(--text-muted);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 13px;">
                No commands run yet — try one above
            </div>
        </div>
    @endforelse
</div>
@endsection