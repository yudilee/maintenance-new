@extends('layouts.app')

@section('title', 'General Settings - HARENT Dashboard')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('maintenance.dashboard') }}" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">General Settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage application configuration and appearance</p>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="space-y-8">
        <!-- Appearance Settings -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Appearance</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Update logos and visual branding</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('settings.general') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="space-y-6">
                    <!-- Config Logo Upload -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Application Logo</label>
                        <div class="flex items-center gap-6">
                            <div class="w-32 h-16 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center justify-center p-2 overflow-hidden">
                                <img src="{{ $general['app_logo_path'] }}" alt="Current Logo" class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="flex-1">
                                <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-slate-500
                                  file:mr-4 file:py-2.5 file:px-4
                                  file:rounded-xl file:border-0
                                  file:text-sm file:font-bold
                                  file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-400
                                  hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 transition-colors
                                cursor-pointer"/>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">We recommend an image that fits nicely in the top navbar (height roughly 40-50px). Max 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Config Logo Link -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Logo Link (URL)</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Where the user goes when they click the app logo.</p>
                        <input type="text" name="app_logo_link" value="{{ $general['app_logo_link'] }}" 
                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="e.g. /maintenance or https://harent.com">
                    </div>
                </div>

                <div class="flex justify-end mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 dark:shadow-none flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- About Section -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-6 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                <span class="font-bold text-slate-700 dark:text-slate-300">HARENT Dashboard</span> &bull; 
                Version 2.0 &bull; 
                Built with Laravel & Alpine.js
            </p>
        </div>
    </div>
</div>
@endsection
