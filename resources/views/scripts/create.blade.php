@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">New Script</div>
        <div class="page-subtitle">Add a script to your library</div>
    </div>
    <a href="{{ route('scripts.index') }}" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <span class="card-title">Script Details</span>
    </div>
    <div style="padding: 24px;">
        <form action="{{ route('scripts.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="label">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="input input-mono" placeholder="block-social-media">
                    @error('name') <p style="color:var(--danger);font-size:11px;margin-top:4px;font-family:'JetBrains Mono',monospace;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Category</label>
                    <input type="text" name="category" value="{{ old('category') }}"
                           class="input" placeholder="firewall">
                </div>
                <div style="grid-column: span 2;">
                    <label class="label">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           class="input" placeholder="What does this script do?">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="label">Script Content</label>
                <textarea name="content" rows="14"
                          class="input input-mono"
                          style="resize: vertical; line-height: 1.6;"
                          placeholder="/ip firewall filter add chain=forward dst-address=1.2.3.4 action=drop">{{ old('content') }}</textarea>
                @error('content') <p style="color:var(--danger);font-size:11px;margin-top:4px;font-family:'JetBrains Mono',monospace;">{{ $message }}</p> @enderror
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Save Script</button>
                <a href="{{ route('scripts.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection