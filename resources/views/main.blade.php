@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden theme-transition mb-6">
    <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Report</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Operational Cost Report Filter</p>
    </div>

    <div class="p-6">
        <form action="{{ route('maintenance.vehicle.transactions') }}" method="GET" class="max-w-4xl">
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
});
</script>
@endsection
