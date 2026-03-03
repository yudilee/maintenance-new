@extends('layouts.app')

@section('title', 'Repair Jobs - SDP Dashboard')

@section('content')
<div x-data="repairJobsPage()" x-init="initTable()">
<div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden theme-transition mb-6">
    {{-- Page Header --}}
    <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Repair Jobs</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Monitor open and closed repair orders from Odoo</p>
        </div>
    </div>

    <div class="p-6">
        {{-- Filter Config UI --}}
        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 mb-6">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="w-full md:w-auto">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wide">Status</label>
                    <div class="inline-flex rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 p-1">
                        <button @click="statusFilter = 'all'; reloadTable()"
                                :class="statusFilter === 'all' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-all">All</button>
                        <button @click="statusFilter = 'open'; reloadTable()"
                                :class="statusFilter === 'open' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-all">Open</button>
                        <button @click="statusFilter = 'closed'; reloadTable()"
                                :class="statusFilter === 'closed' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-all">Closed</button>
                    </div>
                </div>

                <div class="flex-grow flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wide">From Date</label>
                        <input type="date" x-model="startDate" @change="reloadTable()"
                               class="w-full text-sm border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5 shadow-sm px-3">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wide">To Date</label>
                        <input type="date" x-model="endDate" @change="reloadTable()"
                               class="w-full text-sm border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5 shadow-sm px-3">
                    </div>
                </div>

                <div class="w-full md:w-auto mt-4 md:mt-0">
                    <button @click="statusFilter = 'all'; startDate = ''; endDate = ''; reloadTable()" 
                            class="w-full md:w-auto px-6 py-2.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl font-bold uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm hover:border-indigo-300 transition-colors">
                <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 flex items-center gap-2">
                    <div class="w-6 h-6 rounded bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    Total Jobs
                </div>
                <div class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ number_format($totalJobs) }}</div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm hover:border-amber-300 transition-colors">
                 <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 flex items-center gap-2">
                    <div class="w-6 h-6 rounded bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    Open Jobs
                </div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ number_format($openJobs) }}</div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm hover:border-green-300 transition-colors">
                 <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 flex items-center gap-2">
                    <div class="w-6 h-6 rounded bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    Closed Jobs
                </div>
                <div class="text-2xl font-black text-green-600 dark:text-green-400">{{ number_format($closedJobs) }}</div>
            </div>
        </div>

        <div id="dt-controls-top" class="mb-3"></div>

        <div class="frozen-table-container">
            <table id="repairJobsTable" class="frozen-table" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th class="sticky-col" style="min-width:200px;">Job Number</th>
                        <th style="min-width:100px;">Date</th>
                        <th class="text-center" style="min-width:140px;">Status</th>
                        <th style="min-width:200px;">Vehicle</th>
                        <th style="min-width:200px;">Supplier</th>
                        <th class="text-right" style="min-width:140px;">Total</th>
                        <th style="min-width:100px;">Close Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        
        <div id="dt-controls-bottom" class="mt-3"></div>
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
                dom: '<"flex flex-col md:flex-row items-center justify-between gap-4"Bf>rt<"flex flex-col md:flex-row items-center justify-between gap-4 mt-2"ip>',
                buttons: [
                    {
                        extend: 'colvis',
                        text: '<span class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>Show/Hide Columns</span>',
                    }
                ],
                drawCallback: function() {
                    $('#repairJobsTable_filter input').addClass('px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 ml-2');
                    $('#repairJobsTable_length select').addClass('px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm mx-2');
                }
            });

            // Move DOM to outside logic
            setTimeout(() => {
                $('#repairJobsTable_wrapper > .flex').first().appendTo('#dt-controls-top');
                $('#repairJobsTable_wrapper > .flex').last().appendTo('#dt-controls-bottom');
            }, 50);
        },

        reloadTable() {
            if (this.table) {
                this.table.ajax.reload();
            }
        }
    };
}
</script>
<style>
            /* Frozen Table - matching total_stock pattern */
            .frozen-table-container {
                max-height: calc(100vh - 460px);
                min-height: 300px;
                overflow: auto;
                position: relative;
                border: 1px solid rgb(226 232 240);
                border-radius: 0.75rem;
            }
            .dark .frozen-table-container {
                border-color: rgb(30 41 59);
            }
            .frozen-table {
                border-collapse: separate;
                border-spacing: 0;
                width: 100%;
            }
            .frozen-table thead {
                position: sticky;
                top: 0;
                z-index: 20;
            }
            .frozen-table thead th {
                background: rgb(248 250 252);
                color: rgb(100 116 139);
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid rgb(226 232 240);
                white-space: nowrap;
            }
            .dark .frozen-table thead th {
                background: rgb(2 6 23);
                color: rgb(148 163 184);
                border-bottom-color: rgb(30 41 59);
            }
            .frozen-table th.sticky-col,
            .frozen-table td.sticky-col {
                position: sticky;
                left: 0;
                z-index: 10;
            }
            .frozen-table thead th.sticky-col {
                z-index: 30;
                background: rgb(248 250 252);
            }
            .dark .frozen-table thead th.sticky-col {
                background: rgb(2 6 23);
            }
            .frozen-table tbody td.sticky-col {
                background: rgb(255 255 255);
            }
            .dark .frozen-table tbody td.sticky-col {
                background: rgb(15 23 42);
            }
            .frozen-table tbody tr:hover td.sticky-col {
                background: rgb(248 250 252 / 0.8);
            }
            .dark .frozen-table tbody tr:hover td.sticky-col {
                background: rgb(30 41 59 / 0.8);
            }
            .frozen-table th.sticky-col::after,
            .frozen-table td.sticky-col::after {
                content: '';
                position: absolute;
                top: 0; right: -8px; bottom: 0;
                width: 8px;
                background: linear-gradient(to right, rgba(0,0,0,0.06), transparent);
                pointer-events: none;
            }
            .dark .frozen-table th.sticky-col::after,
            .dark .frozen-table td.sticky-col::after {
                background: linear-gradient(to right, rgba(0,0,0,0.25), transparent);
            }
            .frozen-table tbody td {
                padding: 0.625rem 1rem;
                font-size: 0.8125rem;
                color: rgb(51 65 85);
                border-bottom: 1px solid rgb(241 245 249);
                vertical-align: middle;
            }
            .dark .frozen-table tbody td {
                color: rgb(203 213 225);
                border-bottom-color: rgb(30 41 59);
            }
            .frozen-table tbody tr:hover td {
                background: rgb(248 250 252 / 0.5);
            }
            .dark .frozen-table tbody tr:hover td {
                background: rgb(30 41 59 / 0.5);
            }
            
            #repairJobsTable_wrapper { margin-top: 0; }
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.25rem 0.625rem;
                border: 1px solid rgb(226 232 240);
                margin: 0 2px;
                border-radius: 0.5rem;
                font-size: 0.8125rem;
                background: white;
                color: rgb(71 85 105) !important;
                cursor: pointer;
                transition: all 0.15s;
            }
            .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
                background: rgb(30 41 59);
                border-color: rgb(51 65 85);
                color: rgb(148 163 184) !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: rgb(79 70 229) !important;
                color: white !important;
                border-color: rgb(79 70 229) !important;
            }
            .dataTables_wrapper .dataTables_info {
                font-size: 0.75rem;
                color: rgb(100 116 139);
            }
            
    /* DataTables overrides layout for Buttons */
    .dt-buttons .dt-button {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        color: #334155;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        font-weight: 500;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
    }
    .dt-buttons .dt-button:hover {
        background-color: #f8fafc;
    }
    .dark .dt-buttons .dt-button {
        background-color: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    .dark .dt-buttons .dt-button:hover {
        background-color: #334155;
    }
    .dt-button-collection {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        padding: 0.5rem;
        min-width: 200px;
    }
    .dark .dt-button-collection {
        background-color: #1e293b;
        border-color: #334155;
    }
    .dt-button-collection .dt-button {
        width: 100%;
        text-align: left;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dt-button-collection .dt-button.active::after {
        content: '✓';
        color: #4f46e5;
        font-weight: 700;
        margin-left: 0.5rem;
    }
    .dark .dt-button-collection .dt-button.active::after {
        color: #818cf8;
    }
</style>
@endsection
