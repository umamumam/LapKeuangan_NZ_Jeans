<x-app-layout>
    <div class="pc-container">
        <div class="pc-content">
            <div class="col-sm-12">
                @if(session('success'))
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: "{{ session('success') }}",
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
                </script>
                @endif

                @if(session('error'))
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal!",
                            text: "{{ session('error') }}",
                            showConfirmButton: true
                        });
                    });
                </script>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-users text-primary me-2"></i> Partner
                            Reseller</h4>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <form action="{{ route('reseller_transactions.index') }}" method="GET" class="d-flex gap-2">
                            <select name="month" class="form-select form-select-sm" style="width: auto;">
                                @for($m=1; $m<=12; $m++) <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{
                                    $month==str_pad($m, 2, '0' , STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                    @endfor
                            </select>
                            <select name="year" class="form-select form-select-sm" style="width: auto;">
                                @for($y=date('Y')-2; $y<=date('Y'); $y++) <option value="{{ $y }}" {{ $year==$y
                                    ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </form>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target="#rekapGlobal" aria-expanded="false" aria-controls="rekapGlobal"
                            title="Tampilkan/Sembunyikan Rekap">
                            <i class="fas fa-eye"></i> Tampilkan / Sembunyikan Rekap
                        </button>
                    </div>
                </div>

                <div class="collapse" id="rekapGlobal">
                    <div class="pb-4">
                        <div class="card shadow-sm border-0" style="border-radius: 12px;">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="fw-bold"><i class="fas fa-calendar-alt text-muted me-2"></i>Rekap 1 Bulan:
                                    Transaksi Reseller ({{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach (['minggu_1' => 'Minggu 1 (Tgl 1-7)', 'minggu_2' => 'Minggu 2 (Tgl 8-14)',
                                    'minggu_3' => 'Minggu 3 (Tgl 15-21)', 'minggu_4' => 'Minggu 4 (Tgl 22-28)',
                                    'minggu_5'
                                    => 'Minggu 5 (Tgl 29+)'] as $key => $label)
                                    <div class="col-md mb-3" style="min-width: 200px;">
                                        <div class="card bg-light border-0 h-100 shadow-sm" style="border-radius: 8px;">
                                            <div class="card-body text-center p-3">
                                                <h6 class="fw-bold mb-1">{{ explode(' (', $label)[0] }}</h6>
                                                <p class="text-muted" style="font-size: 11px;">({{ explode(' (',
                                                    $label)[1]
                                                    }}</p>
                                                <hr class="my-2" style="opacity: 0.1">

                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted" style="font-size: 11px;">Tagihan</span>
                                                    <span class="fw-bold text-dark" style="font-size: 12px;">Rp {{
                                                        number_format($rekapGlobal[$key]['total_uang'], 0, ',', '.')
                                                        }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted" style="font-size: 11px;">Sisa/Kurang</span>
                                                    <span
                                                        class="{{ $rekapGlobal[$key]['sisa_kurang'] >= 0 ? 'text-dark' : 'text-danger' }} fw-bold"
                                                        style="font-size: 12px;">
                                                        {{ $rekapGlobal[$key]['sisa_kurang'] >= 0 ? '' : '-' }} Rp {{
                                                        number_format(abs($rekapGlobal[$key]['sisa_kurang']), 0, ',',
                                                        '.')
                                                        }}
                                                    </span>
                                                </div>

                                                <hr class="my-2" style="opacity: 0.1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-primary fw-bold"
                                                        style="font-size: 11px;">Keuntungan</span>
                                                    <span class="fw-bold text-primary" style="font-size: 12px;">
                                                        Rp {{ number_format($rekapGlobal[$key]['total_keuntungan'], 0,
                                                        ',',
                                                        '.') }}
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-success fw-bold"
                                                        style="font-size: 11px;">Bayar</span>
                                                    <span class="fw-bold text-success" style="font-size: 12px;">
                                                        Rp {{ number_format($rekapGlobal[$key]['bayar'], 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @foreach($resellers as $index => $reseller)
                    @php
                    $gradients = [
                    'linear-gradient(135deg, #4b3d8f 0%, #663dff 100%)', // Deep Purple
                    'linear-gradient(135deg, #1fa2ff 0%, #12d8fa 100%)', // Blue
                    'linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)', // Orange/Red
                    'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)', // Green
                    ];
                    $bgGradient = $gradients[$index % count($gradients)];

                    $totalTagihanGlobal = $reseller->sisa_kurang;
                    $totalProfitGlobal = $reseller->total_keuntungan;
                    @endphp
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <a href="{{ route('reseller_transactions.show_reseller', $reseller->id) }}"
                            class="text-decoration-none">
                            <div class="card h-100 border-0 shadow hover-card"
                                style="border-radius: 12px; background: {{ $bgGradient }}; position: relative; overflow: hidden; min-height: 140px;">
                                <div
                                    style="position: absolute; right: -30px; top: -30px; width: 140px; height: 140px; border-radius: 50%; background: rgba(255,255,255,0.08);">
                                </div>
                                <div
                                    style="position: absolute; right: 50px; bottom: -50px; width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.05);">
                                </div>

                                <div
                                    class="card-body position-relative z-1 d-flex flex-column justify-content-between p-3 text-white">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div
                                            class="bg-white bg-opacity-25 rounded px-2 py-1 d-flex align-items-center justify-content-center shadow-sm">
                                            <i class="fas fa-user-tie text-white" style="font-size: 1.1rem;"></i>
                                        </div>

                                        @if($totalTagihanGlobal < 0)
                                            <span class="badge bg-danger shadow-sm px-2 py-1"
                                                style="border-radius: 8px; font-size: 0.75rem;">
                                                <i class="fas fa-exclamation-circle me-1"></i> Tagihan
                                            </span>
                                        @elseif($totalTagihanGlobal > 0)
                                            <span class="badge bg-primary shadow-sm px-2 py-1"
                                                style="border-radius: 8px; font-size: 0.75rem;">
                                                <i class="fas fa-info-circle me-1"></i> Sisa
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        <h3 class="mb-1 text-white fw-bolder text-truncate"
                                            style="letter-spacing: -0.5px;" title="{{ $reseller->nama }}">
                                            {{ strtoupper($reseller->nama) }}
                                        </h3>
                                        <div class="d-flex align-items-center text-white text-opacity-75 mb-1"
                                            style="font-size: 0.8rem;">
                                            {{ $totalTagihanGlobal < 0 ? 'Tagihan' : 'Sisa' }}: Rp {{ number_format(abs($totalTagihanGlobal), 0, ',', '.') }}
                                        </div>
                                        <div class="d-flex align-items-center text-white font-bold"
                                            style="font-size: 0.85rem;">
                                            Profit: Rp {{ number_format($totalProfitGlobal, 0, ',', '.') }}
                                        </div>

                                        <div class="border-top border-white border-opacity-25 pt-2 mt-2">
                                            <div style="font-size: 0.7rem; color: rgba(255,255,255,0.9);"
                                                class="mb-1 fw-medium">
                                                <i class="fas fa-boxes me-1 text-white text-opacity-75"></i> Produk
                                                Tersedia ({{ $reseller->barangs->count() }})
                                            </div>
                                            @php
                                                $lastUpdate = collect([
                                                    $reseller->updated_at,
                                                    $reseller->transactions_max_updated_at,
                                                    $reseller->payments_max_updated_at
                                                ])->filter()->max();
                                            @endphp
                                            @if($lastUpdate)
                                                <div style="font-size: 0.65rem; color: rgba(255,255,255,0.7);" class="fw-light">
                                                    <i class="fas fa-clock me-1"></i> Update: {{ \Carbon\Carbon::parse($lastUpdate)->diffForHumans() }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>

                <div class="card shadow-sm border-0 mt-4" style="border-radius: 12px;">
                    <div class="card-header border-bottom bg-transparent pt-3 pb-2">
                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-users-cog me-2"></i> Rincian Per Reseller
                            (Bulan {{ date('F Y', mktime(0, 0, 0, $month, 1)) }})</h6>
                        <small class="text-muted">Daftar transaksi, keuntungan, dan sisa pembayaran bulan ini.</small>
                    </div>
                    <div class="card-body">
                        @if($resellersWithDebt->isEmpty())
                        <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                            <i class="fas fa-check-circle fs-4 me-3"></i>
                            <div>Tidak ada aktivitas transaksi atau tagihan tertunggak bulan ini.</div>
                        </div>
                        @else
                        @php
                            $grandTotalLusin = 0;
                            $grandTotalPotong = 0;
                            $grandTotalHpp = 0;
                            $grandTotalPenjualan = 0;
                            $grandTotalProfit = 0;
                            $grandTotalBayar = 0;
                            $grandTotalPiutang = 0;
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-striped table-bordered mb-0">
                                <thead class="table-primary text-white text-center">
                                    <tr>
                                        <th class="text-white" style="width: 50px;">#</th>
                                        <th class="text-white text-start">Nama Reseller</th>
                                        <th class="text-white">Total Lusin</th>
                                        <th class="text-white">Total Potong</th>
                                        <th class="text-white text-end">Total HPP</th>
                                        <th class="text-white text-end">Total Penjualan</th>
                                        <th class="text-white text-end">Total Profit</th>
                                        <th class="text-white text-end">Total Bayar</th>
                                        <th class="text-white text-end">Sisa Piutang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resellersWithDebt as $rd)
                                    @php
                                        $hpp = $rd->total_uang - $rd->total_keuntungan;
                                        $grandTotalLusin += $rd->total_lusin;
                                        $grandTotalPotong += $rd->total_potong;
                                        $grandTotalHpp += $hpp;
                                        $grandTotalPenjualan += $rd->total_uang;
                                        $grandTotalProfit += $rd->total_keuntungan;
                                        $grandTotalBayar += $rd->bayar;
                                        if ($rd->sisa_kurang < 0) {
                                            $grandTotalPiutang += abs($rd->sisa_kurang);
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="fw-bold text-dark">{{ $rd->nama }}</td>
                                        <td class="text-center">{{ $rd->total_lusin ?: '-' }}</td>
                                        <td class="text-center">{{ $rd->total_potong ?: '-' }}</td>
                                        <td class="text-end text-muted">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-primary">Rp {{ number_format($rd->total_uang, 0, ',', '.') }}</td>
                                        <td class="text-end text-success">Rp {{ number_format($rd->total_keuntungan, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($rd->bayar, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            @if($rd->sisa_kurang < 0)
                                                <span class="text-danger fw-bold">Rp {{ number_format(abs($rd->sisa_kurang), 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-success fw-bold">Rp {{ number_format($rd->sisa_kurang, 0, ',', '.') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light text-dark fw-bold">
                                    <tr>
                                        <td colspan="2" class="text-center">Subtotal</td>
                                        <td class="text-center">{{ $grandTotalLusin ?: '-' }}</td>
                                        <td class="text-center">{{ $grandTotalPotong ?: '-' }}</td>
                                        <td class="text-end">Rp {{ number_format($grandTotalHpp, 0, ',', '.') }}</td>
                                        <td class="text-end text-primary">Rp {{ number_format($grandTotalPenjualan, 0, ',', '.') }}</td>
                                        <td class="text-end text-success">Rp {{ number_format($grandTotalProfit, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($grandTotalBayar, 0, ',', '.') }}</td>
                                        <td class="text-end text-danger">Rp {{ number_format($grandTotalPiutang, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .hover-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            transform-origin: center;
        }

        .hover-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15) !important;
        }
    </style>
</x-app-layout>