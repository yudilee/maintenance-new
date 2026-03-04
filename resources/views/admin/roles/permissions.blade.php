@extends('layouts.app')

@section('title', 'Permissions: ' . $role->name)

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            {{ $role->name }} Permissions
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Configure DocType and field-level permissions</p>
    </div>
    <div>
        <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Roles
        </a>
    </div>
</div>

<form action="{{ route('admin.roles.update-permissions', $role) }}" method="POST">
    @csrf
    
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <h3 class="font-medium text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                DocType Permissions
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 w-1/4">DocType</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 text-center">Read</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 text-center">Write</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 text-center">Create</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 text-center">Delete</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 text-center">Export</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 px-6">Fields</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($doctypes as $doctype => $label)
                        @php $perm = $permissions[$doctype] ?? null; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 align-middle">
                                <span class="font-medium text-slate-800 dark:text-slate-200 block">{{ $label }}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $doctype }}</span>
                            </td>
                            <td class="py-3 px-4 align-middle text-center">
                                <input type="checkbox" name="permissions[{{ $doctype }}][read]" value="1" {{ $perm?->can_read ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-slate-100 border-slate-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                            </td>
                            <td class="py-3 px-4 align-middle text-center">
                                <input type="checkbox" name="permissions[{{ $doctype }}][write]" value="1" {{ $perm?->can_write ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-slate-100 border-slate-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                            </td>
                            <td class="py-3 px-4 align-middle text-center">
                                <input type="checkbox" name="permissions[{{ $doctype }}][create]" value="1" {{ $perm?->can_create ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-slate-100 border-slate-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                            </td>
                            <td class="py-3 px-4 align-middle text-center">
                                <input type="checkbox" name="permissions[{{ $doctype }}][delete]" value="1" {{ $perm?->can_delete ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-slate-100 border-slate-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                            </td>
                            <td class="py-3 px-4 align-middle text-center">
                                <input type="checkbox" name="permissions[{{ $doctype }}][export]" value="1" {{ $perm?->can_export ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-slate-100 border-slate-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                            </td>
                            <td class="py-3 px-6 align-middle">
                                @if(in_array($doctype, ['Job', 'Vehicle', 'Booking', 'PdiRecord', 'TowingRecord']))
                                    <a href="{{ route('admin.roles.field-permissions', [$role, $doctype]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg text-sm font-medium transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        Fields
                                        @if(isset($fieldPermissions[$doctype]) && $fieldPermissions[$doctype]->count() > 0)
                                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 ml-1 text-[10px] font-bold leading-none bg-indigo-600 text-white rounded-full">
                                                {{ $fieldPermissions[$doctype]->count() }}
                                            </span>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Save Permissions
            </button>
        </div>
    </div>
</form>

<div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 rounded-2xl shadow-sm overflow-hidden mt-6">
    <div class="p-4 border-b border-indigo-100 dark:border-indigo-800/50 pb-3">
        <h6 class="font-medium text-indigo-800 dark:text-indigo-300 flex items-center gap-2">
            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Permission Legend
        </h6>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-indigo-700 dark:text-indigo-400">
            <div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2">
                        <span class="font-bold shrink-0">Read </span>
                        <span class="opacity-80"> View records in lists and detail pages</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold shrink-0">Write </span>
                        <span class="opacity-80"> Update existing records and save changes</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold shrink-0">Create</span>
                        <span class="opacity-80"> Add new records to the system</span>
                    </li>
                </ul>
            </div>
            <div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2">
                        <span class="font-bold shrink-0">Delete</span>
                        <span class="opacity-80"> Remove records permanently</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold shrink-0">Export</span>
                        <span class="opacity-80"> Download records as spreadsheet or CSV</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold shrink-0">Fields</span>
                        <span class="opacity-80"> Restrict access (read/write) out of specific fields</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
