@extends('layouts.app')

@section('title', 'Repair Jobs - SDP Dashboard')

@section('content')
<div x-data="repairJobsPage()" x-init="initTable()">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100">Repair Jobs</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2">Monitor open and closed repair orders from Odoo</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        {{-- Total Jobs --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Jobs</span>
            </div>
            <p class="text-3xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalJobs) }}</p>
        </div>

        {{-- Open Jobs --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-200 dark:border-amber-800/50 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-sm font-medium text-amber-600 dark:text-amber-400">Open</span>
            </div>
            <p class="text-3xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($openJobs) }}</p>
        </div>

        {{-- Closed Jobs --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-green-200 dark:border-green-800/50 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-sm font-medium text-green-600 dark:text-green-400">Closed</span>
            </div>
            <p class="text-3xl font-bold text-green-700 dark:text-green-300">{{ number_format($closedJobs) }}</p>
        </div>

    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm mb-6">
        <div class="p-5">
            <div class="flex flex-wrap items-end gap-4">
                {{-- Status Filter Tabs --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Status</label>
                    <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <button @click="statusFilter = 'all'; reloadTable()"
                                :class="statusFilter === 'all' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-4 py-2 text-sm font-medium transition-colors">All</button>
                        <button @click="statusFilter = 'open'; reloadTable()"
                                :class="statusFilter === 'open' ? 'bg-amber-500 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-4 py-2 text-sm font-medium transition-colors border-l border-slate-200 dark:border-slate-700">Open</button>
                        <button @click="statusFilter = 'closed'; reloadTable()"
                                :class="statusFilter === 'closed' ? 'bg-green-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-4 py-2 text-sm font-medium transition-colors border-l border-slate-200 dark:border-slate-700">Closed</button>
                    </div>
                </div>

                {{-- Date Range --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">From</label>
                    <input type="date" x-model="startDate" @change="reloadTable()"
                           class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">To</label>
                    <input type="date" x-model="endDate" @change="reloadTable()"
                           class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                {{-- Reset --}}
                <button @click="statusFilter = 'all'; startDate = ''; endDate = ''; reloadTable()"
                        class="px-4 py-2 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    Reset
                </button>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table id="repairJobsTable" class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Vehicle</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Supplier</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Close Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function repairJobsPage() {
    return {
        statusFilter: 'all',
        startDate: '',
        endDate: '',
        table: null,

        initTable() {
            const self = this;
            this.table = $('#repairJobsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('maintenance.repair.jobs.data') }}",
                    data: function(d) {
                        d.status = self.statusFilter;
                        d.start_date = self.startDate;
                        d.end_date = self.endDate;
                    }
                },
                columns: [
                    {
                        data: 'nomor_job',
                        render: function(data, type, row) {
                            return '<div class="font-medium text-slate-800 dark:text-slate-200">' + data + '</div>' +
                                   '<div class="text-xs text-slate-400">' + (row.service_type || '') + '</div>';
                        }
                    },
                    {
                        data: 'tanggal_job',
                        render: function(data) {
                            return '<span class="text-slate-600 dark:text-slate-300">' + data + '</span>';
                        }
                    },
                    {
                        data: 'state_label',
                        className: 'text-center',
                        render: function(data, type, row) {
                            let cls = '';
                            if (row.is_open) {
                                if (row.state === 'under_repair') cls = 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400';
                                else if (row.state === 'confirmed') cls = 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400';
                                else cls = 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400';
                            } else {
                                cls = 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400';
                            }
                            let badge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium ' + cls + '">' + data + '</span>';
                            if (row.is_open && row.days_open !== null) {
                                badge += '<div class="text-xs text-slate-400 mt-1">' + row.days_open + ' days</div>';
                            }
                            return badge;
                        }
                    },
                    {
                        data: 'nomor_polisi',
                        render: function(data, type, row) {
                            return '<div class="font-medium text-slate-700 dark:text-slate-300">' + data + '</div>' +
                                   '<div class="text-xs text-slate-400">' + (row.model || '') + '</div>';
                        }
                    },
                    {
                        data: 'supplier',
                        render: function(data) {
                            return '<span class="text-slate-600 dark:text-slate-300">' + data + '</span>';
                        }
                    },
                    {
                        data: 'harga_total',
                        className: 'text-right',
                        render: function(data, type, row) {
                            if (row.is_open) return '<span class="text-slate-400">—</span>';
                            return '<div class="font-medium text-slate-800 dark:text-slate-200">Rp ' + data + '</div>' +
                                   (row.harga_pajak && row.harga_pajak !== '0' ? '<div class="text-xs text-slate-400">Tax: Rp ' + row.harga_pajak + '</div>' : '');
                        }
                    },
                    {
                        data: 'tanggal_close',
                        render: function(data, type, row) {
                            if (row.is_open) return '<span class="text-slate-400">—</span>';
                            return '<span class="text-slate-600 dark:text-slate-300">' + data + '</span>';
                        }
                    }
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                language: {
                    processing: '<div class="flex items-center gap-2 text-indigo-600"><svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Loading...</div>',
                    emptyTable: 'No repair jobs found',
                    info: 'Showing _START_ to _END_ of _TOTAL_ jobs',
                    lengthMenu: 'Show _MENU_ jobs',
                },
                dom: '<"flex items-center justify-between flex-wrap gap-4 p-4"lf>rt<"flex items-center justify-between flex-wrap gap-4 p-4 border-t border-slate-100 dark:border-slate-800"ip>',
                drawCallback: function() {
                    // Style the search and length controls
                    $('#repairJobsTable_filter input').addClass('px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500');
                    $('#repairJobsTable_length select').addClass('px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm');
                }
            });
        },

        reloadTable() {
            if (this.table) {
                this.table.ajax.reload();
            }
        }
    };
}
</script>
@endsection
