@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Bulk Results</h1>
        <p class="text-gray-500 font-mono text-sm mt-1">{{ $command }}</p>
    </div>
    <a href="{{ route('commands.bulk') }}"
       class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm">
        Run Another
    </a>
</div>

<div class="space-y-4">
    @foreach($results as $result)
    <div class="bg-white rounded shadow overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
            <span class="font-medium text-gray-800">{{ $result['router'] }}</span>
            @if($result['status'] === 'success')
                <span class="text-green-600 text-sm">✅ {{ $result['time'] }}ms</span>
            @else
                <span class="text-red-600 text-sm">❌ Failed</span>
            @endif
        </div>
        <div class="p-4">
            @if($result['status'] === 'failed')
                <p class="text-red-500 text-sm font-mono">{{ $result['error'] }}</p>
            @else
                <pre class="text-xs font-mono text-gray-700 overflow-x-auto">{{ json_encode($result['response'], JSON_PRETTY_PRINT) }}</pre>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection