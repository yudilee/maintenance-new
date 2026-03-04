@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
        {{ isset($user) ? 'Edit User' : 'Create User' }}
    </h1>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden max-w-2xl">
    <div class="p-6">
        <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
            @csrf
            @isset($user) @method('PUT') @endisset

            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" class="w-full bg-slate-50 dark:bg-slate-800 border {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500' }} rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100 placeholder-slate-400" required autofocus>
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" class="w-full bg-slate-50 dark:bg-slate-800 border {{ $errors->has('email') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500' }} rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100 placeholder-slate-400" required>
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Role</label>
                <select name="role" id="role" class="w-full bg-slate-50 dark:bg-slate-800 border {{ $errors->has('role') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500' }} rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100" required>
                    <option value="user" {{ old('role', $user->role ?? '') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="viewer" {{ old('role', $user->role ?? '') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Password {{ isset($user) ? '(leave blank to keep current)' : '' }}
                </label>
                <input type="password" name="password" id="password" class="w-full bg-slate-50 dark:bg-slate-800 border {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500' }} rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100" {{ isset($user) ? '' : 'required' }}>
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg px-4 py-2 text-slate-900 dark:text-slate-100">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    {{ isset($user) ? 'Update User' : 'Create User' }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
