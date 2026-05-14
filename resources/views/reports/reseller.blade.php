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
                                        @for ($y = date('Y') - 5; $y <= date('Y') + 1; $y++) <option value="{{ $y }}" {{
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
                        <table class="table table-hover table-bordered align-middle" id="resellerDetailTable">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>Nama Reseller</th>
                                    <th class="text-center">Total Lusin</th>
                                    <th class="text-center">Total Potong</th>
                                    <th class="text-end">Total Penjualan</th>
                                    <th class="text-end">Total Profit</th>
                                    <th class="text-end">Total Bayar</th>
                                    <th class="text-end">Sisa Piutang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resellerData as $rd)
                                <tr>
                                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $rd->nama }}</td>
                                    <td class="text-center">{{ floor($rd->total_barang / 12) }}</td>
                                    <td class="text-center">{{ $rd->total_barang % 12 }}</td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($rd->total_penjualan, 0, ',', '.') }}</td>
                                    <td class="text-end text-success">Rp {{ number_format($rd->total_keuntungan, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($rd->total_bayar, 0, ',', '.') }}</td>
                                    <td class="text-end text-danger fw-bold">Rp {{ number_format(abs($rd->total_piutang), 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>