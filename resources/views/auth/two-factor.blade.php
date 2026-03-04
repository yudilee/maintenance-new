@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            Security Settings
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Manage two-factor authentication and active sessions.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 2FA Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            <h3 class="font-medium text-slate-800 dark:text-slate-100">Two-Factor Authentication</h3>
        </div>
        <div class="p-6 flex-1">
            @if($user->two_factor_enabled)
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 rounded-xl p-4 flex items-start gap-3 mb-6">
                    <svg class="w-6 h-6 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <h4 class="text-sm font-semibold text-emerald-800 dark:text-emerald-400">Enabled</h4>
                        <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-1">Since {{ $user->two_factor_confirmed_at?->format('d M Y, H:i') }}</p>
                    </div>
                </div>
                
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                    Your account is protected with two-factor authentication. You'll need to enter a code from 
                    your authenticator app when signing in.
                </p>
                
                <div class="border-t border-slate-200 dark:border-slate-800 my-6"></div>
                
                <h4 class="text-sm font-medium text-slate-800 dark:text-slate-200 flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Recovery Codes
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    Recovery codes can be used to access your account if you lose your authenticator device.
                </p>
                
                <form action="{{ route('2fa.regenerate-codes') }}" method="POST" class="mb-6">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Confirm Password</label>
                        <input type="password" name="password" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" required>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Regenerate Recovery Codes
                    </button>
                </form>
                
                <div class="border-t border-red-100 dark:border-red-900/30 my-6"></div>
                
                <h4 class="text-sm font-medium text-red-600 dark:text-red-400 flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Disable 2FA
                </h4>
                <form action="{{ route('2fa.disable') }}" method="POST" 
                      onsubmit="return confirm('Are you sure? This will make your account less secure.')">
                    @csrf
                    @method('DELETE')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Confirm Password</label>
                        <input type="password" name="password" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" required>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition flex items-center justify-center gap-2 shadow-sm shadow-red-200 dark:shadow-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Disable Two-Factor Authentication
                    </button>
                </form>
            @else
                <div class="text-center py-12 flex flex-col items-center justify-center h-full">
                    <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h5 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Not Enabled</h5>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 max-w-sm mx-auto">
                        Add an extra layer of security to your account by enabling two-factor authentication.
                    </p>
                    <a href="{{ route('2fa.enable') }}" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Enable 2FA
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Active Sessions -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <h3 class="font-medium text-slate-800 dark:text-slate-100">Active Sessions</h3>
            </div>
            <span class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 px-2.5 py-0.5 rounded-full text-xs font-medium">{{ $sessions->count() }}</span>
        </div>
        <div class="p-0 flex-1">
            <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
                @forelse($sessions as $session)
                <div class="p-6 flex justify-between items-start hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                    <div class="flex gap-4">
                        <div class="mt-1">
                            @if($session->device_icon == 'laptop')
                                <svg class="w-8 h-8 {{ $session->is_current ? 'text-indigo-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            @elseif($session->device_icon == 'phone')
                                <svg class="w-8 h-8 {{ $session->is_current ? 'text-indigo-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            @elseif($session->device_icon == 'tablet')
                                <svg class="w-8 h-8 {{ $session->is_current ? 'text-indigo-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            @else
                                <svg class="w-8 h-8 {{ $session->is_current ? 'text-indigo-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            @endif
                        </div>
                        <div>
                            <div class="font-medium text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                {{ $session->browser }} on {{ $session->platform }}
                                @if($session->is_current)
                                <span class="bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">Current</span>
                                @endif
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ $session->ip_address }}
                            </div>
                            <div class="text-xs text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $session->last_active_at?->diffForHumans() ?? 'Unknown' }}
                            </div>
                        </div>
                    </div>
                    @if(!$session->is_current)
                    <form action="{{ route('2fa.terminate-session', $session) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Terminate Session">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                    No active sessions found
                </div>
                @endforelse
            </div>
        </div>
        
        @if($sessions->where('is_current', false)->count() > 0)
        <div class="p-4 sm:p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
            <form action="{{ route('2fa.terminate-other-sessions') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <div class="flex-1">
                    <input type="password" name="password" class="w-full px-4 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" placeholder="Confirm password to log out other sessions" required>
                </div>
                <button type="submit" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-red-900/30 rounded-xl text-red-600 dark:text-red-400 font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Log Out Other Sessions
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
