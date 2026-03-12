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
                <div class="w-full md:w-auto flex-grow">
                    <label for="nomor_polisi_select" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wide">Nomor Polisi</label>
                    <select id="nomor_polisi_select" x-model="nomorPolisiFilter" x-on:change="reloadTable()" class="w-full select2-nomor-polisi">
                        <option></option>
                    </select>
                </div>

                <div class="w-full md:w-auto flex-grow">
                    <label for="nama_customer_select" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wide">Nama Customer</label>
                    <select id="nama_customer_select" x-model="namaCustomerFilter" x-on:change="reloadTable()" class="w-full select2-customer">
                        <option></option>
                    </select>
                </div>

                <div class="w-full md:w-auto flex-grow">
                    <label for="supplier_select" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wide">Vendor / Supplier</label>
                    <select id="supplier_select" x-model="supplierFilter" x-on:change="reloadTable()" class="w-full select2-supplier">
                        <option></option>
                    </select>
                </div>

                <div class="flex-grow flex flex-col md:flex-row gap-4">
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wide">Tanggal Job</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="text" id="tanggal_job_filter"
                                   class="pl-10 w-full text-sm border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5 shadow-sm px-3"
                                   placeholder="Pilih rentang tanggal...">
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-auto mt-4 md:mt-0">
                    <button @click="statusFilter = 'all'; startDate = ''; endDate = ''; nomorPolisiFilter = ''; namaCustomerFilter = ''; supplierFilter = ''; $('#nomor_polisi_select').val(null).trigger('change'); $('#nama_customer_select').val(null).trigger('change'); $('#supplier_select').val(null).trigger('change'); $('#tanggal_job_filter').val(''); reloadTable()" 
                            class="w-full md:w-auto px-6 py-2.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl font-bold uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        {{-- Status Cards - All clickable --}}
        <div class="grid gap-2 mb-6" style="grid-template-columns: repeat(7, 1fr);">
            {{-- Total Jobs --}}
            <button @click="statusFilter = 'all'; reloadTable()"
                    :class="statusFilter === 'all' ? 'ring-2 ring-indigo-500 border-indigo-300 dark:border-indigo-600' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300'"
                    class="bg-white dark:bg-slate-800 border rounded-xl p-2.5 shadow-sm transition-all text-left cursor-pointer group">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5 flex items-center gap-1">
                    <div class="w-4 h-4 rounded bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    Total
                </div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalJobs) }}</div>
            </button>

            {{-- Confirmed --}}
            <button @click="statusFilter = 'confirmed'; reloadTable()"
                    :class="statusFilter === 'confirmed' ? 'ring-2 ring-blue-500 border-blue-300 dark:border-blue-600' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300'"
                    class="bg-white dark:bg-slate-800 border rounded-xl p-2.5 shadow-sm transition-all text-left cursor-pointer group">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5 flex items-center gap-1">
                    <div class="w-4 h-4 rounded bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    Confirmed
                </div>
                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($confirmedJobs) }}</div>
            </button>

            {{-- Under Repair --}}
            <button @click="statusFilter = 'under_repair'; reloadTable()"
                    :class="statusFilter === 'under_repair' ? 'ring-2 ring-amber-500 border-amber-300 dark:border-amber-600' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300'"
                    class="bg-white dark:bg-slate-800 border rounded-xl p-2.5 shadow-sm transition-all text-left cursor-pointer group">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5 flex items-center gap-1">
                    <div class="w-4 h-4 rounded bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    Under Repair
                </div>
                <div class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ number_format($underRepairJobs) }}</div>
            </button>

            {{-- Draft --}}
            <button @click="statusFilter = 'draft'; reloadTable()"
                    :class="statusFilter === 'draft' ? 'ring-2 ring-purple-500 border-purple-300 dark:border-purple-600' : 'border-slate-200 dark:border-slate-700 hover:border-purple-300'"
                    class="bg-white dark:bg-slate-800 border rounded-xl p-2.5 shadow-sm transition-all text-left cursor-pointer group">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5 flex items-center gap-1">
                    <div class="w-4 h-4 rounded bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    Draft
                </div>
                <div class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ number_format($readyJobs) }}</div>
            </button>

            {{-- Done --}}
            <button @click="statusFilter = 'done'; reloadTable()"
                    :class="statusFilter === 'done' ? 'ring-2 ring-green-500 border-green-300 dark:border-green-600' : 'border-slate-200 dark:border-slate-700 hover:border-green-300'"
                    class="bg-white dark:bg-slate-800 border rounded-xl p-2.5 shadow-sm transition-all text-left cursor-pointer group">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5 flex items-center gap-1">
                    <div class="w-4 h-4 rounded bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    Done
                </div>
                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($doneJobs) }}</div>
            </button>

            {{-- On Hold --}}
            <button @click="statusFilter = 'on_hold_repair'; reloadTable()"
                    :class="statusFilter === 'on_hold_repair' ? 'ring-2 ring-orange-500 border-orange-300 dark:border-orange-600' : 'border-slate-200 dark:border-slate-700 hover:border-orange-300'"
                    class="bg-white dark:bg-slate-800 border rounded-xl p-2.5 shadow-sm transition-all text-left cursor-pointer group">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5 flex items-center gap-1">
                    <div class="w-4 h-4 rounded bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    On Hold
                </div>
                <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($onHoldJobs) }}</div>
            </button>

            {{-- Close --}}
            <button @click="statusFilter = 'close'; reloadTable()"
                    :class="statusFilter === 'close' ? 'ring-2 ring-teal-500 border-teal-300 dark:border-teal-600' : 'border-slate-200 dark:border-slate-700 hover:border-teal-300'"
                    class="bg-white dark:bg-slate-800 border rounded-xl p-2.5 shadow-sm transition-all text-left cursor-pointer group">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5 flex items-center gap-1">
                    <div class="w-4 h-4 rounded bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path></svg>
                    </div>
                    Close
                </div>
                <div class="text-lg font-bold text-teal-600 dark:text-teal-400">{{ number_format($toInvoiceJobs) }}</div>
            </button>
        </div>




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
</div>
@endsection

