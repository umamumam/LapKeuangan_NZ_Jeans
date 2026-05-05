<x-app-layout>
    <div class="pc-container">
        <div class="pc-content">
            <div class="card shadow border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header border-0 text-white d-flex justify-content-between align-items-center"
                    style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i> Edit Transaksi Reseller</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div id="priceModeIndicator" class="badge bg-white text-danger py-2 px-3 shadow-sm"
                            style="font-size: 0.85rem; border-radius: 20px; border: 1px solid #f5576c;">
                            <i class="fas fa-tag me-1"></i> Mode: <span id="priceModeText">Default</span>
                        </div>
                        <button type="button" id="changePriceModeBtn" class="btn btn-sm btn-light text-danger shadow-sm"
                            style="border-radius: 20px;">
                            <i class="fas fa-sync-alt me-1"></i> Pilih Mode Harga
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reseller_transactions.update', $resellerTransaction->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Reseller</label>
                                <input type="hidden" name="reseller_id" id="resellerSelect" value="{{ $reseller->id }}">
                                <input type="text" class="form-control bg-light text-muted"
                                    value="{{ $reseller->nama }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Transaksi</label>
                                <input type="date" name="tgl" class="form-control"
                                    value="{{ old('tgl', $resellerTransaction->tgl) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Retur (Potong)</label>
                                <input type="number" name="retur" class="form-control"
                                    value="{{ old('retur', $resellerTransaction->retur) }}" min="0">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Bukti Transfer (Opsional)</label>
                                @if($resellerTransaction->bukti_tf)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $resellerTransaction->bukti_tf) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $resellerTransaction->bukti_tf) }}"
                                            alt="Bukti TF" class="img-thumbnail" style="max-height: 150px;">
                                    </a>
                                </div>
                                @endif
                                <input type="file" name="bukti_tf" class="form-control" accept="image/*">
                                <small class="text-muted">Upload gambar bukti transfer baru untuk mengganti yang
                                    lama.</small>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Detail Barang <span id="detailPriceMode" class="text-white fw-normal" style="font-size: 0.8rem; opacity: 0.9;"></span></h6>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="detailsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Barang</th>
                                        <th style="width: 250px" id="hargaHeader">Harga Per Potong</th>
                                        <th style="width: 150px">Jumlah</th>
                                        <th style="width: 250px">Subtotal</th>
                                        <th style="width: 80px" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resellerTransaction->details as $index => $detail)
                                    <tr data-original-id="{{ $detail->barang_id }}">
                                        <td>
                                            @php
                                            $currentBarangName = '';
                                            foreach($barangs as $b) {
                                            if($b->id == $detail->barang_id) {
                                            $currentBarangName = $b->namabarang . ($b->ukuran ? ' - ' . $b->ukuran :
                                            '');
                                            break;
                                            }
                                            }
                                            @endphp
                                            <input list="barang-list" class="form-control barang-input"
                                                value="{{ $currentBarangName }}" placeholder="Cari atau ketik barang..."
                                                required>
                                            <input type="hidden" name="details[{{ $index }}][barang_id]"
                                                class="barang-id-hidden" value="{{ $detail->barang_id }}">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">Rp</span>
                                                <input type="text" class="form-control harga-display bg-light" readonly
                                                    value="{{ number_format($detail->subtotal / ($detail->jumlah ?: 1), 0, ',', '.') }}"
                                                    data-original-harga="{{ $detail->subtotal / ($detail->jumlah ?: 1) }}">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="details[{{ $index }}][jumlah]"
                                                class="form-control jumlah-input" value="{{ $detail->jumlah }}" required
                                                min="1">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">Rp</span>
                                                <input type="text" class="form-control subtotal-display bg-light"
                                                    readonly
                                                    value="{{ number_format($detail->subtotal, 0, ',', '.') }}">
                                                <input type="hidden" name="details[{{ $index }}][subtotal]"
                                                    class="subtotal-input" value="{{ $detail->subtotal }}">
                                                <input type="hidden" name="details[{{ $index }}][keuntungan]"
                                                    class="keuntungan-input" value="{{ $detail->keuntungan }}">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-row-btn"><i
                                                    class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="align-middle border-end-0">
                                            <button type="button" class="btn btn-info btn-sm text-white" id="addRowBtn">
                                                <i class="fas fa-plus"></i> Tambah Baris
                                            </button>
                                        </td>
                                        <td class="text-end fw-bold align-middle border-start-0">Total Tagihan</td>
                                        <td colspan="2">
                                            <input type="text" class="form-control fw-bold border-0 bg-transparent"
                                                id="total_uang_display" readonly value="Rp 0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="border-end-0 border-top-0"></td>
                                        <td class="text-end fw-bold align-middle">Bayar Nominal</td>
                                        <td colspan="2">
                                            <div class="input-group">
                                                <input type="number" name="bayar" class="form-control fw-bold"
                                                    id="bayar" value="{{ old('bayar', $resellerTransaction->bayar) }}"
                                                    required min="0">
                                                <button type="button" class="btn btn-secondary" id="btn-uang-pas"
                                                    title="Uang Pas">Pas</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="border-end-0 border-top-0"></td>
                                        <td class="text-end fw-bold align-middle">Sisa / Kurang</td>
                                        <td colspan="2">
                                            <input type="text" class="form-control fw-bold border-0 bg-transparent"
                                                id="sisa_kurang_display" readonly value="Rp 0">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('reseller_transactions.show_reseller', $reseller->id) }}"
                                class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <datalist id="barang-list">
        @foreach($barangs as $barang)
        <option value="{{ $barang->namabarang }}{{ $barang->ukuran ? ' - ' . $barang->ukuran : '' }}"
            data-id="{{ $barang->id }}" data-hpp="{{ $barang->hpp ?? 0 }}"
            data-jual-potong="{{ $barang->hargajual_perpotong ?? 0 }}"
            data-jual-lusin="{{ $barang->hargajual_perlusin ?? 0 }}" data-grosir="{{ $barang->harga_grosir ?? 0 }}"
            data-beli-potong="{{ $barang->hargabeli_perpotong ?? 0 }}" 
            data-beli-lusin="{{ $barang->hargabeli_perlusin ?? 0 }}"
            data-reseller-id="{{ $barang->reseller_id }}"
            data-supplier-id="{{ $barang->supplier_id }}">
        </option>
        @endforeach
    </datalist>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tableBody = document.querySelector('#detailsTable tbody');
            const addRowBtn = document.getElementById('addRowBtn');
            const resellerSelect = document.getElementById('resellerSelect');
            
            const totalDisplay = document.getElementById('total_uang_display');
            const bayarInput = document.getElementById('bayar');
            const sisaDisplay = document.getElementById('sisa_kurang_display');
            const btnUangPas = document.getElementById('btn-uang-pas');

            let rowIdx = {{ $resellerTransaction->details->count() }};
            let currentPriceMode = 'default'; 

            function showPriceModePopup() {
                Swal.fire({
                    title: 'Pilih Mode Harga',
                    html: `
                        <p>Pilih mode harga yang ingin diterapkan pada barang:</p>
                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-outline-danger price-mode-btn" data-mode="hpp">HPP</button>
                            <button type="button" class="btn btn-outline-danger price-mode-btn" data-mode="jual_potong">Harga Jual Per Potong</button>
                            <button type="button" class="btn btn-outline-danger price-mode-btn" data-mode="jual_lusin">Harga Jual Per Lusin</button>
                            <button type="button" class="btn btn-outline-danger price-mode-btn" data-mode="grosir">Harga Grosir</button>
                        </div>
                    `,
                    showConfirmButton: false,
                    allowOutsideClick: true,
                    didOpen: () => {
                        const content = Swal.getHtmlContainer();
                        const buttons = content.querySelectorAll('.price-mode-btn');
                        buttons.forEach(btn => {
                            btn.addEventListener('click', () => {
                                currentPriceMode = btn.getAttribute('data-mode');
                                Swal.close();
                                
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'info',
                                    title: 'Mode Aktif: ' + btn.textContent,
                                    showConfirmButton: false,
                                    timer: 3000
                                });

                                // Update Indicator and Header
                                document.getElementById('priceModeText').textContent = btn.textContent;
                                document.getElementById('detailPriceMode').textContent = '(Mode: ' + btn.textContent + ')';
                                document.getElementById('hargaHeader').textContent = btn.textContent;

                                // Refresh existing rows so price changes
                                document.querySelectorAll('.barang-input').forEach(input => {
                                    input.dispatchEvent(new Event('input'));
                                });
                            });
                        });
                    }
                });
            }

            document.getElementById('changePriceModeBtn').addEventListener('click', showPriceModePopup);

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
            }

            // Fungsi Filter Datalist + Sembunyikan barang yang sudah di-input di baris lain
            function updateDatalistFilter() {
                const selectedReseller = resellerSelect.value;
                const datalist = document.getElementById('barang-list');
                const options = datalist.querySelectorAll('option');
                
                // Kumpulkan nama barang yang sudah ada di kotak input tabel
                const usedItems = [];
                document.querySelectorAll('.barang-input').forEach(input => {
                    if (input.value.trim() !== "") {
                        usedItems.push(input.value.trim());
                    }
                });
                
                options.forEach(opt => {
                    const resellerId = opt.getAttribute('data-reseller-id');
                    const supplierId = opt.getAttribute('data-supplier-id');
                    const isUsed = usedItems.includes(opt.value);
                    
                    if ((resellerId === selectedReseller || (!resellerId && !supplierId)) && !isUsed) {
                        opt.disabled = false;
                    } else {
                        opt.disabled = true;
                    }
                });
            }

            resellerSelect.addEventListener('change', updateDatalistFilter);

            function calculateTotals() {
                let total = 0;
                document.querySelectorAll('.subtotal-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                
                totalDisplay.value = formatRupiah(total);
                
                const bayar = parseFloat(bayarInput.value) || 0;
                const sisa = bayar - total;
                
                sisaDisplay.value = (sisa < 0 ? '- ' : '') + formatRupiah(Math.abs(sisa));
                
                if (sisa > 0) {
                    sisaDisplay.classList.add('text-success'); sisaDisplay.classList.remove('text-danger', 'text-secondary');
                } else if (sisa < 0) {
                    sisaDisplay.classList.add('text-danger'); sisaDisplay.classList.remove('text-success', 'text-secondary');
                } else {
                    sisaDisplay.classList.add('text-secondary'); sisaDisplay.classList.remove('text-success', 'text-danger');
                }
            }

            btnUangPas.addEventListener('click', function() {
                let total = 0;
                document.querySelectorAll('.subtotal-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                bayarInput.value = total;
                calculateTotals();
            });

            function addRow() {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <input list="barang-list" class="form-control barang-input" placeholder="Cari atau ketik barang..." required>
                        <input type="hidden" name="details[${rowIdx}][barang_id]" class="barang-id-hidden">
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="text" class="form-control harga-display bg-light" readonly value="0" data-original-harga="0">
                        </div>
                    </td>
                    <td>
                        <input type="number" name="details[${rowIdx}][jumlah]" class="form-control jumlah-input" value="1" required min="1">
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="text" class="form-control subtotal-display bg-light" readonly value="0">
                            <input type="hidden" name="details[${rowIdx}][subtotal]" class="subtotal-input" value="0">
                            <input type="hidden" name="details[${rowIdx}][keuntungan]" class="keuntungan-input" value="0">
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fas fa-times"></i></button>
                    </td>
                `;
                tableBody.appendChild(tr);
                rowIdx++;
                initRowEvents(tr);
            }

            function initRowEvents(tr) {
                const barangInput = tr.querySelector('.barang-input');
                const barangIdHidden = tr.querySelector('.barang-id-hidden');
                const hargaDisplay = tr.querySelector('.harga-display');
                const jumlahInput = tr.querySelector('.jumlah-input');
                const subtotalDisplay = tr.querySelector('.subtotal-display');
                const subtotalInput = tr.querySelector('.subtotal-input');
                const removeBtn = tr.querySelector('.remove-row-btn');

                function updateRow() {
                    const val = barangInput.value;
                    const datalist = document.getElementById('barang-list');
                    const option = Array.from(datalist.options).find(opt => opt.value === val);
                    
                    let harga = 0;
                    if (option) {
                        barangIdHidden.value = option.getAttribute('data-id');
                        
                        if (currentPriceMode === 'default') {
                            // Cek jika ID masih sama dengan saat halaman load, maka gunakan harga asli
                            const originalId = tr.getAttribute('data-original-id');
                            if (originalId && originalId === barangIdHidden.value) {
                                const originalHarga = hargaDisplay.getAttribute('data-original-harga');
                                harga = parseFloat(originalHarga) || 0;
                            } else {
                                // Jika mode masih default tapi usernya ubah barang, fallback ke harga normal
                                harga = parseFloat(option.getAttribute('data-jual-potong')) || 0;
                            }
                        } else if (currentPriceMode === 'hpp') {
                            harga = parseFloat(option.getAttribute('data-hpp')) || 0;
                        } else if (currentPriceMode === 'jual_potong') {
                            harga = parseFloat(option.getAttribute('data-jual-potong')) || 0;
                        } else if (currentPriceMode === 'jual_lusin') {
                            harga = parseFloat(option.getAttribute('data-jual-lusin')) || 0;
                        } else if (currentPriceMode === 'grosir') {
                            harga = parseFloat(option.getAttribute('data-grosir')) || 0;
                        }

                        // Hitung keuntungan berdasarkan mode
                        let hargaBeli = 0;
                        if (currentPriceMode === 'jual_lusin') {
                            hargaBeli = parseFloat(option.getAttribute('data-beli-lusin')) || 0;
                        } else {
                            // Untuk jual_potong, grosir, hpp, default gunakan beli_potong
                            hargaBeli = parseFloat(option.getAttribute('data-beli-potong')) || 0;
                        }

                        const jumlah = parseFloat(jumlahInput.value) || 0;
                        const keuntungan = (harga - hargaBeli) * jumlah;
                        tr.querySelector('.keuntungan-input').value = keuntungan;
                    } else {
                        barangIdHidden.value = "";
                    }
                    
                    const jumlah = parseFloat(jumlahInput.value) || 0;
                    const subtotal = harga * jumlah;

                    hargaDisplay.value = new Intl.NumberFormat('id-ID').format(harga);
                    subtotalDisplay.value = new Intl.NumberFormat('id-ID').format(subtotal);
                    subtotalInput.value = subtotal;

                    calculateTotals();
                    updateDatalistFilter(); // Update datalist setiap ada perubahan input
                }

                barangInput.addEventListener('input', updateRow);
                jumlahInput.addEventListener('input', updateRow);
                
                removeBtn.addEventListener('click', () => { 
                    tr.remove(); 
                    calculateTotals(); 
                    updateDatalistFilter(); // Update datalist saat row dihapus agar barangnya tersedia lagi
                });
            }

            addRowBtn.addEventListener('click', addRow);
            bayarInput.addEventListener('input', calculateTotals);

            // Init event untuk baris yang sudah terisi saat pertama kali Edit dibuka
            document.querySelectorAll('#detailsTable tbody tr').forEach(tr => initRowEvents(tr));
            
            // Panggil filter di awal agar barang yang sudah di dalam tabel langsung terfilter (hilang) dari opsi
            updateDatalistFilter();
            calculateTotals();
        });
    </script>
</x-app-layout>