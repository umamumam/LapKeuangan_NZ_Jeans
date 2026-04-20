<x-app-layout>
    <div class="pc-container">
        <div class="pc-content">
            <!-- Header section with Monthly Filter and Total Summary -->
            <div class="row mb-4 align-items-stretch">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <form action="{{ route('petty_cash.index') }}" method="GET" class="row g-3 align-items-end m-0">
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold mb-1">Bulan</label>
                                    <select name="bulan" class="form-select border-light-subtle shadow-none">
                                        @for($m=1; $m<=12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold mb-1">Tahun</label>
                                    <select name="tahun" class="form-select border-light-subtle shadow-none">
                                        @php $currentYear = date('Y'); @endphp
                                        @for($y=$currentYear-2; $y<=$currentYear+1; $y++)
                                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                        <i class="ti ti-search me-1"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm bg-primary text-white h-100">
                        <div class="card-body d-flex flex-column justify-content-center align-items-end text-end">
                            <p class="mb-1 text-white-50 fw-semibold">Total Pengeluaran Petty Cash</p>
                            <h3 class="mb-0 fw-bold text-white d-flex align-items-center">
                                <span class="fs-5 me-1">Rp</span> <span>{{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Petty Cash Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="ti ti-wallet text-primary me-2"></i> PETTY CASH NZ FASHION: {{ date('F', mktime(0, 0, 0, $bulan, 1)) }} {{ $tahun }}</h5>
                        <button class="btn btn-success btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdd">
                            <i class="ti ti-plus"></i> Tambah Data
                        </button>
                    </div>
                </div>
                
                <div class="collapse" id="collapseAdd">
                    <div class="card-body border-bottom bg-light">
                        <form action="{{ route('petty_cash.store') }}" method="POST" id="formPettyCash">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Jenis Barang</label>
                                    <input type="text" name="jenis_barang" class="form-control form-control-sm" placeholder="Nama Barang" required>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small fw-bold">Ukuran</label>
                                    <input type="text" name="ukuran" class="form-control form-control-sm" placeholder="-">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Harga Satuan</label>
                                    <input type="number" name="harga_satuan" id="input_harga" class="form-control form-control-sm" placeholder="0" required>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small fw-bold">Ball</label>
                                    <input type="number" name="ball" id="input_ball" class="form-control form-control-sm" value="0">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small fw-bold">Pack</label>
                                    <input type="number" name="pack" id="input_pack" class="form-control form-control-sm" value="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-primary">Jumlah (Auto)</label>
                                    <input type="number" name="jumlah" id="input_jumlah" class="form-control form-control-sm bg-white fw-bold text-primary" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="LUNAS">LUNAS</option>
                                        <option value="TF">TF</option>
                                        <option value="BELUM">BELUM</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Kurang Bayar</label>
                                    <input type="number" name="kurang_bayar" class="form-control form-control-sm" value="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Kategori/Ket</label>
                                    <input type="text" name="kategori" class="form-control form-control-sm" placeholder="Atk/Iklan/Dsb">
                                </div>
                                <div class="col-md-2 align-self-end text-end">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Simpan Data</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="table-primary text-white">
                                <tr>
                                    <th class="text-white">Tanggal</th>
                                    <th class="text-white text-start">Jenis Barang</th>
                                    <th class="text-white">Ukuran</th>
                                    <th class="text-white">Harga Satuan</th>
                                    <th class="text-white">Ball</th>
                                    <th class="text-white">Pack</th>
                                    <th class="text-white text-end">Jumlah</th>
                                    <th class="text-white">Status</th>
                                    <th class="text-white">Ket/Kategori</th>
                                    <th class="text-white">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                                    <td class="text-start">{{ $item->jenis_barang }}</td>
                                    <td>{{ $item->ukuran ?? '-' }}</td>
                                    <td>{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td>{{ $item->ball }}</td>
                                    <td>{{ $item->pack }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge {{ $item->status == 'LUNAS' ? 'bg-light-success text-success' : 'bg-light-warning text-warning' }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td>{{ $item->kategori ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('petty_cash.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Hapus data ini?')"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="py-4 text-muted">Belum ada data petty cash untuk bulan ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="6" class="text-end pe-3 py-3">TOTAL PENGELUARAN BULAN INI</th>
                                    <th class="text-end pe-3 text-primary fs-5 fw-bold text-nowrap">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function calculateAmount() {
                let harga = parseInt($('#input_harga').val()) || 0;
                let ball = parseInt($('#input_ball').val()) || 0;
                let pack = parseInt($('#input_pack').val()) || 0;
                
                // Jika user isi ball ATAU pack, kita pakai qty tersebut
                let qty = ball + pack; 
                if (qty === 0) qty = 1; // Default min 1 jika barang diinput per unit tanpa ball/pack

                let total = harga * qty;
                $('#input_jumlah').val(total);
            }

            $('#input_harga, #input_ball, #input_pack').on('input', function() {
                calculateAmount();
            });
        });
    </script>
    
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            confirmButtonColor: '#3085d6'
        });
    </script>
    @endif
</x-app-layout>