@section('scripts')
<!-- Select2 Dependencies & Styles -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<style>
    /* Base styles (Light mode) */
    .select2-container--default .select2-selection--single {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        height: 2.5rem !important;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155;
        font-size: 0.875rem;
        padding-left: 0.75rem;
        font-weight: 400;
        line-height: normal;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 2.5rem;
        right: 0.5rem;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border-top-width: 0;
        background-color: #ffffff;
        font-size: 0.875rem;
        overflow: hidden;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        background-color: #f8fafc;
        color: #334155;
    }
    .select2-results__option {
        color: #334155;
        padding: 0.5rem 1rem;
    }
    .select2-results__option:hover {
        background-color: #e0e7ff;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
    }
    
    /* Dark mode overrides */
    .dark .select2-container--default .select2-selection--single {
        border-color: #475569;
        background-color: #334155;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #e2e8f0;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8;
    }
    .dark .select2-dropdown {
        border-color: #475569;
        background-color: #334155;
    }
    .dark .select2-search--dropdown .select2-search__field {
        border-color: #475569;
        background-color: #1e293b;
        color: #e2e8f0;
    }
    .dark .select2-results__option {
        color: #cbd5e1;
    }
    .dark .select2-results__option:hover {
        background-color: rgba(49, 46, 129, 0.4);
    }
    .dark .select2-container--default .select2-results__option--selected {
        background-color: #475569;
    }
</style>

