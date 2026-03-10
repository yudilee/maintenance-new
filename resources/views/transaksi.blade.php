@extends('layouts.app')

@section('content')
<div x-data="transaksiPage()" x-init="initTable()" class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden theme-transition mb-6">
    <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                Maintenance History
                <span class="text-sm font-normal text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 px-3 py-1 rounded-lg ml-3">
                    {{ $nomor_polisi ? $nomor_polisi . ' - ' : '' }}{{ $nama_customer ? $nama_customer : 'All Vehicles' }}
                </span>
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Operational Cost Report</p>
        </div>
    </div>

    <div class="p-6">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 md:p-6 mb-6 flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="nomor_polisi_select" class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wider">Nomor Polisi</label>
                <select id="nomor_polisi_select" x-model="nomorPolisiFilter" x-on:change="reloadTable()" class="w-full select2-nomor-polisi">
                    <option></option>
                </select>
            </div>

            <div class="flex-1">
                <label for="nama_customer_select" class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wider">Nama Customer</label>
                <select id="nama_customer_select" x-model="namaCustomerFilter" x-on:change="reloadTable()" class="w-full select2-customer">
                    <option></option>
                </select>
            </div>
            
            <div class="flex-1">
                <label for="tanggal_job_transaksi" class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wider">Tanggal Job</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <input type="text" class="pl-9 w-full text-sm border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors h-10 shadow-sm" id="tanggal_job_transaksi" autocomplete="off" placeholder="Select date range...">
                </div>
            </div>
            
            <div class="flex items-end">
                <button @click="startDate = ''; endDate = ''; nomorPolisiFilter = ''; namaCustomerFilter = ''; $('#nomor_polisi_select').val(null).trigger('change'); $('#nama_customer_select').val(null).trigger('change'); $('#tanggal_job_transaksi').val(''); reloadTable()" 
                        class="h-[40px] px-6 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-xl font-bold uppercase tracking-wider text-sm transition-colors flex items-center gap-2 whitespace-nowrap shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reset
                </button>
            </div>
        </div>

        @if($mobilDetail)
        <div class="bg-white dark:bg-slate-800 border-l-4 border-indigo-500 rounded-2xl shadow-sm mb-8 overflow-hidden">
            <div class="bg-slate-50 dark:bg-slate-800/80 px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider text-sm">Detail Mobil</h3>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div class="space-y-3">
                    <div class="flex justify-between md:block"><span class="text-slate-500 font-medium">Nomor Polisi:</span> <span class="font-bold text-slate-800 dark:text-slate-200 md:ml-2">{{ $mobilDetail->nomor_polisi ?: '-' }}</span></div>
                    <div class="flex justify-between md:block"><span class="text-slate-500 font-medium">Tahun Pembuatan:</span> <span class="font-bold text-slate-800 dark:text-slate-200 md:ml-2">{{ $mobilDetail->tahun_pembuatan ?: '-' }}</span></div>
                    <div class="flex justify-between md:block"><span class="text-slate-500 font-medium">Tanggal Pembelian:</span> <span class="font-bold text-slate-800 dark:text-slate-200 md:ml-2">{{ $mobilDetail->tanggal_pembelian ?: '-' }}</span></div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between md:block"><span class="text-slate-500 font-medium">Nomor Chassis:</span> <span class="font-bold text-slate-800 dark:text-slate-200 md:ml-2">{{ $mobilDetail->nomor_chassis ?: '-' }}</span></div>
                    <div class="flex justify-between md:block"><span class="text-slate-500 font-medium">Warna:</span> <span class="font-bold text-slate-800 dark:text-slate-200 md:ml-2">{{ $mobilDetail->warna ?: '-' }}</span></div>
                    <div class="flex justify-between md:block"><span class="text-slate-500 font-medium">Kode Supplier:</span> <span class="font-bold text-slate-800 dark:text-slate-200 md:ml-2">{{ $mobilDetail->kode_sup ?: '-' }}</span></div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between md:block"><span class="text-slate-500 font-medium">Model:</span> <span class="font-bold text-slate-800 dark:text-slate-200 md:ml-2">{{ $mobilDetail->model ?: '-' }}</span></div>
                    <div class="flex justify-between md:block"><span class="text-slate-500 font-medium">Nomor Mesin:</span> <span class="font-bold text-slate-800 dark:text-slate-200 md:ml-2">{{ $mobilDetail->nomor_mesin ?: '-' }}</span></div>
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm hover:border-indigo-300 transition-colors">
                <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Maintenance History Cost</div>
                <div id="kpi-cost" class="text-2xl font-black text-rose-600 dark:text-rose-400">Rp {{ number_format($grandTotals['hargaTotal'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm hover:border-indigo-300 transition-colors">
                <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Tax (PPN)</div>
                <div id="kpi-tax" class="text-2xl font-black text-slate-500 dark:text-slate-400">Rp {{ number_format($grandTotals['hargaPajak'], 0, ',', '.') }}</div>
            </div>
        </div>

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
            /* Sticky Header Row */
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
            /* Sticky First Column */
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
            /* Shadow effect for frozen column on scroll */
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
            /* Body cells */
            .frozen-table tbody td {
                padding: 0.625rem 1rem;
                font-size: 0.8125rem;
                color: rgb(51 65 85);
                border-bottom: 1px solid rgb(241 245 249);
                vertical-align: top;
                max-width: 260px;
                word-break: break-word;
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
            /* Detail rows (sub-lines) */
            .frozen-table tbody tr.is-detail-row td {
                background: rgb(248 250 252);
                color: rgb(100 116 139);
                font-size: 0.75rem;
                border-bottom-color: rgb(241 245 249);
            }
            .dark .frozen-table tbody tr.is-detail-row td {
                background: rgb(15 23 42 / 0.6);
                color: rgb(148 163 184);
            }
            .frozen-table tbody tr.is-detail-row td.sticky-col {
                background: rgb(248 250 252);
            }
            .dark .frozen-table tbody tr.is-detail-row td.sticky-col {
                background: rgb(15 23 42);
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

            /* DataTables Buttons overrides */
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
                padding: 0.375rem 0.75rem;
                border-radius: 0.5rem;
                font-size: 0.8125rem;
            }
            .dt-button-collection .dt-button:hover {
                background-color: rgb(241 245 249) !important;
            }
            .dark .dt-button-collection .dt-button:hover {
                background-color: rgb(51 65 85) !important;
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

            /* DataTables overrides */
            #transaksiTable_wrapper { padding: 1rem 0 0; }
            .dataTables_wrapper .dataTables_length select,
            .dataTables_wrapper .dataTables_filter input {
                border: 1px solid rgb(226 232 240);
                border-radius: 0.5rem;
                font-size: 0.8125rem;
                padding: 0.375rem 0.75rem;
                margin-left: 0.5rem;
            }
            .dark .dataTables_wrapper .dataTables_length select,
            .dark .dataTables_wrapper .dataTables_filter input {
                background: rgb(30 41 59);
                border-color: rgb(51 65 85);
                color: rgb(203 213 225);
            }
            .dataTables_wrapper .dt-buttons .btn {
                background: white;
                border: 1px solid rgb(226 232 240);
                color: rgb(71 85 105);
                padding: 0.375rem 0.75rem;
                border-radius: 0.5rem;
                font-size: 0.8125rem;
                font-weight: 500;
                margin-right: 0.375rem;
                box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
                transition: all 0.15s;
            }
            .dark .dataTables_wrapper .dt-buttons .btn {
                background: rgb(30 41 59);
                border-color: rgb(51 65 85);
                color: rgb(148 163 184);
            }
            .dataTables_wrapper .dt-buttons .btn:hover {
                background: rgb(241 245 249);
            }
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
                padding-top: 1rem;
            }
        </style>

        <!-- DT controls rendered here (outside scroll container) -->
        <div id="dt-controls-top" class="mb-3"></div>

        <div class="frozen-table-container" id="mainTableContainer">
            <table id="transaksiTable" class="frozen-table" style="min-width: 1100px;">
                <thead>
                    <tr>
                        <th class="sticky-col" style="min-width:200px;">Nomor Job</th>
                        <th style="min-width:100px;">Tanggal Job</th>
                        <th style="min-width:80px;">Posisi KM</th>
                        <th style="min-width:140px;">Maintenance/Service</th>
                        <th style="min-width:200px;">Deskripsi</th>
                        <th style="min-width:60px;">Jumlah</th>
                        <th style="min-width:100px;">Harga</th>
                        <th style="min-width:110px;">Harga Total</th>
                        <th style="min-width:110px;">Harga Pajak</th>
                        <th style="min-width:220px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- DT info/pagination rendered here (outside scroll container) -->
        <div id="dt-controls-bottom" class="mt-3"></div>
    </div>
</div>
@endsection

@section('scripts')
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
    
    /* DateRangePicker Dark Mode overrides */
    .dark .daterangepicker {
        background-color: #1e293b;
        border-color: #475569;
        color: #e2e8f0;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .dark .daterangepicker .calendar-table {
        background-color: #1e293b;
        border-color: #1e293b;
    }
    .dark .daterangepicker .calendar-table .next span, 
    .dark .daterangepicker .calendar-table .prev span {
        border-color: #cbd5e1;
    }
    .dark .daterangepicker td.off, 
    .dark .daterangepicker td.off.in-range, 
    .dark .daterangepicker td.off.start-date, 
    .dark .daterangepicker td.off.end-date {
        background-color: #0f172a;
        border-color: transparent;
        color: #475569;
    }
    .dark .daterangepicker td.available:hover, 
    .dark .daterangepicker th.available:hover {
        background-color: #334155;
        color: #f8fafc;
    }
    .dark .daterangepicker td.in-range {
        background-color: rgba(79, 70, 229, 0.2);
        color: #e2e8f0;
    }
    .dark .daterangepicker td.active, 
    .dark .daterangepicker td.active:hover {
        background-color: #4f46e5;
        color: #ffffff;
    }
    .dark .daterangepicker .drp-buttons {
        border-top-color: #475569;
    }
    .dark .daterangepicker .drp-buttons .btn {
        font-weight: 500;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
    }
    .dark .daterangepicker .drp-buttons .btn-primary {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
    .dark .daterangepicker .drp-buttons .btn-default {
        background-color: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    .dark .daterangepicker select.monthselect, 
    .dark .daterangepicker select.yearselect {
        background-color: #334155;
        border-color: #475569;
        color: #e2e8f0;
        border-radius: 0.375rem;
        padding: 2px 4px;
    }
    .dark .daterangepicker:before {
        border-bottom-color: #475569 !important;
    }
    .dark .daterangepicker:after {
        border-bottom-color: #1e293b !important;
    }
    .dark .daterangepicker.drop-up:before {
        border-bottom-color: transparent !important;
        border-top-color: #475569 !important;
    }
    .dark .daterangepicker.drop-up:after {
        border-bottom-color: transparent !important;
        border-top-color: #1e293b !important;
    }
    </style>

    <script>
        function transaksiPage() {
            return {
                nomorPolisiFilter: '{{ $nomor_polisi ?? "" }}',
                namaCustomerFilter: '{{ $nama_customer ?? "" }}',
                startDate: '{{ request("start_date_transaksi") ?? "" }}',
                endDate: '{{ request("end_date_transaksi") ?? "" }}',
                table: null,

                initTable() {
                    const self = this;
                    
                    var grandTotal = {{ $grandTotals['grandTotal'] }};
                    var hargaTotal = {{ $grandTotals['hargaTotal'] }};
                    var hargaPajak = {{ $grandTotals['hargaPajak'] }};

                    // Initialize DataTable with server-side processing
                    this.table = $('#transaksiTable').DataTable({
                        "processing": true,
                        "serverSide": true,
                        ajax: {
                            url: "{{ route('maintenance.vehicle.transactions.data') }}",
                            "data": function(d) {
                                d.nama_customer = self.namaCustomerFilter;
                                d.nomor_polisi = self.nomorPolisiFilter;
                                d.start_date_transaksi = self.startDate;
                                d.end_date_transaksi = self.endDate;
                            },
                            dataSrc: function(json) {
                                // Update KPI cards from AJAX response
                                if (json.grandTotals) {
                                    $('#kpi-cost').text('Rp ' + json.grandTotals.grandTotal.toLocaleString('id-ID'));
                                    $('#kpi-tax').text('Rp ' + json.grandTotals.hargaPajak.toLocaleString('id-ID'));
                                }
                                return json.data;
                            }
                        },
                        "columns": [{
                                "data": "nomor_job"
                            },
                    {
                        "data": "tanggal_job"
                    },
                    {
                        "data": "posisi_km"
                    },
                    {
                        "data": "maintenance_service"
                    },
                    {
                        "data": "deskripsi"
                    },
                    {
                        "data": "jumlah"
                    },
                    {
                        "data": "harga"
                    },
                    {
                        "data": "harga_total",
                        "render": function(data, type, row) {
                            var base = (data === '' || data === null) ? '' : (typeof data ===
                                'number' ? data.toLocaleString('id-ID') : data);
                            if (row.workshop_harent) {
                                return base + ' - ' + row.workshop_harent;
                            }
                            return base;
                        }
                    },
                    {
                        "data": "harga_pajak"
                    },
                    {
                        "data": "keterangan"
                    }
                ],
                "pageLength": 50,
                "lengthMenu": [
                    [25, 50, 100, 200],
                    [25, 50, 100, 200]
                ],
                "order": [],
                "searching": true,
                "ordering": false,
                "columnDefs": [],
                "createdRow": function(row, data, dataIndex) {
                    if (data.is_detail) {
                        $(row).addClass('is-detail-row');
                        $('td:eq(0)', row).addClass('sticky-col');
                    } else {
                        $('td:eq(0)', row).addClass('sticky-col');
                    }
                },
                "dom": '<"#dt-controls-top"Blf>rt<"#dt-controls-bottom"ip>',
                "buttons": [{
                        extend: 'colvis',
                        text: '<span class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>Show/Hide Columns</span>'
                    },
                    {
                        text: 'Export Excel (All Data)',
                        action: function(e, dt, button, config) {
                            exportAllToExcel();
                        }
                    },
                    {
                        text: 'Export PDF (All Data)',
                        action: function(e, dt, button, config) {
                            exportAllToPDF();
                        }
                    }
                ]
            });

            table.buttons().container().appendTo('#transaksiTable_wrapper .col-md-6:eq(0)');
        },

        reloadTable() {
            if (this.table) {
                this.table.ajax.reload();
            }
        }
    }
}

$(document).ready(function() {
    // Export ALL data functions
            function exportAllToExcel() {
                let alpineData = Alpine.$data(document.querySelector('[x-data="transaksiPage()"]'));
                $.ajax({
                    url: "{{ route('maintenance.vehicle.transactions.export') }}",
                    data: {
                        nama_customer: alpineData.namaCustomerFilter,
                        nomor_polisi: alpineData.nomorPolisiFilter,
                        start_date_transaksi: alpineData.startDate,
                        end_date_transaksi: alpineData.endDate
                    },
                    success: function(response) {
                        var wb = XLSX.utils.book_new();
                        var wsData = [
                            ['Nomor Job', 'Tanggal Job', 'Posisi KM',
                                'Maintenance/Service',
                                'Deskripsi',
                                'Jumlah', 'Harga', 'Harga Total', 'Harga Pajak', 'Keterangan'
                            ]
                        ];

                        response.data.forEach(function(row) {
                            // Format values to match view display
                            var hargaFormatted = row.harga === '-' ? '-' : (typeof row.harga ===
                                'number' ? row.harga.toLocaleString('id-ID') : row.harga);
                            var hargaTotalFormatted = row.harga_total === '' ? '' : (typeof row
                                .harga_total === 'number' ? row.harga_total.toLocaleString(
                                    'id-ID') : row.harga_total);
                            if (row.workshop_harent) {
                                hargaTotalFormatted += ' - ' + row.workshop_harent;
                            }
                            var hargaPajakFormatted = row.harga_pajak === '' ? '' : (typeof row
                                .harga_pajak === 'number' ? row.harga_pajak.toLocaleString(
                                    'id-ID') : row.harga_pajak);

                            wsData.push([
                                row.nomor_job,
                                row.tanggal_job,
                                row.posisi_km,
                                row.maintenance_service || '',
                                row.deskripsi,
                                row.jumlah,
                                hargaFormatted,
                                hargaTotalFormatted,
                                hargaPajakFormatted,
                                row.keterangan
                            ]);
                        });

                        wsData.push(['', '', '', '', '', '', '', '', response.hargaTotal
                            .toLocaleString(
                                'id-ID'), response
                            .hargaPajak.toLocaleString('id-ID'), 'GRAND TOTAL: ' + response
                            .grandTotal.toLocaleString('id-ID')
                        ]);

                        var ws = XLSX.utils.aoa_to_sheet(wsData);

                        // Set column widths to match view
                        ws['!cols'] = [{
                                wch: 30
                            }, // Nomor Job
                            {
                                wch: 12
                            }, // Tanggal Job
                            {
                                wch: 10
                            }, // Posisi KM
                            {
                                wch: 20
                            }, // Maintenance/Service
                            {
                                wch: 40
                            }, // Deskripsi
                            {
                                wch: 10
                            }, // Jumlah
                            {
                                wch: 15
                            }, // Harga
                            {
                                wch: 15
                            }, // Harga Total
                            {
                                wch: 15
                            }, // Harga Pajak
                            {
                                wch: 30
                            } // Keterangan
                        ];

                        XLSX.utils.book_append_sheet(wb, ws, "Transactions");
                        XLSX.writeFile(wb, "transaksi_" + response.customer + ".xlsx");
                    },
                    error: function() {
                        Toast.fire({
                            icon: 'error',
                            title: 'Error exporting data. Please try again.'
                        });
                    }
                });
            }

            function exportAllToPDF() {
                let alpineData = Alpine.$data(document.querySelector('[x-data="transaksiPage()"]'));
                $.ajax({
                    url: "{{ route('maintenance.vehicle.transactions.export') }}",
                    data: {
                        nama_customer: alpineData.namaCustomerFilter,
                        nomor_polisi: alpineData.nomorPolisiFilter,
                        start_date_transaksi: alpineData.startDate,
                        end_date_transaksi: alpineData.endDate
                    },
                    success: function(response) {
                        var docDefinition = {
                            pageOrientation: 'landscape',
                            pageSize: 'A3',
                            pageMargins: [8, 25, 8, 25],
                            content: [{
                                    text: 'Data Report Transaksi',
                                    style: 'header'
                                },
                                {
                                    text: customerDetail,
                                    margin: [0, 0, 0, 8]
                                },
                                {
                                    table: {
                                        headerRows: 1,
                                        widths: [100, 50, 40, 70, 60, 90, 35, 60, 70,
                                            60, 100
                                        ],
                                        body: [
                                            [
                                                {text: 'Nomor Job', style: 'tableHeader'},
                                                {text: 'Tgl Job', style: 'tableHeader'},
                                                {text: 'Pos KM', style: 'tableHeader'},
                                                {text: 'Maintenance/Service', style: 'tableHeader'},
                                                {text: 'Deskripsi', style: 'tableHeader'},
                                                {text: 'Jumlah', style: 'tableHeader'},
                                                {text: 'Harga', style: 'tableHeader'},
                                                {text: 'Harga Total', style: 'tableHeader'},
                                                {text: 'Harga Pajak', style: 'tableHeader'},
                                                {text: 'Keterangan', style: 'tableHeader'}
                                            ]
                                        ].concat(response.data.map(function(row) {
                                            // Format values to match view display
                                            var hargaFormatted = row.harga === '-' ?
                                                '-' : (typeof row.harga ===
                                                    'number' ? row.harga
                                                    .toLocaleString('id-ID') : row
                                                    .harga);
                                            var hargaTotalFormatted = row
                                                .harga_total === '' ? '' : (
                                                    typeof row.harga_total ===
                                                    'number' ? row.harga_total
                                                    .toLocaleString('id-ID') : row
                                                    .harga_total);
                                            if (row.workshop_harent) {
                                                hargaTotalFormatted += ' - ' + row
                                                    .workshop_harent;
                                            }
                                            var hargaPajakFormatted = row
                                                .harga_pajak === '' ? '' : (
                                                    typeof row.harga_pajak ===
                                                    'number' ? row.harga_pajak
                                                    .toLocaleString('id-ID') : row
                                                    .harga_pajak);

                                            return [{
                                                    text: row.nomor_job || '',
                                                    fillColor: null
                                                },
                                                {
                                                    text: row.tanggal_job || '',
                                                    fillColor: null
                                                },
                                                {
                                                    text: row.posisi_km || '',
                                                    fillColor: null
                                                },
                                                {
                                                    text: row
                                                        .maintenance_service ||
                                                        '',
                                                    fillColor: null
                                                },
                                                {
                                                    text: row.deskripsi || '',
                                                    fillColor: null
                                                },
                                                {
                                                    text: row.jumlah || '',
                                                    fillColor: null
                                                },
                                                {
                                                    text: hargaFormatted,
                                                    fillColor: null
                                                },
                                                {
                                                    text: hargaTotalFormatted,
                                                    fillColor: null
                                                },
                                                {
                                                    text: hargaPajakFormatted,
                                                    fillColor: null
                                                },
                                                {
                                                    text: row.keterangan || '',
                                                    fillColor: null
                                                }
                                            ];
                                        })).concat([
                                            [
                                                '', '', '', '', '', '', '', '', 
                                                response.hargaTotal ? response
                                                .hargaTotal.toLocaleString('id-ID') :
                                                '',
                                                response.hargaPajak ? response
                                                .hargaPajak.toLocaleString('id-ID') :
                                                '',
                                                'GRAND TOTAL: ' + (response.grandTotal ?
                                                    response.grandTotal.toLocaleString(
                                                        'id-ID') : '')
                                            ]
                                        ])
                                    },
                                    layout: {
                                        hLineWidth: function(i) { return i === 0 || i === 1 ? 1 : 0.5; },
                                        vLineWidth: function() { return 0; },
                                        hLineColor: function(i) { return i === 0 || i === 1 ? '#4F46E5' : '#e2e8f0'; },
                                        fillColor: function(rowIndex) {
                                            if (rowIndex === 0) return '#4F46E5'; // indigo header
                                            // Footer row
                                            var totalRows = response.data.length + 1;
                                            if (rowIndex === totalRows) return '#EEF2FF';
                                            return rowIndex % 2 === 0 ? '#F8FAFC' : null;
                                        }
                                    }
                                }
                            ],
                            styles: {
                                header: {
                                    fontSize: 15,
                                    bold: true,
                                    color: '#1E293B',
                                    margin: [0, 0, 0, 4]
                                },
                                tableHeader: {
                                    bold: true,
                                    fontSize: 8,
                                    color: 'white',
                                    fillColor: '#4F46E5',
                                    alignment: 'left'
                                },
                                tableFooter: {
                                    bold: true,
                                    fontSize: 8,
                                    color: '#4F46E5',
                                    fillColor: '#EEF2FF'
                                }
                            },
                            defaultStyle: {
                                fontSize: 7.5,
                                color: '#334155'
                            }
                        };
                        pdfMake.createPdf(docDefinition).download('transaksi_' + response.customer +
                            '.pdf');
                    },
                    error: function() {
                        Toast.fire({
                            icon: 'error',
                            title: 'Error exporting data. Please try again.'
                        });
                    }
                });
            }

            // Select2 for Nomor Polisi
            $('.select2-nomor-polisi').select2({
                placeholder: 'Cari nomor polisi...',
                allowClear: true,
                ajax: {
                    url: "{{ route('maintenance.nomor_polisi.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            }).on('change', function(e) {
                let alpineData = Alpine.$data(document.querySelector('[x-data="transaksiPage()"]'));
                alpineData.nomorPolisiFilter = $(this).val();
                alpineData.reloadTable();
            });

            // Select2 for Customer
            $('.select2-customer').select2({
                placeholder: 'Cari kode/nama customer...',
                allowClear: true,
                ajax: {
                    url: "{{ route('maintenance.nama_customer.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            }).on('change', function(e) {
                let alpineData = Alpine.$data(document.querySelector('[x-data="transaksiPage()"]'));
                alpineData.namaCustomerFilter = $(this).val();
                alpineData.reloadTable();
            });

            // Date range picker logic
            $('#tanggal_job_transaksi').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'DD-MM-YYYY'
                }
            });

            @if(request('start_date_transaksi') && request('end_date_transaksi'))
                $('#tanggal_job_transaksi').val("{{ \Carbon\Carbon::parse(request('start_date_transaksi'))->format('d-m-Y') }} - {{ \Carbon\Carbon::parse(request('end_date_transaksi'))->format('d-m-Y') }}");
            @endif

            $('#tanggal_job_transaksi').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
                let alpineData = Alpine.$data(document.querySelector('[x-data="transaksiPage()"]'));
                alpineData.startDate = picker.startDate.format('YYYY-MM-DD');
                alpineData.endDate = picker.endDate.format('YYYY-MM-DD');
                alpineData.reloadTable();
            });

            $('#tanggal_job_transaksi').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                let alpineData = Alpine.$data(document.querySelector('[x-data="transaksiPage()"]'));
                alpineData.startDate = '';
                alpineData.endDate = '';
                alpineData.reloadTable();
            });
        });
    </script>

    <script>
    // ── Column Resize Logic ──────────────────────────────────────────────
    (function() {
        function initColumnResize(tableId) {
            var table = document.getElementById(tableId);
            if (!table) return;

            var headers = table.querySelectorAll('thead th');
            headers.forEach(function(th) {
                // Remove any old handles first
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

        // Initialize after DataTable draws
        $(document).ready(function() {
            $('#transaksiTable').on('draw.dt', function() {
                initColumnResize('transaksiTable');
            });
            // Initial call
            setTimeout(function() { initColumnResize('transaksiTable'); }, 200);
        });
    })();
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
@endsection
