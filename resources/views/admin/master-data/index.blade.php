@extends('layouts.app', ['headerTitle' => 'Master Data'])

@section('title', 'Master Data')

@section('content')

{{-- Tabs --}}
<div class="border-b border-slate-100 mb-6">
    <nav class="flex gap-8 overflow-x-auto">
        @php
            $tabs = [
                'user' => 'User',
                'area' => 'Area',
                'unit' => 'Unit',
                'material' => 'Material',
                'target' => 'Target Harian',
            ];
        @endphp
        @foreach($tabs as $key => $label)
            <a href="?tab={{ $key }}"
               class="pb-3 px-1 text-sm sm:text-base font-bold border-b-2 transition-colors whitespace-nowrap
                      {{ ($activeTab ?? 'user') === $key ? 'border-[var(--accent)] text-[var(--primary)]' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>
</div>

{{-- Tab Content --}}
@if(($activeTab ?? 'user') === 'user')
    @include('admin.master-data.partials.user-table')
@elseif($activeTab === 'area')
    @include('admin.master-data.partials.area-table')
@elseif($activeTab === 'unit')
    @include('admin.master-data.partials.unit-table')
@elseif($activeTab === 'material')
    @include('admin.master-data.partials.material-table')
@elseif($activeTab === 'target')
    @include('admin.master-data.partials.target-table')
@endif
@endsection