<script>
function repairJobsPage() {
    return {
        statusFilter: 'all',
        startDate: '',
        endDate: '',
        nomorPolisiFilter: '',
        namaCustomerFilter: '',
        supplierFilter: '',
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
                        d.nomor_polisi = self.nomorPolisiFilter;
                        d.customer = self.namaCustomerFilter;
                        d.supplier = self.supplierFilter;
                    }
                },
                columns: [
                    {
                        data: 'nomor_job',
                        render: function(data, type, row) {
                            return '<a href="javascript:void(0)" onclick="window.showJobDetails(\'' + data + '\')" class="inline-block flex-col group">' +
                                   '<div class="font-medium text-indigo-600 dark:text-indigo-400 group-hover:underline">' + data + '</div>' +
                                   '<div class="text-xs text-slate-400 group-hover:text-slate-500">' + (row.service_type || '') + '</div>' +
                                   '</a>';
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
                            if (row.state === 'on_hold_repair') {
                                cls = 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400';
                            } else if (row.is_open) {
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
                            return '<a href="javascript:void(0)" onclick="window.filterByVehicle(\'' + data + '\')" class="inline-block flex-col group" title="Click to filter by this vehicle">' +
                                   '<div class="font-medium text-indigo-600 dark:text-indigo-400 group-hover:underline">' + data + '</div>' +
                                   '<div class="text-xs text-slate-400 group-hover:text-slate-500">' + (row.model || '') + '</div>' +
                                   '</a>';
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
                            // Compute grand total = harga_total + harga_pajak
                            var rawTotal = row.harga_total_raw || 0;
                            var rawPajak = row.harga_pajak_raw || 0;
                            var grandTotal = (rawTotal + rawPajak).toLocaleString('id-ID');
                            return '<div class="font-medium text-slate-800 dark:text-slate-200">Rp ' + grandTotal + '</div>' +
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
                dom: '<"flex flex-col md:flex-row items-center justify-between gap-4 mb-3"Bf><"frozen-table-container"rt><"flex flex-col md:flex-row items-center justify-between gap-4 mt-3"ip>',
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
        },

        reloadTable() {
            if (this.table) {
                this.table.ajax.reload();
            }
        }
    };
}

window.filterByVehicle = function(nomorPolisi) {
    // Set the search box value and trigger DataTables search
    $('#repairJobsTable').DataTable().search(nomorPolisi).draw();
    
    // Scroll back to the top where the search box is visible
    setTimeout(() => {
        document.getElementById('repairJobsTable_wrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
};

window.showJobDetails = function(nomorJob) {
    Swal.fire({
        title: 'Loading details...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            $.get("{{ url('/maintenance/repair-job-details') }}/" + nomorJob, function(htmlTemplate) {
                const isDark = document.documentElement.classList.contains('dark');
                Swal.fire({
                    html: htmlTemplate,
                    showCloseButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Close',
                    customClass: {
                        popup: 'rounded-2xl border shadow-xl job-detail-popup',
                        confirmButton: 'px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition-colors',
                        closeButton: 'swal-close-btn'
                    },
                    width: '600px',
                    padding: '2rem',
                    didRender: () => {
                        const popup = Swal.getPopup();
                        const closeBtn = Swal.getCloseButton();
                        if (isDark) {
                            popup.style.backgroundColor = '#0f172a';
                            popup.style.borderColor = '#1e293b';
                            popup.style.color = '#e2e8f0';
                            if (closeBtn) {
                                closeBtn.style.color = '#94a3b8';
                            }
                        } else {
                            popup.style.backgroundColor = '#ffffff';
                            popup.style.borderColor = '#f1f5f9';
                            popup.style.color = '#0f172a';
                            if (closeBtn) {
                                closeBtn.style.color = '#64748b';
                            }
                        }
                    }
                });
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load job details. Transaction might not be synchronized yet.',
                    customClass: { popup: 'rounded-2xl', confirmButton: 'px-6 py-2 bg-slate-800 text-white rounded-xl' }
                });
            });
        }
    });
};

