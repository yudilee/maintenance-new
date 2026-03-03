@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden theme-transition mb-6">
    <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Report</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Operational Cost Report Filter</p>
    </div>

    <div class="p-6">
        <form action="{{ route('maintenance.dashboard') }}" method="GET" class="max-w-4xl">
            <!-- Plate Number -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nomor Polisi</label>
                <select class="w-full text-sm border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors shadow-sm select2-plate" id="nomor_polisi" name="nomor_polisi">
                    <option></option>
                    @if (request('nomor_polisi'))
                        <option value="{{ request('nomor_polisi') }}" selected>{{ request('nomor_polisi') }}</option>
                    @endif
                </select>
            </div>

            <!-- Customer -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Customer</label>
                <select class="w-full text-sm border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors shadow-sm select2-customer" id="nama_customer" name="nama_customer">
                    <option></option>
                    @if (request('nama_customer'))
                        @php
                            $cust = \App\Models\customer::where('kode_customer', request('nama_customer'))->first();
                        @endphp
                        <option value="{{ request('nama_customer') }}" selected>
                            {{ $cust ? $cust->kode_customer . ' - ' . $cust->nama_customer : request('nama_customer') }}
                        </option>
                    @endif
                </select>
            </div>

            <!-- Date -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal Job</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <input type="text" class="pl-10 w-full text-sm border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors py-2.5 shadow-sm" id="tanggal_job_transaksi" autocomplete="off" placeholder="Pilih rentang tanggal">
                </div>
                <input type="hidden" id="start_date_transaksi" name="start_date_transaksi" value="{{ request('start_date_transaksi') }}">
                <input type="hidden" id="end_date_transaksi" name="end_date_transaksi" value="{{ request('end_date_transaksi') }}">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Contoh: <b>01-01-2025 - 31-12-2025</b> (format: hari-bulan-tahun)</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200 dark:shadow-none flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Search
                </button>
            </div>
        </form>

        @if(isset($vehicleResults) && $vehicleResults->count() > 0)
            <div class="mt-8 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Daftar Mobil untuk Customer: <span class="text-indigo-600 dark:text-indigo-400">{{ \App\Models\customer::where('kode_customer', request('nama_customer'))->value('nama_customer') ?? request('nama_customer') }}</span></h3>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table id="vehicleListTable" class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-900/50 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 font-bold">Nomor Polisi</th>
                                <th class="px-4 py-3 font-bold">Nomor Chassis</th>
                                <th class="px-4 py-3 font-bold">Model</th>
                                <th class="px-4 py-3 font-bold whitespace-normal">Tahun Pembuatan</th>
                                <th class="px-4 py-3 font-bold">Warna</th>
                                <th class="px-4 py-3 font-bold">Nomor Mesin</th>
                                <th class="px-4 py-3 font-bold whitespace-normal">Tanggal Pembelian</th>
                                <th class="px-4 py-3 font-bold">Kode Supplier</th>
                                <th class="px-4 py-3 font-bold text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($vehicleResults as $v)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $v->nomor_polisi ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $v->nomor_chassis ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $v->model ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $v->tahun_pembuatan ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $v->warna ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $v->nomor_mesin ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $v->tanggal_pembelian ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $v->kode_sup ?: '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('maintenance.vehicle.transactions', [
                                        'nama_customer' => request('nama_customer'),
                                        'nomor_polisi' => $v->nomor_polisi,
                                        'start_date_transaksi' => request('start_date_transaksi'),
                                        'end_date_transaksi' => request('end_date_transaksi')
                                    ]) }}" class="inline-flex items-center px-4 py-2 bg-cyan-500 text-white text-xs font-medium rounded-lg hover:bg-cyan-600 transition-colors shadow-sm">
                                        Lihat Transaksi
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="mt-6 flex justify-start">
                        <a href="{{ route('maintenance.vehicle.transactions', [
                                        'nama_customer' => request('nama_customer'),
                                        'start_date_transaksi' => request('start_date_transaksi'),
                                        'end_date_transaksi' => request('end_date_transaksi')
                                    ]) }}" class="px-6 py-3 bg-slate-600 text-white text-sm font-medium rounded-xl hover:bg-slate-700 transition-colors shadow-sm inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            Show All Transactions for {{ \App\Models\customer::where('kode_customer', request('nama_customer'))->value('nama_customer') ?? request('nama_customer') }}
                        </a>
                    </div>
                </div>
            </div>
        @elseif(isset($vehicleResults) && request('nama_customer'))
            <div class="mt-8 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700/50 rounded-2xl p-6 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-amber-800 dark:text-amber-500 mb-1">No Vehicles Found</h3>
                <p class="text-amber-700 dark:text-amber-400/80">No vehicles or maintenance records found for this customer within the selected dates.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<!-- Export Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<style>
.select2-container--default .select2-selection--single {
    @apply border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 h-10 rounded-xl flex items-center shadow-sm;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    @apply text-slate-700 dark:text-slate-200 text-sm pl-3 font-normal;
    line-height: normal;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    @apply h-10 right-2;
}
.select2-dropdown {
    @apply border-slate-200 dark:border-slate-600 rounded-xl shadow-lg border-t-0 bg-white dark:bg-slate-700 text-sm;
    overflow: hidden;
}
.select2-search--dropdown .select2-search__field {
    @apply border-slate-200 dark:border-slate-600 rounded-lg text-sm bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200;
}
.select2-results__option {
    @apply text-slate-700 dark:text-slate-300 px-4 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/40;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    @apply bg-indigo-600 text-white;
}
</style>

<script>
$(document).ready(function() {
    $('.select2-plate').select2({
        placeholder: 'Cari nomor polisi...',
        allowClear: true,
        ajax: {
            url: "{{ route('maintenance.nomor_polisi.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data };
            },
            cache: true
        }
    });

    $('.select2-customer').select2({
        placeholder: 'Cari kode/nama customer...',
        allowClear: true,
        ajax: {
            url: "{{ route('maintenance.nama_customer.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data };
            },
            cache: true
        }
    });

    $('#tanggal_job_transaksi').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'DD-MM-YYYY'
        }
    });

    $('#tanggal_job_transaksi').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        $('#start_date_transaksi').val(picker.startDate.format('YYYY-MM-DD'));
        $('#end_date_transaksi').val(picker.endDate.format('YYYY-MM-DD'));
    });

    $('#tanggal_job_transaksi').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('#start_date_transaksi').val('');
        $('#end_date_transaksi').val('');
    });

    if ($('#vehicleListTable').length > 0) {
        $('#vehicleListTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            "language": {
                "search": "",
                "searchPlaceholder": "Search..."
            },
            "dom": '<"flex flex-col md:flex-row items-center justify-between gap-4 mb-4"Bf>rt<"flex flex-col md:flex-row items-center justify-between gap-4 mt-4"ip>',
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>Excel</span>',
                    className: 'btn-export'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>PDF</span>',
                    className: 'btn-export'
                }
            ],
            drawCallback: function() {
                $('.dataTables_filter input').addClass('px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500');
            }
        });
    }
});
</script>

<style>
/* Adjust DataTables export buttons to look like the screenshot */
.dt-buttons .btn-export {
    background-color: #64748b !important;
    border-color: #475569 !important;
    color: white !important;
    padding: 0.5rem 1rem !important;
    border-radius: 0.5rem !important;
    font-size: 0.875rem !important;
    margin-right: 0.5rem !important;
}
.dt-buttons .btn-export:hover {
    background-color: #475569 !important;
}

/* Pagination styles */
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
</style>
@endsection
