<x-app-layout>
    <div class="pc-container">
        <div class="pc-content">

            <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 border-bottom pb-2">
                <ul class="nav nav-tabs border-bottom-0 m-0" id="penarikanTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-primary shadow-sm rounded-top" id="riwayat-tab"
                            data-bs-toggle="tab" data-bs-target="#riwayat" type="button" role="tab"
                            style="border: 1px solid #dee2e6; border-bottom: none;">
                            <i class="fas fa-list me-1"></i> Riwayat Penarikan
                        </button>
                    </li>
                    <li class="nav-item ms-2" role="presentation">
                        <button class="nav-link fw-bold text-success shadow-sm rounded-top" id="rekap-tab"
                            data-bs-toggle="tab" data-bs-target="#rekap" type="button" role="tab"
                            style="border: 1px solid #dee2e6; border-bottom: none;">
                            <i class="fas fa-chart-bar me-1"></i> Rekap Bulanan
                        </button>
                    </li>
                </ul>

                <form action="{{ route('penarikan_omset.index') }}" method="GET" id="yearFilterForm"
                    class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                    <label class="form-label fw-bold mb-0 text-nowrap">Filter Tahun:</label>
                    <select name="year" class="form-select form-select-sm shadow-sm border-primary"
                        style="min-width: 120px;" onchange="this.form.submit()">
                        @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $year==$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="tab-content" id="penarikanTabContent">

                <div class="tab-pane fade show active" id="riwayat" role="tabpanel">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card shadow-sm border-0">
                                <div
                                    class="card-header d-flex flex-wrap justify-content-between align-items-center bg-white border-bottom gap-3">
                                    <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-cash-register me-2"></i> Data
                                        Penarikan</h5>

                                    <ul class="nav nav-pills flex-grow-1 justify-content-md-center" id="tokoTabs"
                                        role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active btn-sm py-1 px-3 toko-filter-btn"
                                                data-filter="all" type="button" role="tab">Semua Toko</button>
                                        </li>
                                        @foreach($tokos as $toko)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link btn-sm py-1 px-3 toko-filter-btn"
                                                data-filter="{{ $toko->id }}" type="button" role="tab">{{ $toko->nama
                                                }}</button>
                                        </li>
                                        @endforeach
                                    </ul>

                                    <button type="button" class="btn btn-primary btn-sm text-nowrap"
                                        data-bs-toggle="modal" data-bs-target="#penarikanModal" id="btnAddPenarikan">
                                        <i class="fas fa-plus"></i> Tambah Penarikan
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="table-penarikan" class="table table-striped table-hover">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Toko</th>
                                                    <th>Tanggal</th>
                                                    <th>Jumlah</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($penarikans as $item)
                                                <tr class="penarikan-row" data-toko="{{ $item->toko->id }}">
                                                    <td class="row-number">{{ $loop->iteration }}</td>
                                                    <td>{{ $item->toko->nama }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($item->tgl)->format('d M Y') }}</td>
                                                    <td class="fw-bold">Rp {{ number_format($item->jumlah, 0, ',', '.')
                                                        }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-warning btn-sm btn-edit text-dark"
                                                                data-id="{{ $item->id }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form
                                                                action="{{ route('penarikan_omset.destroy', $item->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Yakin ingin menghapus data penarikan ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="rekap" role="tabpanel">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0 text-center text-uppercase fw-bold"><i
                                            class="fas fa-chart-line me-2"></i> Keuntungan Bulanan ({{ $year }})</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0 table-sm"
                                            style="border: 2px solid #000;">
                                            <thead class="text-center" style="background-color: #f8f9fa;">
                                                <tr>
                                                    <th style="border: 1px solid #000; width: 150px;">BULAN</th>
                                                    <th style="border: 1px solid #000;">NAMA TOKO</th>
                                                    <th style="border: 1px solid #000;">JUMLAH</th>
                                                    <th style="border: 1px solid #000;">TOTAL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $idMonths = [
                                                'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
                                                'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
                                                'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
                                                'October' => 'Oktober', 'November' => 'November', 'December' =>
                                                'Desember'
                                                ];
                                                $grandTotal = 0; // Inisialisasi variabel Grand Total
                                                @endphp

                                                @foreach($rekapBulanan as $bulanKey => $items)
                                                @php
                                                $totalBulan = $items->sum('total_toko');
                                                $grandTotal += $totalBulan; // Tambahkan ke Grand Total
                                                $first = true;
                                                $bulanIndo = $idMonths[$items->first()->bulan] ??
                                                $items->first()->bulan;
                                                @endphp
                                                @foreach($items as $item)
                                                <tr>
                                                    @if($first)
                                                    <td rowspan="{{ $items->count() }}"
                                                        class="text-center align-middle fw-bold"
                                                        style="border: 1px solid #000; background-color: #fff;">
                                                        {{ strtoupper($bulanIndo) }} {{ $item->tahun }}
                                                    </td>
                                                    @endif
                                                    <td style="border: 1px solid #000; background-color: #fff;">{{
                                                        $item->toko->nama }}</td>
                                                    <td class="text-end"
                                                        style="border: 1px solid #000; background-color: #fff;">{{
                                                        number_format($item->total_toko, 0, ',', '.') }}</td>
                                                    @if($first)
                                                    <td rowspan="{{ $items->count() }}"
                                                        class="text-center align-middle fw-bold text-success"
                                                        style="border: 1px solid #000; background-color: #e8f5e9;">
                                                        {{ number_format($totalBulan, 0, ',', '.') }}
                                                    </td>
                                                    @endif
                                                </tr>
                                                @php $first = false; @endphp
                                                @endforeach
                                                @endforeach
                                            </tbody>
                                            <tfoot style="background-color: #e8f5e9;">
                                                <tr>
                                                    <th colspan="3" class="text-end fw-bold align-middle pe-3"
                                                        style="border: 1px solid #000; font-size: 1.1rem;">GRAND TOTAL
                                                        KESELURUHAN</th>
                                                    <th class="text-center fw-bold text-success"
                                                        style="border: 1px solid #000; font-size: 1.15rem;">
                                                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="penarikanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <form id="formPenarikan" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="method" value="POST">

                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-primary" id="modalTitle">Tambah Penarikan Omset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Toko <span class="text-danger">*</span></label>
                            <input type="hidden" name="toko_id" id="toko_id" required>

                            <div class="row g-3" id="toko-selection">
                                @foreach($tokos as $toko)
                                <div class="col-md-3 col-6">
                                    <div class="card h-100 store-card border rounded-3 text-center"
                                        data-id="{{ $toko->id }}">
                                        <div
                                            class="card-body p-2 d-flex flex-column justify-content-center align-items-center">
                                            <i class="fas fa-store fs-4 mb-2 store-icon text-primary"></i>
                                            <div class="small fw-bold">{{ $toko->nama }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl" id="tgl" class="form-control" required
                                        value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jumlah Penarikan (Rp) <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="jumlah" id="jumlah" class="form-control fw-bold"
                                            required placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .store-card {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            background-color: #fff;
        }

        .store-card * {
            pointer-events: none;
        }

        .store-card:hover {
            border-color: #0d6efd !important;
            background-color: #f0f7ff;
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .store-card.selected {
            border-color: #0d6efd !important;
            background-color: #0d6efd !important;
            color: #fff !important;
            transform: translateY(0);
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
        }

        .store-card.selected .store-icon,
        .store-card.selected .fw-bold {
            color: #fff !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formPenarikan');
            const tokoIdInput = document.getElementById('toko_id');
            const storeCards = document.querySelectorAll('.store-card');
            
            // 1. Logic Filter Tabel Berdasarkan Toko (Client-side)
            const filterBtns = document.querySelectorAll('.toko-filter-btn');
            const tableRows = document.querySelectorAll('.penarikan-row');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Update state tombol aktif
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const filterValue = this.getAttribute('data-filter');
                    let counter = 1; // Untuk mereset nomor urut (#)

                    tableRows.forEach(row => {
                        const rowTokoId = row.getAttribute('data-toko');
                        if (filterValue === 'all' || rowTokoId === filterValue) {
                            row.style.display = ''; // Munculkan
                            row.querySelector('.row-number').textContent = counter++; // Update nomor urut
                        } else {
                            row.style.display = 'none'; // Sembunyikan
                        }
                    });
                });
            });

            // 2. Logic Klik Toko di Modal
            storeCards.forEach(card => {
                card.addEventListener('click', function() {
                    storeCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    tokoIdInput.value = this.dataset.id;
                });
            });

            // 3. Logic saat tombol Tambah diklik (Reset Modal)
            document.getElementById('btnAddPenarikan').addEventListener('click', function() {
                form.reset();
                form.action = "{{ route('penarikan_omset.store') }}";
                document.getElementById('method').value = "POST";
                document.getElementById('modalTitle').innerText = "Tambah Penarikan Omset";
                
                tokoIdInput.value = "";
                storeCards.forEach(c => c.classList.remove('selected'));
                document.getElementById('tgl').value = "{{ date('Y-m-d') }}";
            });

            // 4. Logic saat tombol Edit diklik
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    
                    fetch(`{{ url('penarikan_omset') }}/${id}`)
                        .then(res => res.json())
                        .then(data => {
                            form.action = `{{ url('penarikan_omset') }}/${id}`;
                            document.getElementById('method').value = "PUT";
                            document.getElementById('modalTitle').innerText = "Edit Penarikan Omset";
                            
                            document.getElementById('tgl').value = data.tgl.split(' ')[0];
                            document.getElementById('jumlah').value = Math.round(data.jumlah);
                            tokoIdInput.value = data.toko_id;
                            
                            storeCards.forEach(card => {
                                if (card.dataset.id == data.toko_id) {
                                    card.classList.add('selected');
                                } else {
                                    card.classList.remove('selected');
                                }
                            });
                            
                            const modalElement = document.getElementById('penarikanModal');
                            const bootstrapModal = new bootstrap.Modal(modalElement);
                            bootstrapModal.show();
                        })
                        .catch(err => {
                            console.error("Gagal mengambil data:", err);
                            alert("Terjadi kesalahan saat memuat data!");
                        });
                });
            });
        });
    </script>
</x-app-layout>