$(document).ready(function() {
    // Select2 Initialization for Nomor Polisi
    $('.select2-nomor-polisi').select2({
        placeholder: "Cari nomor polisi...",
        allowClear: true,
        ajax: {
            url: "{{ route('maintenance.nomor_polisi.search') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, page: params.page };
            },
            processResults: function(data, params) {
                return { results: data };
            },
            cache: true
        }
    }).on('change', function(e) {
        // AlpineJS integration
        let val = $(this).val();
        let alpineData = Alpine.$data(document.querySelector('[x-data="repairJobsPage()"]'));
        alpineData.nomorPolisiFilter = val;
        alpineData.reloadTable();
    });

    // Select2 Initialization for Customer
    $('.select2-customer').select2({
        placeholder: "Cari kode/nama customer...",
        allowClear: true,
        ajax: {
            url: "{{ route('maintenance.nama_customer.search') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, page: params.page };
            },
            processResults: function(data, params) {
                return { results: data };
            },
            cache: true
        }
    }).on('change', function(e) {
        // AlpineJS integration
        let val = $(this).val();
        let alpineData = Alpine.$data(document.querySelector('[x-data="repairJobsPage()"]'));
        alpineData.namaCustomerFilter = val;
        alpineData.reloadTable();
    });

    // Select2 Initialization for Supplier / Vendor
    $('.select2-supplier').select2({
        placeholder: "Cari vendor/supplier...",
        allowClear: true,
        ajax: {
            url: "{{ route('maintenance.supplier.search') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, page: params.page };
            },
            processResults: function(data, params) {
                return { results: data };
            },
            cache: true
        }
    }).on('change', function(e) {
        let val = $(this).val();
        let alpineData = Alpine.$data(document.querySelector('[x-data="repairJobsPage()"]'));
        alpineData.supplierFilter = val;
        alpineData.reloadTable();
    });

    // Date range picker logic
    $('#tanggal_job_filter').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'DD-MM-YYYY'
        }
    });

    $('#tanggal_job_filter').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        
        // AlpineJS integration
        let alpineData = Alpine.$data(document.querySelector('[x-data="repairJobsPage()"]'));
        alpineData.startDate = picker.startDate.format('YYYY-MM-DD');
        alpineData.endDate = picker.endDate.format('YYYY-MM-DD');
        alpineData.reloadTable();
    });

    $('#tanggal_job_filter').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        
        // AlpineJS integration
        let alpineData = Alpine.$data(document.querySelector('[x-data="repairJobsPage()"]'));
        alpineData.startDate = '';
        alpineData.endDate = '';
        alpineData.reloadTable();
    });
});
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
            
            /* Column resize handle */
            .frozen-table thead th {
                position: relative;
                overflow: visible;
            }
            .frozen-table thead th .col-resize-handle {
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: 6px;
                cursor: col-resize;
                z-index: 40;
                background: transparent;
                transition: background 0.15s;
            }
            .frozen-table thead th .col-resize-handle:hover,
            .frozen-table thead th .col-resize-handle.resizing {
                background: rgb(99 102 241 / 0.5);
            }
            .frozen-table thead th .col-resize-handle::after {
                content: '';
                position: absolute;
                right: 2px;
                top: 25%;
                bottom: 25%;
                width: 2px;
                border-radius: 1px;
                background: rgb(148 163 184 / 0.4);
                transition: background 0.15s;
            }
            .frozen-table thead th .col-resize-handle:hover::after,
            .frozen-table thead th .col-resize-handle.resizing::after {
                background: rgb(99 102 241);
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

<script>
// ── Column Resize Logic (Repair Jobs) ────────────────────────────────
(function() {
    function initColumnResize(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var headers = table.querySelectorAll('thead th');
        headers.forEach(function(th) {
            var oldHandle = th.querySelector('.col-resize-handle');
            if (oldHandle) oldHandle.remove();

            var handle = document.createElement('div');
            handle.classList.add('col-resize-handle');
            th.appendChild(handle);

            var startX, startWidth, thEl;

            handle.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                thEl = th;
                startX = e.pageX;
                startWidth = th.offsetWidth;
                handle.classList.add('resizing');

                var overlay = document.createElement('div');
                overlay.id = 'resize-overlay';
                overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;cursor:col-resize;';
                document.body.appendChild(overlay);

                function onMouseMove(e) {
                    var newWidth = startWidth + (e.pageX - startX);
                    if (newWidth < 50) newWidth = 50;
                    thEl.style.width = newWidth + 'px';
                    thEl.style.minWidth = newWidth + 'px';
                }

                function onMouseUp() {
                    handle.classList.remove('resizing');
                    var ov = document.getElementById('resize-overlay');
                    if (ov) ov.remove();
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                }

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
        });
    }

    $(document).ready(function() {
        $('#repairJobsTable').on('draw.dt', function() {
            initColumnResize('repairJobsTable');
        });
        setTimeout(function() { initColumnResize('repairJobsTable'); }, 200);
    });
})();
</script>
@endsection
