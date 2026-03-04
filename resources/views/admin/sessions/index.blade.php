@extends('layouts.app')

@section('title', 'Session Manager')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            Session Manager
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">View and manage all active user sessions</p>
    </div>
    <div>
        <form action="{{ route('admin.sessions.cleanup') }}" method="POST" class="inline" onsubmit="return confirm('Clean up sessions inactive for {{ $schedule->session_cleanup_days ?? 7 }} days?');">
            @csrf
            <button type="submit" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Cleanup Inactive
            </button>
        </form>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-sm">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-green-100 text-sm font-medium mb-1">Online Now</p>
                <h3 class="text-3xl font-bold">{{ $stats['online_now'] }}</h3>
            </div>
            <div class="p-3 bg-white/20 rounded-xl">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-sm">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-blue-100 text-sm font-medium mb-1">Logins Today</p>
                <h3 class="text-3xl font-bold">{{ $stats['today_logins'] }}</h3>
            </div>
            <div class="p-3 bg-white/20 rounded-xl">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-sm">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-indigo-100 text-sm font-medium mb-1">Active Users Today</p>
                <h3 class="text-3xl font-bold">{{ $stats['unique_users_today'] }}</h3>
            </div>
            <div class="p-3 bg-white/20 rounded-xl">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-slate-600 to-slate-700 rounded-2xl p-6 text-white shadow-sm">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-slate-200 text-sm font-medium mb-1">Total Sessions</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_sessions'] }}</h3>
            </div>
            <div class="p-3 bg-white/20 rounded-xl">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
        </div>
    </div>
</div>

<!-- Device Breakdown -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 mb-8 shadow-sm">
    <div class="flex justify-between items-center">
        <span class="font-medium text-slate-700 dark:text-slate-300">Devices:</span>
        <div class="flex gap-2">
            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-full text-sm font-medium flex items-center gap-1">
                Desktop: {{ $stats['devices']['desktop'] }}
            </span>
            <span class="px-3 py-1 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full text-sm font-medium flex items-center gap-1">
                Mobile: {{ $stats['devices']['mobile'] }}
            </span>
            <span class="px-3 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-full text-sm font-medium flex items-center gap-1">
                Tablet: {{ $stats['devices']['tablet'] }}
            </span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Cleanup Settings Card -->
    <div class="lg:col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm flex flex-col">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-medium text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Auto Cleanup
            </h3>
        </div>
        <div class="p-5 flex-1">
            <form action="{{ route('admin.sessions.settings') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="session_cleanup_enabled" value="1" class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500" {{ ($schedule->session_cleanup_enabled ?? true) ? 'checked' : '' }}>
                        <span class="text-slate-700 dark:text-slate-300">Enable automated cleanup</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Remove inactive after (days)</label>
                    <input type="number" name="session_cleanup_days" min="1" max="365" value="{{ $schedule->session_cleanup_days ?? 7 }}" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                </div>
                <div class="text-xs text-slate-500 mb-4">
                    Sessions inactive for longer than this limit will be automatically removed securely.
                </div>
                <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition">
                    Save Settings
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Actions Card -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-medium text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter Sessions
            </h3>
        </div>
        <div class="p-5">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Filter by User</label>
                    <select name="user_id" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Device Type</label>
                    <select name="device" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                        <option value="">All Devices</option>
                        <option value="desktop" {{ request('device') == 'desktop' ? 'selected' : '' }}>Desktop</option>
                        <option value="mobile" {{ request('device') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                        <option value="tablet" {{ request('device') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex items-center gap-3 pt-2">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.sessions.index') }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-medium transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sessions Table -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <th class="py-4 px-6 font-semibold text-sm text-slate-600 dark:text-slate-300">User</th>
                    <th class="py-4 px-6 font-semibold text-sm text-slate-600 dark:text-slate-300">Device</th>
                    <th class="py-4 px-6 font-semibold text-sm text-slate-600 dark:text-slate-300">Browser / OS</th>
                    <th class="py-4 px-6 font-semibold text-sm text-slate-600 dark:text-slate-300">IP Address</th>
                    <th class="py-4 px-6 font-semibold text-sm text-slate-600 dark:text-slate-300">Activity</th>
                    <th class="py-4 px-6 font-semibold text-sm text-slate-600 dark:text-slate-300 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @php $currentSessionId = session()->getId(); @endphp
                @forelse($sessions as $session)
                @php $isCurrentSession = $session->session_id === $currentSessionId; @endphp
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition {{ $isCurrentSession ? 'bg-indigo-50/50 dark:bg-indigo-900/10' : '' }}">
                    <td class="py-4 px-6 align-middle">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400 flex items-center justify-center font-bold">
                                {{ strtoupper(substr($session->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                    {{ $session->user->name ?? 'Unknown' }}
                                    @if($isCurrentSession)
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400">Current</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $session->user->email ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6 align-middle">
                        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            @if($session->device_type === 'desktop')
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            @elseif($session->device_type === 'mobile')
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            @else
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            @endif
                            <span class="capitalize">{{ $session->device_type ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 align-middle text-slate-600 dark:text-slate-400 text-sm">
                        {{ $session->browser ?? 'Unknown' }} / {{ $session->platform ?? 'Unknown' }}
                    </td>
                    <td class="py-4 px-6 align-middle">
                        <span class="font-mono text-sm bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-1 rounded">
                            {{ $session->ip_address ?? '-' }}
                        </span>
                    </td>
                    <td class="py-4 px-6 align-middle">
                        <div class="flex flex-col">
                            <span class="text-sm text-slate-800 dark:text-slate-200">{{ $session->last_active_at?->diffForHumans() ?? 'Unknown' }}</span>
                            <div class="mt-1">
                                @if($isCurrentSession)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active Now
                                    </span>
                                @elseif($session->last_active_at && $session->last_active_at >= now()->subMinutes(5))
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Online
                                    </span>
                                @elseif($session->last_active_at && $session->last_active_at >= now()->subHours(1))
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-600 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Idle
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Offline
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6 align-middle text-right">
                        @if($isCurrentSession)
                            <button disabled class="p-2 text-slate-300 dark:text-slate-600 cursor-not-allowed" title="Cannot terminate current session">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </button>
                        @else
                            <form action="{{ route('admin.sessions.terminate', $session) }}" method="POST" onsubmit="return confirm('Terminate this session for {{ $session->user->name ?? 'user' }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition" title="Terminate Session">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"></path></svg>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-500 dark:text-slate-400">
                        <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <p>No active sessions found via history.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $sessions->links() }}
</div>
@endsection
