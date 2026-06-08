<x-app-layout>
    <div class="pc-container">
        <div class="pc-content">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Rekap Penjualan Reseller Tahunan</h5>
                    </div>
                    <div class="card-body" style="overflow-x:auto;">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('reports.reseller') }}" class="mb-4 p-3 border rounded">
                            <h6 class="mb-3"><i class="fas fa-filter"></i> Filter Data Rekap Reseller</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tahun</label>
                                    <select name="tahun" class="form-select">
                                        @for ($y = date('Y') - 1; $y <= date('Y') + 5; $y++) <option value="{{ $y }}" {{
                                            $tahun==$y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>

                        <table class="table table-striped table-hover dt-responsive nowrap" style="width: 100%">
                            <thead class="table-primary">
                                <tr>
                                    <th>Kategori</th>
                                    @foreach($bulanList as $bulan)
                                    <th>{{ $bulan }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Total Reseller</strong></td>
                                    @foreach($bulanList as $bulan)
                                    <td>{{ number_format($hasil[$bulan]['total_reseller'], 0, ',', '.') }} Reseller</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td><strong>Total Barang (Pcs)</strong></td>
                                    @foreach($bulanList as $bulan)
                                    <td>{{ number_format($hasil[$bulan]['total_barang'], 0, ',', '.') }} Pcs</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td><strong>Total Penjualan (Rp)</strong></td>
                                    @foreach($bulanList as $bulan)
                                    <td>Rp {{ number_format($hasil[$bulan]['total_penjualan'], 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td><strong>Total Keuntungan (Rp)</strong></td>
                                    @foreach($bulanList as $bulan)
                                    <td>Rp {{ number_format($hasil[$bulan]['total_keuntungan'], 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td><strong>Total Bayar (Rp)</strong></td>
                                    @foreach($bulanList as $bulan)
                                    <td>Rp {{ number_format($hasil[$bulan]['total_bayar'], 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td><strong>Total Piutang (Rp)</strong></td>
                                    @foreach($bulanList as $bulan)
                                    <td class="text-danger">Rp {{ number_format($hasil[$bulan]['total_piutang'], 0, ',',
                                        '.') }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        <hr class="my-5">
                        <h5 class="mb-4 text-primary"><i class="fas fa-user-tag me-2"></i> Rincian Per Nama Reseller (Total Tahun {{ $tahun }})</h5>
                        @php
                            $grandTotalLusin = 0;
                            $grandTotalPotong = 0;
                            $grandTotalPenjualan = 0;
                            $grandTotalHpp = 0;
                            $grandTotalProfit = 0;
                            $grandTotalBayar = 0;
                            $grandTotalPiutang = 0;
                        @endphp
                        <table class="table table-hover table-bordered align-middle" id="resellerDetailTable">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>Nama Reseller</th>
                                    <th class="text-center">Total Lusin</th>
                                    <th class="text-center">Total Potong</th>
                                    <th class="text-end">Total HPP</th>
                                    <th class="text-end">Total Penjualan</th>
                                    <th class="text-end">Total Profit</th>
                                    <th class="text-end">Total Bayar</th>
                                    <th class="text-end">Sisa Piutang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resellerData as $rd)
                                @php
                                    $hpp = $rd->total_penjualan - $rd->total_keuntungan;
                                    $grandTotalLusin += $rd->total_lusin;
                                    $grandTotalPotong += $rd->total_potong;
                                    $grandTotalPenjualan += $rd->total_penjualan;
                                    $grandTotalHpp += $hpp;
                                    $grandTotalProfit += $rd->total_keuntungan;
                                    $grandTotalBayar += $rd->total_bayar;
                                    $grandTotalPiutang += abs($rd->total_piutang);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $rd->nama }}</td>
                                    <td class="text-center">{{ $rd->total_lusin ?: '-' }}</td>
                                    <td class="text-center">{{ $rd->total_potong ?: '-' }}</td>
                                    <td class="text-end text-muted">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($rd->total_penjualan, 0, ',', '.') }}</td>
                                    <td class="text-end text-success">Rp {{ number_format($rd->total_keuntungan, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($rd->total_bayar, 0, ',', '.') }}</td>
                                    <td class="text-end text-danger fw-bold">Rp {{ number_format(abs($rd->total_piutang), 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-white text-dark">
                                <tr class="fw-bold">
                                    <td colspan="2" class="text-center">Subtotal</td>
                                    <td class="text-center">{{ $grandTotalLusin ?: '-' }}</td>
                                    <td class="text-center">{{ $grandTotalPotong ?: '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($grandTotalHpp, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($grandTotalPenjualan, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($grandTotalProfit, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($grandTotalBayar, 0, ',', '.') }}</td>
                                    <td class="text-end text-danger">Rp {{ number_format($grandTotalPiutang, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>