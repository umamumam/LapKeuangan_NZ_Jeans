<x-app-layout>
    <div class="pc-container">
        <div class="pc-content">
            <!-- Filter Section -->
            <div class="card shadow-sm border-0 mb-4 no-print">
                <div class="card-body">
                    <form action="{{ route('reseller_transactions.invoice', $reseller->id) }}" method="GET"
                        class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <button type="button" onclick="window.print()" class="btn btn-secondary shadow-sm">
                                <i class="fas fa-print me-1"></i> Cetak
                            </button>
                            @php
                            $totalRunning = $prevBalance;
                            if(isset($items)) {
                            foreach($items as $item) {
                            $totalRunning += ($item->tagihan - $item->bayar);
                            }
                            }

                            $waMessage = "Total Tagihan: Rp. " . number_format($totalRunning, 0, ',', '.') . "\n" .
                            "TRANSFER DIUSAHAKAN TEPAT WAKTU\n" .
                            "TRANSFER KE BCA\n" .
                            "No.Rek\n" .
                            "MAULANA AZRIAL ZULMI\n" .
                            "2500711399\n\n" .
                            "Kirim bukti transfer ke no\n" .
                            "+62 881-2736-711\n\n" .
                            "Terima kasih\n\n" .
                            "Catatan penting :\n" .
                            "- Setiap menerima totalan diusahakan setiap hari Jum,at LUNAS";

                            $waPhone = $reseller->telepon ? preg_replace('/[^0-9]/', '', $reseller->telepon) : '';
                            if($waPhone && !str_starts_with($waPhone, '62') && str_starts_with($waPhone, '0')) {
                            $waPhone = '62' . substr($waPhone, 1);
                            }
                            @endphp
                            @if($waPhone)
                            <button type="button" onclick="sendToWhatsApp()" class="btn btn-success shadow-sm">
                                <i class="fab fa-whatsapp me-1"></i> Kirim WA & Gambar
                            </button>
                            @else
                            <button type="button" class="btn btn-success shadow-sm disabled"
                                title="Nomor HP belum diatur">
                                <i class="fab fa-whatsapp me-1"></i> Kirim WA
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoice Content -->
            <div id="invoice-capture" class="card shadow-lg border-0 invoice-print-area"
                style="border-radius: 0; background: white;">
                <div class="card-body p-4 p-md-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <h2 class="fw-bold text-uppercase mb-0"
                                style="letter-spacing: 2px; border-bottom: 3px solid #333; display: inline-block; padding-bottom: 5px;">
                                RESELLER {{ $reseller->nama }}
                            </h2>
                        </div>
                        @if($reseller->telepon)
                        <div class="mt-2 text-muted fw-bold">
                            <i class="fas fa-phone-alt me-1"></i> {{ $reseller->telepon }}
                        </div>
                        @endif
                    </div>

                    <!-- Date info -->
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Periode: <strong>{{ date('d/m/Y', strtotime($startDate)) }} - {{
                                date('d/m/Y', strtotime($endDate)) }}</strong></span>
                        <span class="text-muted small">Dicetak: {{ date('d/m/Y H:i') }}</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center"
                            style="border: 2px solid #333 !important;">
                            <thead style="background-color: #f8f9fa;">
                                <tr style="border-bottom: 2px solid #333 !important;">
                                    <th rowspan="2" class="align-middle"
                                        style="width: 100px; border: 1px solid #333 !important;">Tanggal</th>
                                    <th rowspan="2" class="align-middle" style="border: 1px solid #333 !important;">
                                        Jenis Barang</th>
                                    <th rowspan="2" class="align-middle"
                                        style="width: 80px; border: 1px solid #333 !important;">UKURAN</th>
                                    <th colspan="3" class="text-center" style="border: 1px solid #333 !important;">Harga
                                        Jual</th>
                                    <th rowspan="2" class="align-middle"
                                        style="width: 120px; border: 1px solid #333 !important;">Total Tagihan</th>
                                    <th rowspan="2" class="align-middle"
                                        style="width: 120px; border: 1px solid #333 !important;">Total Bayar</th>
                                    <th rowspan="2" class="align-middle"
                                        style="width: 150px; border: 1px solid #333 !important;">Total Hutang</th>
                                </tr>
                                <tr style="border-bottom: 2px solid #333 !important;">
                                    <th style="border: 1px solid #333 !important;">Harga Satuan</th>
                                    <th style="border: 1px solid #333 !important;">Jumlah</th>
                                    <th style="border: 1px solid #333 !important;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Previous Balance Row -->
                                <tr class="fw-bold" style="background-color: #fefefe;">
                                    <td colspan="3" class="text-center text-uppercase"
                                        style="border: 1px solid #333 !important;">TOTAL</td>
                                    <td style="border: 1px solid #333 !important;"></td>
                                    <td style="border: 1px solid #333 !important;"></td>
                                    <td style="border: 1px solid #333 !important;"></td>
                                    <td colspan="2" class="text-center text-uppercase"
                                        style="border: 1px solid #333 !important;">SALDO AWAL</td>
                                    <td class="text-end" style="border: 1px solid #333 !important;">
                                        IDR {{ number_format($prevBalance, 0, ',', '.') }}
                                    </td>
                                </tr>

                                @php $runningBalance = $prevBalance; @endphp
                                @foreach($items as $item)
                                @php
                                $runningBalance += ($item->tagihan - $item->bayar);
                                $sales = $item->sales_details;
                                $rowCount = max(1, $sales->count());
                                @endphp

                                @if($sales->count() > 0)
                                @foreach($sales as $index => $sale)
                                <tr style="border-bottom: 1px solid #333 !important;">
                                    @if($index === 0)
                                    <td rowspan="{{ $rowCount }}" style="border: 1px solid #333 !important;">{{
                                        date('d/m/Y', strtotime($item->tgl)) }}</td>
                                    @endif

                                    <td class="text-start" style="border: 1px solid #333 !important;">{{
                                        $sale->namabarang }}</td>
                                    <td style="border: 1px solid #333 !important;">{{ $sale->ukuran ?? '-' }}</td>
                                    <td class="text-end" style="border: 1px solid #333 !important;">IDR {{
                                        number_format($sale->subtotal / ($sale->jumlah ?: 1), 0, ',', '.') }}</td>
                                    <td style="border: 1px solid #333 !important;">{{ $sale->jumlah }}</td>
                                    <td class="text-end" style="border: 1px solid #333 !important;">IDR {{
                                        number_format($sale->subtotal, 0, ',', '.') }}</td>

                                    @if($index === 0)
                                    <td rowspan="{{ $rowCount }}" class="text-end fw-bold"
                                        style="border: 1px solid #333 !important;">
                                        {{ $item->tagihan > 0 ? 'IDR ' . number_format($item->tagihan, 0, ',', '.') :
                                        '-' }}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-end fw-bold"
                                        style="border: 1px solid #333 !important;">
                                        {{ $item->bayar > 0 ? 'IDR ' . number_format($item->bayar, 0, ',', '.') : '-' }}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-end fw-bold"
                                        style="border: 1px solid #333 !important;">
                                        IDR {{ number_format($runningBalance, 0, ',', '.') }}
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                                @else
                                {{-- Payment only row --}}
                                <tr style="border-bottom: 1px solid #333 !important;">
                                    <td style="border: 1px solid #333 !important;">{{ date('d/m/Y',
                                        strtotime($item->tgl)) }}</td>
                                    <td class="text-start" style="border: 1px solid #333 !important;">PEMBAYARAN</td>
                                    <td style="border: 1px solid #333 !important;">-</td>
                                    <td style="border: 1px solid #333 !important;"></td>
                                    <td style="border: 1px solid #333 !important;"></td>
                                    <td style="border: 1px solid #333 !important;"></td>
                                    <td class="text-end" style="border: 1px solid #333 !important;">-</td>
                                    <td class="text-end fw-bold" style="border: 1px solid #333 !important;">IDR {{
                                        number_format($item->bayar, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold" style="border: 1px solid #333 !important;">IDR {{
                                        number_format($runningBalance, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @endforeach

                                <!-- Footer Total -->
                                <tr class="fw-bold"
                                    style="background-color: #fefefe; border-top: 2px solid #333 !important;">
                                    <td colspan="8" class="text-end text-uppercase"
                                        style="border: 1px solid #333 !important; letter-spacing: 1px;">TOTAL HUTANG
                                        SAAT INI</td>
                                    <td class="text-end text-danger"
                                        style="border: 1px solid #333 !important; font-size: 1.1rem;">
                                        IDR {{ number_format($runningBalance, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Notes (WhatsApp format style) -->
                    <div class="mt-5 row">
                        <div class="col-md-6 offset-md-6">
                            <div class="p-4 border border-dark rounded shadow-sm bg-light"
                                style="border-style: dashed !important;">
                                <h6 class="fw-bold mb-3 border-bottom border-dark pb-2">RINCIAN PEMBAYARAN:</h6>
                                <div class="mb-2">Total Tagihan: <span class="float-end fw-bold">IDR {{
                                        number_format($runningBalance, 0, ',', '.') }}</span></div>
                                <div class="mb-3 small text-muted">TRANSFER DIUSAHAKAN TEPAT WAKTU</div>

                                <div class="fw-bold mb-1">TRANSFER KE BCA </div>
                                <div class="mb-1">No.Rek: <span class="fw-bold">2500711399</span></div>
                                <div class="mb-3">A/N: <span class="fw-bold">MAULANA AZRIAL ZULMI</span></div>

                                <div class="small">Kirim bukti transfer ke: <span class="fw-bold">+62
                                        881-2736-711</span></div>
                                <div class="mt-3 fst-italic text-center">Terima kasih</div>
                            </div>

                            <div class="mt-4 p-3 border border-danger text-danger small" style="border-radius: 8px;">
                                <strong>Catatan Penting:</strong><br>
                                - Setiap menerima totalan diusahakan setiap hari Jum'at LUNAS.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .invoice-print-area td,
        .invoice-print-area th {
            padding: 8px !important;
            border-color: #333 !important;
        }

        @media print {

            .no-print,
            .pc-sidebar,
            .pc-header,
            .pc-footer,
            .breadcrumb {
                display: none !important;
            }

            .pc-container {
                margin: 0 !important;
                padding: 0 !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
            }

            .pc-content {
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .invoice-print-area {
                padding: 0 !important;
            }

            body {
                background-color: #fff !important;
                color: #000 !important;
            }

            .table-bordered {
                border: 2px solid #000 !important;
            }

            .table-bordered td,
            .table-bordered th {
                border: 1px solid #000 !important;
            }
        }
    </style>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        function sendToWhatsApp() {
            const captureArea = document.getElementById('invoice-capture');
            const waPhone = "{{ $waPhone }}";
            const waMessage = `{!! addslashes($waMessage) !!}`;

            // Show a "Processing" state if needed
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            btn.disabled = true;

            html2canvas(captureArea, {
                scale: 2, // Better resolution
                backgroundColor: "#ffffff",
                useCORS: true
            }).then(canvas => {
                // Convert to blob and download
                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `Invoice_NZ_{{ $reseller->nama }}_{{ date('Ymd_His') }}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Reset button
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;

                    // Alert the user that image is downloaded and they should attach it
                    Swal.fire({
                        title: 'Gambar Berhasil Diunduh',
                        text: 'Silakan lampirkan gambar yang baru saja diunduh ke WhatsApp.',
                        icon: 'success',
                        confirmButtonText: 'Buka WhatsApp',
                        showCancelButton: true,
                        cancelButtonText: 'Tutup'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(`https://wa.me/${waPhone}?text=${encodeURIComponent(waMessage)}`, '_blank');
                        }
                    });
                }, 'image/png');
            });
        }
    </script>
</x-app-layout>