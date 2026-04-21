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
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>