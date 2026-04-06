@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden theme-transition mb-6" x-data="odooConfig()">
    <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6">Maintenance Odoo Config</h1>
        
        <div class="flex gap-4 border-b border-slate-200 dark:border-slate-700">
            <button @click="tab = 'api'" :class="tab === 'api' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'" class="pb-3 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Odoo API
            </button>
            <button @click="tab = 'history'" :class="tab === 'history' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'" class="pb-3 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Import History
            </button>
        </div>
    </div>

    <div class="p-6">
        <!-- Odoo API Tab -->
        <div x-show="tab === 'api'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
            
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>{{ session('success') }}</div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <form action="{{ route('maintenance.odoo.settings.store') }}" method="POST" id="odooConfigForm" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="odoo_url">Odoo URL</label>
                            <input type="url" id="odoo_url" name="odoo_url" value="{{ old('odoo_url', $setting?->odoo_url ?? '') }}" class="w-full text-sm border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="database">Database</label>
                            <input type="text" id="database" name="database" value="{{ old('database', $setting?->database ?? '') }}" class="w-full text-sm border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="user_email">User Email</label>
                            <input type="email" id="user_email" name="user_email" value="{{ old('user_email', $setting?->user_email ?? '') }}" class="w-full text-sm border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="api_key">API Key / Password</label>
                            <input type="password" id="api_key" name="api_key" value="{{ old('api_key', $setting?->api_key ?? '') }}" class="w-full text-sm border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5">
                        </div>

                        <div class="flex flex-wrap gap-3 pt-4">
                            <button type="submit" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors shadow-sm">Save Config</button>
                            <button type="button" @click="testConnection" :disabled="isTesting" class="px-5 py-2.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-xl font-medium hover:bg-amber-200 dark:hover:bg-amber-800/50 transition-colors shadow-sm flex items-center gap-2">
                                <svg x-show="isTesting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Test Connection</span>
                            </button>
                            <button type="button" @click="syncNow(false)" :disabled="isSyncing" class="px-5 py-2.5 bg-indigo-600 text-white border border-indigo-700 rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200 dark:shadow-none flex items-center gap-2">
                                <svg x-show="isSyncing && !isForceSyncing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Sync Now</span>
                            </button>
                            <button type="button" @click="forceFullSync" :disabled="isSyncing" class="px-5 py-2.5 bg-rose-600 text-white border border-rose-700 rounded-xl font-medium hover:bg-rose-700 transition-colors shadow-sm shadow-rose-200 dark:shadow-none flex items-center gap-2" title="Clear last sync date and fetch everything from Odoo">
                                <svg x-show="isSyncing && isForceSyncing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Force Full Sync</span>
                            </button>
                        </div>

                        <!-- Sync Result -->
                        <div x-show="syncResult" x-transition class="mt-4">
                            <div :class="syncResult?.success ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'"
                                 class="p-4 rounded-xl border flex items-start gap-3">
                                <svg x-show="syncResult?.success" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg x-show="!syncResult?.success" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <span x-text="syncResult?.message"></span>
                                    <span x-show="syncResult?.items" class="font-semibold" x-text="' — ' + syncResult?.items + ' records processed'"></span>
                                    <span x-show="syncResult?.success" class="block text-xs mt-1 opacity-75">Switching to Import History tab...</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-3xl p-6">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Auto-Sync Schedule
                        </h3>
                        
                        <form action="{{ route('maintenance.odoo.settings.store') }}" method="POST" class="space-y-5">
                            @csrf
                            <input type="hidden" name="odoo_url" value="{{ $setting?->odoo_url ?? '' }}">
                            <input type="hidden" name="database" value="{{ $setting?->database ?? '' }}">
                            <input type="hidden" name="user_email" value="{{ $setting?->user_email ?? '' }}">
                            <input type="hidden" name="api_key" value="{{ $setting?->api_key ?? '' }}">

                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-slate-800 dark:text-slate-200">Enable Auto-Sync</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Fetch hourly/daily</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="enable_auto_sync" value="1" class="sr-only peer" {{ ($setting?->enable_auto_sync ?? false) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="sync_interval">Interval</label>
                                <select id="sync_interval" name="sync_interval" class="w-full text-sm border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5">
                                    <option value="daily" {{ ($setting?->sync_interval ?? '') == 'daily' ? 'selected' : '' }}>Daily (Midnight)</option>
                                    <option value="hourly" {{ ($setting?->sync_interval ?? '') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                    <option value="weekly" {{ ($setting?->sync_interval ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                </select>
                            </div>

                            <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                                <div class="flex justify-between items-center text-xs mb-4">
                                    <span class="text-slate-500 dark:text-slate-400">Last Sync</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $setting?->last_sync ? \Carbon\Carbon::parse($setting->last_sync)->format('M d, Y - h:i A') : 'Never' }}</span>
                                </div>
                                
                                <button type="submit" class="w-full px-5 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-medium hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors shadow-sm">Save Schedule</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Tab -->
        <div x-show="tab === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
            <div class="flex justify-between items-center mb-6">
                <p class="text-slate-500 dark:text-slate-400 text-sm">Review past sync executions and status</p>
                <button onclick="location.reload()" class="px-3 py-1.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="p-4 border-b border-slate-200 dark:border-slate-800">Date & Time</th>
                            <th class="p-4 border-b border-slate-200 dark:border-slate-800">Source</th>
                            <th class="p-4 border-b border-slate-200 dark:border-slate-800 text-center">Items Pulled</th>
                            <th class="p-4 border-b border-slate-200 dark:border-slate-800 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($histories as $history)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-4 text-sm font-medium text-slate-800 dark:text-slate-200">{{ $history->created_at->format('M d, Y - h:i:s A') }}</td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    {{ $history->source }}
                                </span>
                            </td>
                            <td class="p-4 text-center text-sm font-bold text-slate-700 dark:text-slate-300">{{ number_format($history->items) }}</td>
                            <td class="p-4 text-center">
                                @if($history->status === 'Success')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Success
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400" title="{{ $history->status }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Failed
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">No history records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($histories->hasPages())
            <div class="mt-4">
                {{ $histories->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('odooConfig', () => ({
            tab: 'api',
            isTesting: false,
            isSyncing: false,
            isForceSyncing: false,
            syncResult: null,

            testConnection() {
                this.isTesting = true;
                
                $.ajax({
                    url: "{{ route('maintenance.odoo.test_connection', [], false) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        odoo_url: $('#odoo_url').val(),
                        database: $('#database').val(),
                        user_email: $('#user_email').val(),
                        api_key: $('#api_key').val()
                    },
                    success: (response) => {
                        Toast.fire({
                            icon: response.success ? 'success' : 'error',
                            title: response.success ? 'Connection Successful!' : 'Connection Failed',
                            text: response.message || ''
                        });
                    },
                    error: () => {
                        Toast.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Failed to test connection.'
                        });
                    },
                    complete: () => {
                        this.isTesting = false;
                    }
                });
            },
            
            syncNow(force = false) {
                this.isSyncing = true;
                if (force) this.isForceSyncing = true;
                this.syncResult = null;
                
                console.log('Starting sync, force=' + force);
                
                $.ajax({
                    url: "{{ route('maintenance.odoo.sync_now', [], false) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        force: force ? 1 : 0
                    },
                    success: (response) => {
                        this.syncResult = response;
                        if (response.success) {
                            setTimeout(() => { this.tab = 'history'; }, 2000);
                        }
                    },
                    error: (xhr) => {
                        this.syncResult = { 
                            success: false, 
                            message: 'Sync Error: ' + (xhr.responseJSON?.message || xhr.statusText || 'Unknown error') 
                        };
                    },
                    complete: () => {
                        this.isSyncing = false;
                        this.isForceSyncing = false;
                    }
                });
            },
            async forceFullSync() {
                if(confirm("Are you sure you want to force a FULL sync? This will fetch all data from Odoo and might take several minutes.")) {
                    this.syncNow(true);
                }
            }

        }));
    });
</script>
@endsection
