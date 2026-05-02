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
                            <button type="button" onclick="sendToWhatsApp('personal')"
                                class="btn btn-success shadow-sm">
                                <i class="fab fa-whatsapp me-1"></i> Kirim ke No WA
                            </button>
                            @else
                            <button type="button" class="btn btn-success shadow-sm disabled"
                                title="Nomor HP belum diatur">
                                <i class="fab fa-whatsapp me-1"></i> Kirim ke No WA
                            </button>
                            @endif
                            <button type="button" onclick="sendToWhatsApp('group')"
                                class="btn btn-outline-success shadow-sm">
                                <i class="fas fa-users me-1"></i> Kirim ke Grup
                            </button>
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
                                        style="width: 100px; border: 1px solid #333 !important; padding: 12px 5px !important;">
                                        Tanggal</th>
                                    <th rowspan="2" class="align-middle"
                                        style="border: 1px solid #333 !important; padding: 12px 5px !important;">
                                        Jenis Barang</th>
                                    <th rowspan="2" class="align-middle"
                                        style="width: 80px; border: 1px solid #333 !important; padding: 12px 5px !important;">
                                        UKURAN</th>
                                    <th colspan="4" class="text-center"
                                        style="border: 1px solid #333 !important; padding: 8px !important;">Harga
                                        Jual</th>
                                    <th rowspan="2" class="align-middle"
                                        style="width: 130px; border: 1px solid #333 !important; padding: 12px 5px !important; line-height: 1.2;">
                                        JUMLAH<br>BAYAR</th>
                                    <th rowspan="2" class="align-middle"
                                        style="width: 160px; border: 1px solid #333 !important; padding: 12px 5px !important; line-height: 1.2;">
                                        TOTAL<br>HUTANG</th>
                                </tr>
                                <tr style="border-bottom: 2px solid #333 !important;">
                                    <th style="border: 1px solid #333 !important; padding: 8px 5px !important;">Harga
                                        Satuan</th>
                                    <th style="border: 1px solid #333 !important; padding: 8px 5px !important;">Perlusin
                                    </th>
                                    <th style="border: 1px solid #333 !important; padding: 8px 5px !important;">
                                        Perpotong</th>
                                    <th style="border: 1px solid #333 !important; padding: 8px 5px !important;">Jumlah
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Previous Balance Row -->
                                <tr class="fw-bold" style="background-color: #fefefe;">
                                    <td style="border: 1px solid #333 !important;"></td> {{-- Tanggal Kosong --}}
                                    <td colspan="6" class="text-center text-uppercase"
                                        style="border: 1px solid #333 !important; letter-spacing: 2px;">TOTAL</td>
                                    <td class="text-center text-uppercase"
                                        style="border: 1px solid #333 !important; font-size: 0.85rem;">
                                        HUTANG AWAL</td>
                                    <td class="text-end"
                                        style="border: 1px solid #333 !important; padding: 5px !important;">
                                        <table style="width: 100%; border: none !important; background: transparent;">
                                            <tr style="border: none !important;">
                                                <td
                                                    style="text-align: left; border: none !important; padding: 0 !important; font-weight: bold;">
                                                    IDR</td>
                                                <td
                                                    style="text-align: right; border: none !important; padding: 0 !important; font-weight: bold;">
                                                    {{ number_format($prevBalance, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                @php $runningBalance = $prevBalance; @endphp
                                @foreach($items as $item)
                                @php
                                $sales = $item->sales_details;
                                $payments = $item->payments;
                                $totalDayRows = $sales->count() + $payments->count();
                                $currentRow = 0;
                                @endphp

                                {{-- Render Sales --}}
                                @foreach($sales as $index => $sale)
                                @php
                                $runningBalance += $sale->subtotal;
                                $currentRow++;
                                @endphp
                                <tr style="border-bottom: 1px solid #333 !important;">
                                    @if($currentRow === 1)
                                    <td rowspan="{{ $totalDayRows }}" style="border: 1px solid #333 !important;">
                                        {{ date('d/m/Y', strtotime($item->tgl)) }}
                                    </td>
                                    @endif

                                    <td class="text-start" style="border: 1px solid #333 !important;">{{
                                        $sale->namabarang }}</td>
                                    <td style="border: 1px solid #333 !important;">{{ $sale->ukuran ?? '-' }}</td>
                                    @php
                                    $unitPrice = $sale->subtotal / ($sale->jumlah ?: 1);
                                    $isLusin = $sale->hargajual_perlusin > 0 && round($unitPrice) ==
                                    round($sale->hargajual_perlusin);
                                    @endphp
                                    <td class="text-end"
                                        style="border: 1px solid #333 !important; padding: 5px !important;">
                                        <table style="width: 100%; border: none !important; background: transparent;">
                                            <tr style="border: none !important;">
                                                <td
                                                    style="text-align: left; border: none !important; padding: 0 !important;">
                                                    IDR</td>
                                                <td
                                                    style="text-align: right; border: none !important; padding: 0 !important;">
                                                    {{ number_format($unitPrice, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="border: 1px solid #333 !important;">
                                        {{ $isLusin ? $sale->jumlah : '' }}
                                    </td> {{-- Perlusin --}}
                                    <td style="border: 1px solid #333 !important;">
                                        {{ !$isLusin ? $sale->jumlah : '' }}
                                    </td> {{-- Perpotong --}}
                                    <td class="text-end"
                                        style="border: 1px solid #333 !important; padding: 5px !important;">
                                        <table style="width: 100%; border: none !important; background: transparent;">
                                            <tr style="border: none !important;">
                                                <td
                                                    style="text-align: left; border: none !important; padding: 0 !important;">
                                                    IDR</td>
                                                <td
                                                    style="text-align: right; border: none !important; padding: 0 !important;">
                                                    {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="text-end" style="border: 1px solid #333 !important;">-</td>
                                    <td class="text-end fw-bold"
                                        style="border: 1px solid #333 !important; padding: 5px !important;">
                                        <table style="width: 100%; border: none !important; background: transparent;">
                                            <tr style="border: none !important;">
                                                <td
                                                    style="text-align: left; border: none !important; padding: 0 !important; font-weight: bold;">
                                                    IDR</td>
                                                <td
                                                    style="text-align: right; border: none !important; padding: 0 !important; font-weight: bold;">
                                                    {{ number_format($runningBalance, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach

                                {{-- Render Payments --}}
                                @foreach($payments as $index => $pay)
                                @php
                                $runningBalance -= $pay->subtotal;
                                $currentRow++;
                                @endphp
                                <tr style="border-bottom: 1px solid #333 !important; background-color: #f0fdf4;">
                                    @if($currentRow === 1)
                                    <td rowspan="{{ $totalDayRows }}" style="border: 1px solid #333 !important;">
                                        {{ date('d/m/Y', strtotime($item->tgl)) }}
                                    </td>
                                    @endif

                                    <td class="text-start fw-bold" style="border: 1px solid #333 !important;">PEMBAYARAN
                                    </td>
                                    <td style="border: 1px solid #333 !important;">-</td>
                                    <td style="border: 1px solid #333 !important;">-</td>
                                    <td style="border: 1px solid #333 !important;">-</td>
                                    <td style="border: 1px solid #333 !important;">-</td>
                                    <td style="border: 1px solid #333 !important;">-</td>
                                    <td class="text-end fw-bold text-success"
                                        style="border: 1px solid #333 !important; padding: 5px !important;">
                                        <table style="width: 100%; border: none !important; background: transparent;">
                                            <tr style="border: none !important;">
                                                <td
                                                    style="text-align: left; border: none !important; padding: 0 !important; color: inherit; font-weight: bold;">
                                                    IDR</td>
                                                <td
                                                    style="text-align: right; border: none !important; padding: 0 !important; color: inherit; font-weight: bold;">
                                                    {{ number_format($pay->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="text-end fw-bold"
                                        style="border: 1px solid #333 !important; padding: 5px !important;">
                                        <table style="width: 100%; border: none !important; background: transparent;">
                                            <tr style="border: none !important;">
                                                <td
                                                    style="text-align: left; border: none !important; padding: 0 !important; font-weight: bold;">
                                                    IDR</td>
                                                <td
                                                    style="text-align: right; border: none !important; padding: 0 !important; font-weight: bold;">
                                                    {{ number_format($runningBalance, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach

                                {{-- Render Total Row only on Fridays --}}
                                @if(date('N', strtotime($item->tgl)) == 5)
                                <tr class="fw-bold" style="background-color: #f8f9fa;">
                                    <td style="border: 1px solid #333 !important;"></td> {{-- Tanggal Kosong --}}
                                    <td colspan="7" class="text-center text-uppercase"
                                        style="border: 1px solid #333 !important; letter-spacing: 2px;">TOTAL</td>
                                    <td class="text-end"
                                        style="border: 1px solid #333 !important; padding: 5px !important;">
                                        <table style="width: 100%; border: none !important; background: transparent;">
                                            <tr style="border: none !important;">
                                                <td
                                                    style="text-align: left; border: none !important; padding: 0 !important; font-weight: bold;">
                                                    IDR</td>
                                                <td
                                                    style="text-align: right; border: none !important; padding: 0 !important; font-weight: bold;">
                                                    {{ number_format($runningBalance, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
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
                                        style="border: 1px solid #333 !important; font-size: 1.1rem; padding: 5px !important;">
                                        <table style="width: 100%; border: none !important; background: transparent;">
                                            <tr style="border: none !important;">
                                                <td
                                                    style="text-align: left; border: none !important; padding: 0 !important; color: red; font-weight: bold;">
                                                    IDR</td>
                                                <td
                                                    style="text-align: right; border: none !important; padding: 0 !important; color: red; font-weight: bold;">
                                                    {{ number_format($runningBalance, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Notes (WhatsApp format style) -->
                    <div class="mt-5 row" data-html2canvas-ignore="true">
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

                            <div class="mt-4 p-3 border border-danger text-danger small" style="border-radius: 8px;"
                                data-html2canvas-ignore="true">
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
        function sendToWhatsApp(type = 'personal') {
            const captureArea = document.getElementById('invoice-capture');
            const waPhone = "{{ $waPhone }}";
            const waMessage = `{!! addslashes($waMessage) !!}`;
 
            // Show a "Processing" state
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
                            let waUrl = '';
                            if (type === 'group') {
                                // Link untuk share ke grup/pilih kontak
                                waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(waMessage)}`;
                            } else {
                                // Link untuk chat pribadi ke nomor reseller
                                waUrl = `https://wa.me/${waPhone}?text=${encodeURIComponent(waMessage)}`;
                            }
                            window.open(waUrl, '_blank');
                        }
                    });
                }, 'image/png');
            });
        }
    </script>
</x-app-layout>