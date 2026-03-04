@extends('layouts.app')

@section('title', 'Field Permissions: ' . $doctype)

@section('content')
<div class="mb-8">
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 transition font-medium">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Roles
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('admin.roles.permissions', $role) }}" class="ml-1 md:ml-2 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 transition font-medium">{{ $role->name }}</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 md:ml-2 text-slate-800 dark:text-slate-200 font-semibold">{{ $doctype }} Fields</span>
                </div>
            </li>
        </ol>
    </nav>
    <div class="flex flex-col mb-4">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            {{ $doctype }} Field Permissions
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Configure which fields the "{{ $role->name }}" role can read/write</p>
    </div>
</div>

<form action="{{ route('admin.roles.update-field-permissions', [$role, $doctype]) }}" method="POST">
    @csrf
    
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden mb-8 max-w-4xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 w-1/2">Field</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 text-center w-1/4">Can Read</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 text-center w-1/4">Can Write</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($fields as $field => $label)
                        @php $perm = $fieldPerms[$field] ?? null; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 align-middle">
                                <span class="font-medium text-slate-800 dark:text-slate-200 block">{{ $label }}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $field }}</span>
                            </td>
                            <td class="py-3 px-4 align-middle text-center">
                                <input type="checkbox" name="fields[{{ $field }}][read]" value="1" {{ ($perm?->can_read ?? true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-slate-100 border-slate-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                            </td>
                            <td class="py-3 px-4 align-middle text-center">
                                <input type="checkbox" name="fields[{{ $field }}][write]" value="1" {{ $perm?->can_write ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-slate-100 border-slate-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex flex-wrap items-center gap-3">
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Save Field Permissions
            </button>
            <a href="{{ route('admin.roles.permissions', $role) }}" class="px-5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium transition">
                Cancel
            </a>
        </div>
    </div>
</form>

<div class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50 rounded-xl p-4 flex items-start gap-3 mt-6">
    <svg class="w-5 h-5 shrink-0 mt-0.5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <div class="text-sm">
        <span class="font-bold">Note:</span> If a field has "Can Write" unchecked, that field will be displayed as read-only for users with this role.
    </div>
</div>
@endsection
