<x-app-layout>
    <div class="pc-container">
        <div class="pc-content">

            <div class="row mb-4 align-items-stretch">
                <div class="col-lg-8 col-md-12 mb-3 mb-lg-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <form action="{{ route('gaji.index') }}" method="GET" class="row g-3 align-items-end m-0">
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold mb-1">Bulan</label>
                                    <select name="bulan" class="form-select border-light-subtle shadow-none">
                                        @for($m=1; $m<=12; $m++) <option value="{{ sprintf('%02d', $m) }}" {{
                                            $bulan==sprintf('%02d', $m) ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                            @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold mb-1">Tahun</label>
                                    <select name="tahun" class="form-select border-light-subtle shadow-none">
                                        @php $currentYear = date('Y'); @endphp
                                        @for($y=$currentYear-2; $y<=$currentYear+1; $y++) <option value="{{ $y }}" {{
                                            $tahun==$y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                        <i class="ti ti-search me-1"></i> Terapkan Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card border-0 shadow-sm bg-primary text-white h-100">
                        <div class="card-body d-flex flex-column justify-content-center align-items-end text-end">
                            <p class="mb-1 text-white-50 fw-semibold">Total Pengeluaran Gaji Bulan Ini</p>
                            <h3 class="mb-0 fw-bold text-white d-flex align-items-center">
                                <span class="fs-5 me-1">Rp</span> <span id="top_grand_total">0</span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom pb-3 pt-4">
                            <h5 class="mb-0 fw-bold">
                                <i class="ti ti-file-invoice text-primary me-2"></i>
                                Input Gaji: <span class="text-primary">{{ date('F', mktime(0, 0, 0, $bulan, 1)) }} {{
                                    $tahun }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <form action="{{ route('gaji.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="bulan" value="{{ $bulan }}">
                                <input type="hidden" name="tahun" value="{{ $tahun }}">

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 text-center">
                                        <thead class="table-light text-muted">
                                            <tr>
                                                <th rowspan="2" class="align-middle border-end text-start ps-4">Nama
                                                    Karyawan</th>
                                                <th colspan="4" class="py-3 border-end">Minggu & Tanggal Pembayaran</th>
                                                <th rowspan="2" class="align-middle text-end pe-4">Total Nominal</th>
                                            </tr>
                                            <tr>
                                                <th class="fw-medium px-3 bg-light">
                                                    Minggu 1
                                                    <input type="date" name="tgl_m1"
                                                        class="form-control form-control-sm mt-2 border-light-subtle shadow-none text-center"
                                                        value="{{ $dates['m1'] }}">
                                                </th>
                                                <th class="fw-medium px-3 bg-light">
                                                    Minggu 2
                                                    <input type="date" name="tgl_m2"
                                                        class="form-control form-control-sm mt-2 border-light-subtle shadow-none text-center"
                                                        value="{{ $dates['m2'] }}">
                                                </th>
                                                <th class="fw-medium px-3 bg-light">
                                                    Minggu 3
                                                    <input type="date" name="tgl_m3"
                                                        class="form-control form-control-sm mt-2 border-light-subtle shadow-none text-center"
                                                        value="{{ $dates['m3'] }}">
                                                </th>
                                                <th class="fw-medium px-3 bg-light border-end">
                                                    Minggu 4
                                                    <input type="date" name="tgl_m4"
                                                        class="form-control form-control-sm mt-2 border-light-subtle shadow-none text-center"
                                                        value="{{ $dates['m4'] }}">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                            @foreach($karyawans as $k)
                                            @php
                                            $gaji = $penggajians[$k->id] ?? null;
                                            @endphp
                                            <tr class="karyawan-row">
                                                <td class="text-start ps-4 fw-medium text-dark border-end">{{ $k->nama
                                                    }}</td>
                                                <td>
                                                    <input type="number" name="gaji[{{ $k->id }}][minggu_1]"
                                                        class="form-control text-end border-0 bg-transparent nominal-input shadow-none"
                                                        value="{{ $gaji->minggu_1 ?? 0 }}" placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="gaji[{{ $k->id }}][minggu_2]"
                                                        class="form-control text-end border-0 bg-transparent nominal-input shadow-none"
                                                        value="{{ $gaji->minggu_2 ?? 0 }}" placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="gaji[{{ $k->id }}][minggu_3]"
                                                        class="form-control text-end border-0 bg-transparent nominal-input shadow-none"
                                                        value="{{ $gaji->minggu_3 ?? 0 }}" placeholder="0">
                                                </td>
                                                <td class="border-end">
                                                    <input type="number" name="gaji[{{ $k->id }}][minggu_4]"
                                                        class="form-control text-end border-0 bg-transparent nominal-input shadow-none"
                                                        value="{{ $gaji->minggu_4 ?? 0 }}" placeholder="0">
                                                </td>
                                                <td class="pe-4">
                                                    <input type="text"
                                                        class="form-control text-end border-0 bg-light text-primary fw-bold total-field shadow-none"
                                                        value="{{ number_format($gaji->nominal ?? 0, 0, ',', '.') }}"
                                                        readonly tabindex="-1">
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th class="text-end pe-3 py-3 border-end">TOTAL REKAP</th>
                                                <th id="total_m1" class="text-end pe-3 text-dark">0</th>
                                                <th id="total_m2" class="text-end pe-3 text-dark">0</th>
                                                <th id="total_m3" class="text-end pe-3 text-dark">0</th>
                                                <th id="total_m4" class="text-end pe-3 border-end text-dark">0</th>
                                                <th id="total_all" class="text-end pe-4 text-primary fs-5 fw-bold">0
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="p-4 border-top text-end bg-white rounded-bottom">
                                    <button type="submit" class="btn btn-success px-4 py-2 shadow-sm">
                                        <i class="ti ti-device-floppy me-1"></i> Simpan Semua Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            function calculateTotals() {
                let colTotals = [0, 0, 0, 0];
                let grandTotal = 0;

                $('.karyawan-row').each(function() {
                    let rowTotal = 0;
                    // Hitung per cell nominal
                    $(this).find('.nominal-input').each(function(index) {
                        let val = parseInt($(this).val()) || 0;
                        rowTotal += val;
                        colTotals[index] += val;
                    });
                    
                    // Update field total per baris (Karyawan)
                    $(this).find('.total-field').val(formatRupiah(rowTotal));
                    grandTotal += rowTotal;
                });

                // Update text di Tfoot
                $('#total_m1').text(formatRupiah(colTotals[0]));
                $('#total_m2').text(formatRupiah(colTotals[1]));
                $('#total_m3').text(formatRupiah(colTotals[2]));
                $('#total_m4').text(formatRupiah(colTotals[3]));
                $('#total_all').text(formatRupiah(grandTotal));

                // Update text di WIDGET CARD atas (Baru ditambahkan)
                $('#top_grand_total').text(formatRupiah(grandTotal));
            }

            // Trigger kalkulasi saat input diubah
            $('.nominal-input').on('input', function() {
                calculateTotals();
            });

            // Initial calculation saat halaman dimuat
            calculateTotals();
        });
    </script>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{!! session('success') !!}',
                confirmButtonColor: '#3085d6'
            });
        });
    </script>
    @endif
</x-app-layout>