@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm mb-6">
    <ul class="list-disc list-inside space-y-1">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
