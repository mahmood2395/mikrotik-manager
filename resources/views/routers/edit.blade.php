@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Edit Router</div>
        <div class="page-subtitle font-mono">{{ $router->ip_address }}</div>
    </div>
    <a href="{{ route('routers.show', $router) }}" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width: 680px;">
    <div class="card-header">
        <span class="card-title">Device Information</span>
    </div>
    <div style="padding: 24px;">
        <form action="{{ route('routers.update', $router) }}" method="POST">
            @csrf @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">

                <div class="form-group">
                    <label class="label">Name</label>
                    <input type="text" name="name" value="{{ old('name', $router->name) }}" class="input">
                    @error('name') <p style="color:var(--danger);font-size:11px;margin-top:4px;font-family:'JetBrains Mono',monospace;">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="label">Group</label>
                    <input type="text" name="group" value="{{ old('group', $router->group) }}" class="input">
                </div>

                <div class="form-group">
                    <label class="label">IP Address</label>
                    <input type="text" name="ip_address" value="{{ old('ip_address', $router->ip_address) }}"
                           class="input input-mono">
                    @error('ip_address') <p style="color:var(--danger);font-size:11px;margin-top:4px;font-family:'JetBrains Mono',monospace;">{{ $message }}</p> @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="label">API Port</label>
                        <input type="number" name="api_port" value="{{ old('api_port', $router->api_port) }}"
                               class="input input-mono">
                    </div>
                    <div class="form-group">
                        <label class="label">REST Port</label>
                        <input type="number" name="rest_port" value="{{ old('rest_port', $router->rest_port ?? 80) }}"
                               class="input input-mono">
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Username</label>
                    <input type="text" name="username" value="{{ old('username', $router->username) }}"
                           class="input input-mono">
                </div>

                <div class="form-group">
                    <label class="label">Password <span style="color:var(--text-muted);text-transform:none;font-size:10px;">(leave blank to keep)</span></label>
                    <input type="password" name="password" class="input input-mono">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="label">Description</label>
                    <textarea name="description" rows="2" class="input">{{ old('description', $router->description) }}</textarea>
                </div>

            </div>

            <div style="display: flex; gap: 10px; margin-top: 8px;">
                <button type="submit" class="btn btn-primary">Update Router</button>
                <a href="{{ route('routers.show', $router) }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection