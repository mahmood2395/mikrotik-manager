@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Compare: {{ $scriptName }}</h1>
        <p class="text-gray-500 text-sm mt-1">Script content across all routers</p>
    </div>
    <a href="javascript:history.back()"
       class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm">
        Back
    </a>
</div>

<!-- App DB version -->
@if($appScript)
<div class="bg-white rounded shadow overflow-hidden mb-6">
    <div class="flex items-center justify-between px-4 py-3 bg-blue-50 border-b border-blue-100">
        <span class="font-semibold text-blue-800">📦 App DB Version</span>
        <a href="{{ route('scripts.edit', $appScript) }}"
           class="text-blue-600 text-xs hover:underline">Edit</a>
    </div>
    <pre class="p-4 text-xs font-mono text-gray-700 overflow-x-auto bg-gray-50">{{ $appScript->content }}</pre>
</div>
@else
<div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6 text-sm text-yellow-800">
    ⚠️ This script is not in the app DB yet. Import it from any router to save it.
</div>
@endif

<!-- Router versions side by side -->
<div class="grid grid-cols-1 gap-4">
    @foreach($results as $result)
    <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
            <div>
                <span class="font-semibold text-gray-800">{{ $result['router']->name }}</span>
                <span class="text-gray-400 font-mono text-xs ml-2">{{ $result['router']->ip_address }}</span>
            </div>
            <div class="flex items-center gap-3">
                @if(!$result['found'])
                    <span class="text-red-500 text-xs">❌ Not found on this router</span>
                @else
                    <!-- Push app version to this router -->
                    @if($appScript)
                    <form action="{{ route('router-scripts.push', $result['router']) }}" method="POST">
                        @csrf
                        <input type="hidden" name="script_id" value="{{ $appScript->id }}">
                        <button type="submit"
                                class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                            ↑ Push App Version
                        </button>
                    </form>
                    @endif

                    <!-- Import this router's version to app DB -->
                    <form action="{{ route('router-scripts.import', $result['router']) }}" method="POST">
                        @csrf
                        <input type="hidden" name="name" value="{{ $scriptName }}">
                        <button type="submit"
                                class="bg-purple-600 text-white px-3 py-1 rounded text-xs hover:bg-purple-700">
                            ↓ Use This Version
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(isset($result['error']))
            <p class="p-4 text-red-500 text-sm font-mono">{{ $result['error'] }}</p>
        @elseif($result['found'])
            <pre class="p-4 text-xs font-mono text-gray-700 overflow-x-auto bg-gray-50">{{ $result['content'] }}</pre>
        @else
            <p class="p-4 text-gray-400 text-sm italic">Script not present on this router.</p>
        @endif
    </div>
    @endforeach
</div>
@endsection