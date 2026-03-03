<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('admin/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/dist/css/select2/select2.min.css') }}">

    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet"
        href="{{ asset('admin/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        html {
            font-size: clamp(12px, 1.2vw, 16px);
        }

        .content-wrapper,
        .main-footer,
        .main-header {
            margin-left: 0 !important;
        }

        .wrapper {
            margin-left: 0 !important;
        }

        @media (max-width: 600px) {

            table,
            th,
            td {
                font-size: 10px !important;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home') }}" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('odoo.settings') }}" class="nav-link">Odoo API</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                {{-- <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="fas fa-search"></i>
                    </a>
                    <div class="navbar-search-block">
                        <form class="form-inline">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search"
                                    aria-label="Search">
                                <div class="input-group-append">
                                    <button class="btn btn-navbar" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li> --}}

                <!-- Messages Dropdown Menu -->
                {{-- <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown">{{ Auth::user()->name }}</a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="{{ route('logout') }}" class="dropdown-item">
                            <p class="text-sm">Log Out</p>
                        </a>
                        <div class="dropdown-divider"></div>
                    </div>
                </li> --}}
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    {{-- <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a> --}}
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">15 Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new messages
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> 8 friend requests
                            <span class="float-right text-muted text-sm">12 hours</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-file mr-2"></i> 3 new reports
                            <span class="float-right text-muted text-sm">2 days</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                    </div>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#"
                        role="button">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li> --}}
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        {{-- <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="brand-link">
                <img src="{{ asset('admin/dist/img/GIRH.png') }}" alt="AdminLTE Logo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Hartono Raya Motor</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                    </div>
                </div>
                <br>
            </div>
        </aside> --}}

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Blank Page</li> --}}
                            </ol>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                @yield('content')
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('admin/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('admin/dist/js/select2/select2.min.js') }}"></script>

    <script src="{{ asset('admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
    </script>
</body>
@yield('scripts')
<script>
    function hasRealRows(tableId) {
        var $rows = $(tableId + ' tbody tr');
        if ($rows.length === 0) return false;
        // Check if the only row is a "No data found" row
        if ($rows.length === 1 && $rows.first().find('td').length === 1 &&
            $rows.first().find('td').text().toLowerCase().includes('no data')) {
            return false;
        }
        return true;
    }

    $(document).ready(function() {
        // Prepare mobil detail for export
        /*var mobilDetail = '';
        @if (isset($mobilDetail) && $mobilDetail)
            mobilDetail = `
Nomor Polisi: {{ $mobilDetail->nomor_polisi }}
Nomor Chassis: {{ $mobilDetail->nomor_chassis }}
Model: {{ $mobilDetail->model }}
Tahun Pembuatan: {{ $mobilDetail->tahun_pembuatan }}
Warna: {{ $mobilDetail->warna }}
Nomor Mesin: {{ $mobilDetail->nomor_mesin }}
Tanggal Pembelian: {{ $mobilDetail->tanggal_pembelian }}
Kode Supplier: {{ $mobilDetail->kode_sup }}
        `;
        @endif

        // DataTable for #example2 (transactions)
        if ($('#example2').length && hasRealRows('#example2')) {
            var table = $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": false,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "pageLength": 10,
                "buttons": [{
                        extend: 'colvis',
                        text: 'Show/Hide Columns'
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Data Report',
                        messageTop: mobilDetail.replace(/\n/g, '\r\n')
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'Data Report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 5, 6, 7, 8, 9,
                                10
                            ] // Only export specific columns (exclude 3,4,11)
                        },
                        customize: function(doc) {
                            if (mobilDetail) {
                                doc.content.splice(0, 0, {
                                    text: mobilDetail,
                                    margin: [0, 0, 0, 12],
                                    fontSize: 10
                                });
                            }
                            if (doc.content[1] && doc.content[1].table && doc.content[1].table
                                .body && doc.content[1].table.body.length) {
                                // Set specific column widths instead of equal widths
                                doc.content[1].table.widths = [
                                    '12%', // Nomor Job
                                    '10%', // Tanggal Job
                                    '8%', // Posisi KM
                                    '30%', // Deskripsi
                                    '6%', // Jumlah
                                    '12%', // Harga
									'12%', // Harga Total
                                    '8%' // Keterangan
                                ];

                                // Process each row for text truncation and styling
                                doc.content[1].table.body.forEach(function(row, rowIndex) {
                                    row.forEach(function(cell, colIndex) {
                                        // Style header row
                                        if (rowIndex === 0) {
                                            if (typeof cell === 'object') {
                                                cell.fillColor = '#3498db';
                                                cell.color = 'white';
                                                cell.bold = true;
                                                cell.fontSize = 9;
                                            }
                                        } else {
                                            // Style data rows
                                            var cellText = (typeof cell ===
                                                'object') ? cell.text : cell;

                                            // Truncate long text in specific columns
                                            if (colIndex ===
                                                4) { // Deskripsi column
                                                if (cellText && cellText
                                                    .length > 40) {
                                                    cellText = cellText
                                                        .substring(0, 40) +
                                                        '...';
                                                }
                                            } else if (colIndex ===
                                                7) { // Keterangan column
                                                if (cellText && cellText
                                                    .length > 15) {
                                                    cellText = cellText
                                                        .substring(0, 15) +
                                                        '...';
                                                }
                                            }

                                            // Update cell with styling
                                            row[colIndex] = {
                                                text: cellText || '',
                                                fontSize: 8,
                                                alignment: 'left'
                                            };
                                        }
                                    });
                                });
                            }
                        }
                    }
                ],
                "columnDefs": [{
                    "targets": [3, 10],
                    "visible": false
                }]
            });
            table.buttons().container().appendTo('#example2_wrapper .col-md-6:eq(0)');
        }

        // DataTable for #vehicle (vehicles)
        if ($('#vehicle').length && hasRealRows('#vehicle')) {
            var tableVehicle = $('#vehicle').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "processing": true,
                "language": {
                    "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>'
                },
                "autoWidth": false,
                "responsive": true,
                "pageLength": 10,
                "buttons": [{
                        extend: 'excelHtml5',
                        title: 'Data Kendaraan'
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'Data Kendaraan',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function(doc) {
                            if (mobilDetail) {
                                doc.content.splice(0, 0, {
                                    text: mobilDetail,
                                    margin: [0, 0, 0, 12],
                                    fontSize: 10
                                });
                            }
                            if (doc.content[1] && doc.content[1].table && doc.content[1].table
                                .body && doc.content[1].table.body.length) {
                                doc.content[1].table.widths = Array(doc.content[1].table.body[0]
                                    .length + 1).join('*').split('');
                            }
                        }
                    }
                ],
            });
            tableVehicle.buttons().container().appendTo('#vehicle_wrapper .col-md-6:eq(0)');
        }
		
		// DataTable for #transaksiTable (transactions with long content)
        if ($('#transaksiTable').length && hasRealRows('#transaksiTable')) {
            var transaksiTable = $('#transaksiTable').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": false,
                "info": true,
                "processing": true,
                "language": {
                    "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>'
                },
                "autoWidth": false,
                "responsive": true,
                "pageLength": 10,
                "buttons": [{
                        extend: 'colvis',
                        text: 'Show/Hide Columns'
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Data Report',
                        messageTop: mobilDetail.replace(/\n/g, '\r\n')
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'Data Report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 4, 5, 6, 7, 8,
                                9
                            ] // Only export specific columns (exclude 3, 10)
                        },
                        customize: function(doc) {
                            if (mobilDetail) {
                                doc.content.splice(0, 0, {
                                    text: mobilDetail,
                                    margin: [0, 0, 0, 12],
                                    fontSize: 10
                                });
                            }
                            if (doc.content[1] && doc.content[1].table && doc.content[1].table
                                .body && doc.content[1].table.body.length) {
                                // Set specific column widths for transaksi table
                                doc.content[1].table.widths = [
                                    '12%', // Nomor Job
                                    '10%', // Tanggal Job
                                    '8%', // Posisi KM
                                    '30%', // Deskripsi
                                    '6%', // Jumlah
                                    '12%', // Harga
									'12%', // Harga Total
                                    '8%' // Keterangan
                                ];

                                // Process each row for text truncation and styling
                                doc.content[1].table.body.forEach(function(row, rowIndex) {
                                    row.forEach(function(cell, colIndex) {
                                        // Style header row
                                        if (rowIndex === 0) {
                                            if (typeof cell === 'object') {
                                                cell.fillColor = '#3498db';
                                                cell.color = 'white';
                                                cell.bold = true;
                                                cell.fontSize = 9;
                                            }
                                        } else {
                                            // Style data rows
                                            var cellText = (typeof cell ===
                                                'object') ? cell.text : cell;

                                            // Truncate long text in specific columns
                                            if (colIndex ===
                                                4) { // Deskripsi column
                                                if (cellText && cellText
                                                    .length > 40) {
                                                    cellText = cellText
                                                        .substring(0, 40) +
                                                        '...';
                                                }
                                            } else if (colIndex ===
                                                7) { // Keterangan column
                                                if (cellText && cellText
                                                    .length > 15) {
                                                    cellText = cellText
                                                        .substring(0, 15) +
                                                        '...';
                                                }
                                            }

                                            // Update cell with styling
                                            row[colIndex] = {
                                                text: cellText || '',
                                                fontSize: 8,
                                                alignment: 'left'
                                            };
                                        }
                                    });
                                });
                            }
                        }
                    }
                ],
                "columnDefs": [{
                    "targets": [3, 10],
                    "visible": false
                }]
            });
            transaksiTable.buttons().container().appendTo('#transaksiTable_wrapper .col-md-6:eq(0)');
        }*/

        // Initialize Select2 for Nomor Polisi
        $('#nomor_polisi').select2({
            placeholder: 'Cari nomor polisi...',
			allowClear: true, // Enable clear button
            minimumInputLength: 2,
            ajax: {
                url: '{{ route('nomor_polisi.search') }}',
                dataType: 'json',
                delay: 350,
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
        });

        // Initialize Select2 for Nama Customer (kode_customer - nama_customer)
        $('#nama_customer').select2({
            placeholder: 'Cari kode/nama customer...',
			allowClear: true, // Enable clear button
            minimumInputLength: 2,
            ajax: {
                url: '{{ route('customer.search') }}',
                dataType: 'json',
                delay: 350,
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
            },
            matcher: function(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }
                if (typeof data.text === 'undefined') {
                    return null;
                }
                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                    return data;
                }
                return null;
            }
        });

        // Date Range Picker
        $('#tanggal_job').daterangepicker({
            locale: {
                format: 'DD-MM-YYYY'
            },
            autoUpdateInput: false
        });

        $('#tanggal_job').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' s/d ' + picker.endDate.format(
                'DD-MM-YYYY'));
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
        });

        $('#tanggal_job').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#start_date').val('');
            $('#end_date').val('');
        });

        // Set selected value for nomor_polisi if exists
        @if (request('nomor_polisi'))
            var nomorPolisi = "{{ request('nomor_polisi') }}";
            var newOption = new Option(nomorPolisi, nomorPolisi, true, true);
            $('#nomor_polisi').append(newOption).trigger('change');
        @endif

        // Set date range picker value if exists
        @if (request('start_date') && request('end_date'))
            $('#tanggal_job').data('daterangepicker').setStartDate("{{ request('start_date') }}");
            $('#tanggal_job').data('daterangepicker').setEndDate("{{ request('end_date') }}");
            $('#tanggal_job').val(
                "{{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}"
            );
            $('#start_date').val("{{ request('start_date') }}");
            $('#end_date').val("{{ request('end_date') }}");
        @endif
		
		$('#tanggal_job_transaksi').daterangepicker({
			locale: {
				format: 'DD-MM-YYYY'
			},
			autoUpdateInput: false
		});

		$('#tanggal_job_transaksi').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('DD-MM-YYYY') + ' s/d ' + picker.endDate.format(
				'DD-MM-YYYY'));
			$('#start_date_transaksi').val(picker.startDate.format('YYYY-MM-DD'));
			$('#end_date_transaksi').val(picker.endDate.format('YYYY-MM-DD'));
		});

		$('#tanggal_job_transaksi').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
			$('#start_date_transaksi').val('');
			$('#end_date_transaksi').val('');
		});

		@if (request('start_date_transaksi') && request('end_date_transaksi'))
			$('#tanggal_job_transaksi').data('daterangepicker').setStartDate(
				"{{ request('start_date_transaksi') }}");
			$('#tanggal_job_transaksi').data('daterangepicker').setEndDate(
				"{{ request('end_date_transaksi') }}");
			$('#tanggal_job_transaksi').val(
				"{{ \Carbon\Carbon::parse(request('start_date_transaksi'))->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse(request('end_date_transaksi'))->format('d-m-Y') }}"
			);
			$('#start_date_transaksi').val("{{ request('start_date_transaksi') }}");
			$('#end_date_transaksi').val("{{ request('end_date_transaksi') }}");
		@endif
    });
</script>

</html>
