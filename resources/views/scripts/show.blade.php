@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $script->name }}</div>
        <div class="page-subtitle">
            @if($script->category)
                <span class="badge badge-blue">{{ $script->category }}</span>
            @endif
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('scripts.edit', $script) }}" class="btn btn-ghost">✎ Edit</a>
        <a href="{{ route('scripts.index') }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- Script content -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Script Content</span>
            </div>
            <div style="padding: 20px; background: var(--bg-base); border-radius: 0 0 12px 12px;">
                <pre style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--accent);
                            line-height: 1.6; margin: 0; overflow-x: auto; white-space: pre-wrap;">{{ $script->content }}</pre>
            </div>
        </div>

        <!-- Execute -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Execute on Routers</span>
            </div>
            <div style="padding: 20px;">
                <form action="{{ route('scripts.execute', $script) }}" method="POST">
                    @csrf
                    <div style="border: 1px solid var(--border); border-radius: 8px;
                                max-height: 200px; overflow-y: auto; margin-bottom: 16px;">
                        @foreach($routers as $router)
                        <label style="display: flex; align-items: center; gap: 12px; padding: 10px 14px;
                                      border-bottom: 1px solid var(--border); cursor: pointer;
                                      transition: background 0.1s;"
                               onmouseover="this.style.background='var(--bg-card-hover)'"
                               onmouseout="this.style.background='transparent'">
                            <input type="checkbox" name="router_ids[]" value="{{ $router->id }}">
                            <span style="font-size: 13px;">
                                <span style="font-weight: 600;">{{ $router->name }}</span>
                                <span class="font-mono" style="color: var(--accent); font-size: 11px; margin-left: 10px;">
                                    {{ $router->ip_address }}
                                </span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        ▶ Run on Selected Routers
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Execution history -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Execution History</span>
        </div>
        <div style="padding: 16px;">
            @forelse($executions as $execution)
            <div style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; font-weight: 600;">{{ $execution->router->name }}</span>
                    @if($execution->status === 'success')
                        <span class="badge badge-success">{{ $execution->execution_time }}ms</span>
                    @else
                        <span class="badge badge-danger">Failed</span>
                    @endif
                </div>
                <div class="font-mono" style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">
                    {{ $execution->created_at->diffForHumans() }}
                </div>
            </div>
            @empty
                <p style="color: var(--text-muted); font-size: 12px; font-family: 'JetBrains Mono', monospace;">
                    No executions yet
                </p>
            @endforelse
        </div>
    </div>

</div>
@endsection