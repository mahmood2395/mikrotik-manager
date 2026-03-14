@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Scripts</div>
        <div class="page-subtitle">{{ $scripts->count() }} script(s) in library</div>
    </div>
    <a href="{{ route('scripts.create') }}" class="btn btn-primary">+ New Script</a>
</div>

@if($scripts->isEmpty())
    <div class="card" style="padding: 48px; text-align: center; color: var(--text-muted);">
        <div style="font-size: 32px; margin-bottom: 12px;">⌗</div>
        <div class="font-mono" style="font-size: 13px;">No scripts in library yet</div>
    </div>
@else
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Executions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scripts as $script)
            <tr>
                <td style="font-weight: 600; font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--accent);">
                    {{ $script->name }}
                </td>
                <td>
                    @if($script->category)
                        <span class="badge badge-blue">{{ $script->category }}</span>
                    @else
                        <span style="color: var(--text-muted);">—</span>
                    @endif
                </td>
                <td style="color: var(--text-muted); font-size: 12px;">
                    {{ Str::limit($script->description, 60) ?? '—' }}
                </td>
                <td style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--text-muted);">
                    {{ $script->executions_count }}
                </td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('scripts.show', $script) }}" class="btn btn-ghost" style="padding: 5px 10px; font-size: 11px;">▶ Run</a>
                        <a href="{{ route('scripts.edit', $script) }}" class="btn btn-ghost" style="padding: 5px 10px; font-size: 11px;">Edit</a>
                        <form action="{{ route('scripts.destroy', $script) }}" method="POST"
                              onsubmit="return confirm('Delete this script?')">
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