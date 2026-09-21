<!-- includes/modals.php -->

<!-- Modal Input Tiket -->
<div class="modal fade" id="modalTiket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-plus-circle-dotted text-primary me-2"></i>Buat Tiket Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formTiket">
                <div class="modal-body p-4">
                    <!-- Form Inputs (Nama, Kontak, Lokasi, Alamat, Kendala) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Nama Pelanggan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="pelanggan" class="form-control border-start-0" placeholder="Cth: Budi Santoso" required autofocus>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">No. WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-success"></i></span>
                                <input type="text" name="no_kontak" class="form-control border-start-0" placeholder="Kontak pelanggan" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">URL Lokasi (Maps)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-danger"></i></span>
                                <input type="url" name="url_lokasi" class="form-control border-start-0" placeholder="Link maps">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="2" placeholder="Jalan, No Rumah, RT/RW, Patokan" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Keluhan / Kendala</label>
                        <input type="text" name="kendala" class="form-control" placeholder="Cth: LOS, Modem Rusak, Putus Kabel" required>
                    </div>
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-10">
                        <label class="form-label small fw-bold text-muted mb-1">Petugas Input</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-check text-primary"></i></span>
                            <input type="text" name="admin" class="form-control bg-white border-start-0 text-muted fw-semibold" value="<?= $admin_nama ?>" readonly style="cursor: default; background-color: #f8fafc;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSimpanTiket" class="btn btn-primary px-4 fw-bold shadow-sm"><i class="bi bi-send-fill me-1"></i> Simpan Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Dispatch -->
<div class="modal fade" id="modalDispatch" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Pilih Teknisi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formDispatch" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_tiket" id="dispatch_id_tiket">
                    <label class="form-label text-muted small">Tim Teknisi Lapangan</label>
                    <select name="id_teknisi" class="form-select form-select-lg mb-3" required>
                        <option value="">-- Pilih Teknisi --</option>
                        
                        <!-- PERUBAHAN: Query ke tabel users -->
                        <?php 
                        $tech_options = $db->query("SELECT id_user AS id_teknisi, nama_lengkap AS nama_teknisi FROM users WHERE role = 'teknisi' ORDER BY nama_lengkap ASC")->fetchAll(PDO::FETCH_ASSOC);
                        foreach($tech_options as $tech): 
                        ?>
                            <option value="<?=$tech['id_teknisi']?>"><?=$tech['nama_teknisi']?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-info-circle me-1"></i> Notifikasi akan dikirim ke Telegram teknisi.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="btnKirimDispatch" class="btn btn-warning w-100 text-white fw-bold">Kirim Penugasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal User Management -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalUserTitle"><i class="bi bi-person-gear me-2"></i> Tambah User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="resetUserForm()"></button>
            </div>
            <div class="modal-body">
                <!-- Hidden ID untuk Edit -->
                <input type="hidden" name="id_user" id="userIdInput" value="">

                <form id="formUser" action="ajax/proses_user.php" method="POST" class="row g-2 mb-4 p-3 bg-light rounded border">
                    <input type="hidden" name="action" id="actionInput" value="add"> 
                    
                    <!-- Layout Grid Diperbaiki: 3 + 3 + 4 + 2 = 12 (Pas) -->
                    <div class="col-md-2"><input type="text" name="username" id="usernameInput" class="form-control form-control-sm" placeholder="Username" required></div>
                    
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="password" name="password" id="passwordInput" class="form-control form-control-sm" placeholder="Password" required>
                            <span class="input-group-text" style="cursor: pointer; padding: 0 8px;" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-md-3"><input type="text" name="nama_lengkap" id="namaInput" class="form-control form-control-sm" placeholder="Nama Lengkap" required></div>
                    
                    <div class="col-md-4">
                        <div class="row g-1">
                            <div class="col-8">
                                <select name="role" id="roleSelect" class="form-select form-control-sm">
                                    <option value="staff">Admin</option>
                                    <option value="superadmin">Superadmin</option>
                                    <option value="teknisi">Teknisi</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <button type="submit" id="btnSaveUser" class="btn btn-primary btn-sm w-100"><i class="bi bi-person-plus-fill"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Input Telegram ID -->
                    <div class="col-12" id="telegramField" style="display: none;">
                        <label class="small text-muted mb-1">ID Telegram (Wajib untuk Teknisi)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-telegram"></i></span>
                            <input type="text" name="telegram_id" id="telegramInput" class="form-control" placeholder="chat_id teknisi">
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr><th>Username</th><th>Nama Lengkap</th><th>Role / Telegram</th><th class="text-end">Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $users = $db->query("SELECT * FROM users ORDER BY id_user DESC")->fetchAll();
                            foreach($users as $u):
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary opacity-75"><?= $u['username'] ?></span></td>
                                <td class="fw-bold"><?= $u['nama_lengkap'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $u['role']=='superadmin'?'dark':($u['role']=='teknisi'?'info':'primary') ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                    <?php if(!empty($u['telegram_id'])): ?>
                                        <div class="small text-muted mt-1"><i class="bi bi-telegram"></i> <?= $u['telegram_id'] ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($u['username'] != 'enrocks'): ?>
                                        <!-- Tombol Edit: DATA PASSWORD DIHAPUS untuk mencegah error parsing -->
                                        <button class="btn btn-sm btn-outline-primary me-1 btn-edit-user border-0" 
                                                data-id="<?= $u['id_user'] ?>"
                                                data-username="<?= $u['username'] ?>"
                                                data-nama="<?= $u['nama_lengkap'] ?>"
                                                data-role="<?= $u['role'] ?>"
                                                data-telegram="<?= $u['telegram_id'] ?? '' ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <!-- Tombol Hapus -->
                                        <a href="ajax/proses_user.php?action=delete&id=<?= $u['id_user'] ?>" class="btn btn-outline-danger btn-sm border-0" onclick="return confirm('Hapus user ini?')"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal Logout -->
<div class="modal fade" id="modalLogout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-danger"><i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i></div>
                <h5 class="fw-bold">Konfirmasi Keluar</h5>
                <p class="text-muted small">Logout dari sistem ?</p>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light w-100 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <a href="logout.php" class="btn btn-danger w-100 fw-bold">Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Tiket -->
<div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Hapus Tiket?</h5>
                <p class="text-muted small">Data yang dihapus tidak dapat dikembalikan.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Batal</button>
                    <a id="btnLinkHapus" href="#" class="btn btn-danger w-100">Ya, Hapus</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Close Tiket -->
<div class="modal fade" id="modalKonfirmasiClose" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <i class="bi bi-check2-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Selesaikan Tiket?</h5>
                <p class="text-muted small">Tandai bahwa gangguan ini telah selesai diperbaiki.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Belum</button>
                    <button type="button" id="btnConfirmClose" class="btn btn-success w-100">Ya, Selesai</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sukses -->
<div class="modal fade" id="modalSukses" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body p-4">
                <div class="mb-3"><i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i></div>
                <h5 class="fw-bold">Berhasil!</h5>
                <p class="text-muted small">Penugasan telah dikirim ke Telegram teknisi.</p>
                <button type="button" class="btn btn-success w-100" data-bs-dismiss="modal">Oke Sip!</button>
            </div>
        </div>
    </div>
</div>
