@extends('layouts.app')

@section('title', 'Search Results - HARENT Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 transition-colors">Search Results</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">
                Found results for: <span class="font-semibold text-indigo-600 dark:text-indigo-400">"{{ $query }}"</span>
            </p>
        </div>
    </div>

    @if(!$query || strlen($query) < 2)
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-8 text-center text-slate-500 dark:text-slate-400">
            Please enter at least 2 characters to search.
        </div>
    @else
        <!-- Results Container -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Repair Jobs -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Repair Jobs ({{ $jobs->count() }})
                </h2>
                
                @if($jobs->isEmpty())
                    <p class="text-slate-500 text-sm">No repair jobs found.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($jobs as $job)
                            <li class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <a href="{{ route('maintenance.repair.job.details', $job->nomor_job) }}" class="block">
                                    <div class="flex justify-between items-start">
                                        <div class="font-medium text-slate-700 dark:text-slate-200">{{ $job->nomor_job }}</div>
                                        <div class="text-xs text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($job->tanggal_job)->format('d M Y') }}</div>
                                    </div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                        {{ $job->mobil->nomor_polisi ?? 'No Polisi' }} - {{ $job->customer->nama_customer ?? 'Unknown Customer' }}
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Vehicles -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    Vehicles ({{ $vehicles->count() }})
                </h2>
                
                @if($vehicles->isEmpty())
                    <p class="text-slate-500 text-sm">No vehicles found.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($vehicles as $vehicle)
                            <li class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                                <div class="font-medium text-slate-700 dark:text-slate-200">{{ $vehicle->nomor_polisi }}</div>
                                <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                    {{ $vehicle->model_kendaraan }} (Chassis: {{ $vehicle->nomor_chassis }})
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Customers -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Customers ({{ $customers->count() }})
                </h2>
                
                @if($customers->isEmpty())
                    <p class="text-slate-500 text-sm">No customers found.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($customers as $customer)
                            <li class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                                <div class="font-medium text-slate-700 dark:text-slate-200">{{ $customer->nama_customer }}</div>
                                <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                    Code: {{ $customer->kode_customer }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    @endif
</div>
@endsection
