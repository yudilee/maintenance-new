@extends('layouts.guest')

@section('title', 'Login - Vehicle maintenance record')

@section('content')
<div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/50 dark:border-slate-800/50 overflow-hidden theme-transition ring-1 ring-slate-200 dark:ring-slate-800">
    
    <!-- Header Section -->
    <div class="px-8 pt-10 pb-8 text-center bg-gradient-to-b from-indigo-50/50 to-white/0 dark:from-indigo-950/20 dark:to-slate-900/0">
        <div class="mx-auto mb-6 flex items-center justify-center">
            <img src="{{ \App\Models\Setting::get('app_logo_path', asset('images/harent-logo.png')) }}" alt="Vehicle maintenance record" class="h-16 w-auto object-contain">
        </div>
        <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mb-2">Vehicle maintenance record</h3>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Log into your workshop account</p>
    </div>
    
    <!-- Body Section -->
    <div class="px-8 pb-10">
        @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50 text-red-600 dark:text-red-400 text-sm space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            
            <!-- Email -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <input type="email" name="email" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 transition-all sm:text-sm shadow-sm" placeholder="admin@example.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>
            
            <!-- Password -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <input type="password" name="password" class="w-full pl-11 pr-4 py-3 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 transition-all sm:text-sm shadow-sm" placeholder="••••••••" required>
                </div>
            </div>
            
            <!-- Remember Me -->
            <div class="flex items-center pt-2">
                <div class="relative flex items-start">
                    <div class="flex h-6 items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-600 dark:focus:ring-indigo-500 dark:bg-slate-800 transition-colors cursor-pointer">
                    </div>
                    <div class="ml-2.5 text-sm leading-6">
                        <label for="remember" class="font-medium text-slate-600 dark:text-slate-400 select-none cursor-pointer">Remember me for 30 days</label>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-md shadow-indigo-200 dark:shadow-none text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all hover:-translate-y-0.5 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-white/20 group-hover:translate-x-full -translate-x-full transition-transform duration-500 ease-in-out skew-x-12"></div>
                    <span>Sign In to Dashboard</span>
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
