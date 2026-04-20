<x-app-layout>
    <div class="pc-container">
        <div class="pc-content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-users me-1"></i> Daftar Karyawan
                            </h5>

                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalKaryawan">
                                <i class="fas fa-user-plus me-1"></i> Tambah Karyawan
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="res-config" class="table table-striped table-bordered nowrap">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Jabatan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($karyawans as $k)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $k->nama }}</td>
                                            <td>{{ $k->jabatan ?? '-' }}</td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editKaryawan{{ $k->id }}">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <form action="{{ route('karyawan.destroy', $k->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Hapus karyawan ini?')">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editKaryawan{{ $k->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('karyawan.update', $k->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Karyawan</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Nama</label>
                                                                <input type="text" name="nama" class="form-control"
                                                                    value="{{ $k->nama }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Jabatan (Opsional)</label>
                                                                <input type="text" name="jabatan" class="form-control"
                                                                    value="{{ $k->jabatan }}">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan
                                                                Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="modalKaryawan" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('karyawan.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Karyawan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan (Opsional)</label>
                            <input type="text" name="jabatan" class="form-control" placeholder="Jabatan">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
        });
    </script>
    @endif
</x-app-layout>