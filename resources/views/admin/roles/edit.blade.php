@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        Edit Role: {{ $role->name }}
    </h1>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden max-w-2xl">
    <div class="p-6">
        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf @method('PUT')
            
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Role Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" class="w-full bg-slate-50 dark:bg-slate-800 border {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500' }} rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500" required>
                @error('name') 
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug</label>
                <input type="text" value="{{ $role->slug }}" class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 text-slate-500 dark:text-slate-400 cursor-not-allowed" disabled>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Slug cannot be changed</p>
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100">{{ old('description', $role->description) }}</textarea>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Save Changes
                </button>
                <a href="{{ route('admin.roles.permissions', $role) }}" class="px-5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-indigo-600 dark:text-indigo-400 rounded-xl font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    Manage Permissions
                </a>
                <a href="{{ route('admin.roles.index') }}" class="px-5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium transition ml-auto">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
