@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Create Role
    </h1>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden max-w-2xl">
    <div class="p-6">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Role Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full bg-slate-50 dark:bg-slate-800 border {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500' }} rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500" required autofocus>
                @error('name') 
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug (unique identifier)</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="w-full bg-slate-50 dark:bg-slate-800 border {{ $errors->has('slug') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500' }} rounded-lg px-4 py-2 font-mono text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500" required>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Lowercase, no spaces (e.g., workshop_manager)</p>
                @error('slug') 
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Create & Configure Permissions
                </button>
                <a href="{{ route('admin.roles.index') }}" class="px-5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    document.getElementById('slug').value = slug;
});
</script>
@endsection
