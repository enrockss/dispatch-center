<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';
require_once 'includes/functions.php';

// 1. AMBIL DATA ADMIN
 $admin_id   = $_SESSION['user_id'];
 $admin_nama = $_SESSION['nama_lengkap'];
 $admin_role = $_SESSION['role'];

// 2. NAVIGASI & FILTER
 $view = $_GET['view'] ?? 'all';
 $tgl = $_GET['tanggal'] ?? date('Y-m-d');
 $tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl) ? $tgl : date('Y-m-d');
 $filter_tech = $_GET['teknisi'] ?? 'all';

// 3. LOGIKA QUERY DINAMIS
if ($admin_role == 'teknisi') {
    // --- KHUSUS TEKNISI ---
    
    // 1. Filter: Hanya milik sendiri
    $conditions[] = "t.id_teknisi = :my_tech_id";
    
    // 2. Filter Status
    if ($view == 'all') {
        // PERUBAHAN: Dashboard Teknisi HANYA menampilkan Tiket Dispatched (Yang sedang dikerjakan)
        $conditions[] = "status = 'ASSIGNED'"; 
    } elseif ($view == 'open') {
        // Teknisi tidak boleh melihat tiket Open
        $conditions[] = "1=0"; 
    } elseif ($view == 'dispatched') {
        $conditions[] = "status = 'ASSIGNED'";
    } elseif ($view == 'closed') {
        $conditions[] = "status = 'CLOSED'";
        // Filter tanggal tetap aktif untuk history Closed
        $conditions[] = "date(created_at) = :tgl";
    }

} else {
    // --- ADMIN & SUPERADMIN (Logika Tetap Sama) ---
    if ($view == 'all') {
        $conditions[] = "status IN ('OPEN', 'ASSIGNED')";
    } elseif ($view == 'open') {
        $conditions[] = "status = 'OPEN'";
    } elseif ($view == 'dispatched') {
        $conditions[] = "status = 'ASSIGNED'";
    } elseif ($view == 'closed') {
        $conditions[] = "status = 'CLOSED'";
        $conditions[] = "date(created_at) = :tgl";
    }

    if ($filter_tech != 'all') {
        $conditions[] = "t.id_teknisi = :id_tech";
    }
}

// QUERY UTAMA (Sama untuk semua role)
 $sql = "SELECT t.*, u.nama_lengkap AS nama_teknisi 
        FROM tiket t 
        LEFT JOIN users u ON t.id_teknisi = u.id_user AND u.role = 'teknisi'
        WHERE " . implode(" AND ", $conditions) . " ORDER BY t.id_tiket DESC";
        
 $stmt = $db->prepare($sql);

// PARAMETER BINDING
 $params = [];

// Jika teknisi, bind ID user-nya
if ($admin_role == 'teknisi') {
    $params[':my_tech_id'] = $admin_id;
}

// Jika view closed, bind tanggal
if ($view == 'closed') { 
    $params[':tgl'] = $tgl; 
}

// Jika Admin/Superadmin filter teknisi, bind ID teknisi
if ($admin_role != 'teknisi' && $filter_tech != 'all') { 
    $params[':id_tech'] = $filter_tech; 
}

 $stmt->execute($params);
 $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
// 4. DATA COUNTER SIDEBAR
// Total Backlog (tanpa filter tanggal)
 $count_open = $db->query("SELECT count(*) FROM tiket WHERE status='OPEN'")->fetchColumn();
 $count_disp = $db->query("SELECT count(*) FROM tiket WHERE status='ASSIGNED'")->fetchColumn();

// 5. DROPDOWN TEKNISI (Sumber Data: Tabel USERS)
// Alias digunakan agar HTML tidak perlu diubah
 $teknisi_list = $db->query("SELECT id_user AS id_teknisi, nama_lengkap AS nama_teknisi 
                              FROM users 
                              WHERE role = 'teknisi' 
                              ORDER BY nama_lengkap ASC")->fetchAll(PDO::FETCH_ASSOC);

// 6. INCLUDE BAGIAN HTML
require_once 'includes/head.php'; ?>

<div class="main-content">
    
    <!-- Sidebar -->
    <?php require_once 'includes/sidebar.php'; ?>

    <!-- Top Bar Header -->
    <div class="page-header">
        
        
        <!-- BAGIAN KIRI: Judul -->
        <div class="d-flex align-items-center gap-2 overflow-hidden">
            <button class="btn-sidebar-toggle d-lg-none" id="btnSidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="page-title">
                <h2 class="d-flex align-items-center gap-2 mb-0" style="font-size: 1.25rem;">
                    <i class="bi bi-layers-half text-primary d-none d-sm-inline"></i> 
                    Ticket <?= ucfirst($view) ?>
                </h2>
                 <p class="mb-0 small text-muted">Enrocks Maintenance Ticket System</p>
            </div>
        </div>

        <!-- BAGIAN KANAN: Tanggal (Desktop) & Tombol Buat Tiket -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            
           
            

            <?php if($admin_role != 'teknisi'): ?>
            <button class="btn-add-ticket" data-bs-toggle="modal" data-bs-target="#modalTiket">
                <i class="bi bi-plus-circle-fill"></i> 
                <span>Buat Tiket</span>
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Filters Toolbar -->
        <div class="bg-white rounded shadow-sm p-2 p-md-3 mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        
        <!-- GRUP KIRI: Filter Teknisi & Tanggal -->
        <div class="d-flex flex-wrap gap-2 align-items-center">
            
            <!-- Filter Teknisi (Hanya Admin) -->
            <?php if($admin_role != 'teknisi'): ?>
            <div class="input-group input-group-sm" style="width: 190px;">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-person-badge text-secondary"></i>
                </span>
                <select class="form-select border-start-0 ps-0 bg-light" 
                        onchange="location.href='?view=<?=htmlspecialchars($view)?>&tanggal=<?=htmlspecialchars($tgl)?>&teknisi='+this.value" 
                        style="font-size: 0.85rem;">
                    <option value="all">Semua Teknisi</option>
                    <?php foreach($teknisi_list as $tech): ?>
                        <option value="<?=$tech['id_teknisi']?>" <?= $filter_tech == $tech['id_teknisi'] ? 'selected' : '' ?>>
                            <?=htmlspecialchars($tech['nama_teknisi'])?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>       
            
            <!-- Filter Tanggal -->
            <div class="input-group input-group-sm" style="width: 160px;">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-calendar3 text-secondary"></i>
                </span>
                <input type="date" class="form-control border-start-0 ps-0 bg-light" value="<?=htmlspecialchars($tgl)?>" 
                       onchange="location.href='?view=<?=htmlspecialchars($view)?>&teknisi=<?=htmlspecialchars($filter_tech)?>&tanggal='+this.value"
                       style="font-size: 0.85rem;">
            </div>
        </div>

        <!-- GRUP KANAN: Tombol Multi Dispatch -->
        <!-- Menggunakan ms-auto di mobile agar nempel kanan, tapi di desktop diatur oleh justify-content-between -->
        <div class="ms-md-0">
            <?php if($admin_role != 'teknisi'): ?>
            <button id="btnMultiDispatch" class="btn btn-dark btn-sm d-none fw-bold shadow-sm py-2 px-3" 
                    data-bs-toggle="modal" data-bs-target="#modalDispatch">
                <i class="bi bi-send-check-fill me-1"></i>
                Tugaskan (<span id="selectedCount">0</span>)
            </button>
            <?php endif; ?>
        </div>
    </div>


    <!-- Ticket Grid -->
    <!-- PERUBAHAN: Kembali ke xl-5 dan g-3 agar tampilan stabil seperti versi awal -->
    <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
        <?php if(count($tickets) > 0): ?>
            <?php foreach($tickets as $t): ?>
            <div class="col">
                <div class="card card-ticket card-<?=$t['status']?>">
                    
                    <!-- Checkbox (Hanya Admin) -->
                    <?php if($admin_role != 'teknisi'): ?>
                    <?php if($t['status'] != 'CLOSED'): ?>
                    <div class="form-check position-absolute" style="top: 10px; left: 12px; z-index: 10;">
                        <input class="form-check-input ticket-checkbox" type="checkbox" value="<?=$t['id_tiket']?>" style="width: 1rem; height: 1rem; cursor: pointer;">
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="<?= ($t['status'] != 'CLOSED') ? 'ms-4' : '' ?>">
                                <div class="ticket-id mb-1 text-uppercase">#<?=$t['id_tiket']?></div>
                                <div class="status-badge bg-<?=$t['status']?>"><?=$t['status']?></div>
                                <div class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">
                                    <i class="bi bi-clock"></i> 
                                    <?= date('d M H:i', strtotime($t['created_at'])) ?>
                                </div>
                            </div>

				<!-- TAMBAHAN: Nama Admin Creator -->
                                <div class="text-muted mt-1" style="font-size: 0.7rem; line-height: 1.2;">
                                    <i class="bi bi-person-badge"></i> 
                                    <!-- Ganti 'nama_admin' dengan nama kolom database Anda -->
                                    <span class="text-truncate d-inline-block" style="max-width: 120px; vertical-align: bottom;">
                                        <?= !empty($t['nama_admin']) ? $t['nama_admin'] : 'Admin' ?>
                                    </span>


                                </div>                            
                            <div class="dropdown">
                                <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" style="text-decoration: none;">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <!-- Hapus (Hanya Admin) -->
                                    <?php if($admin_role != 'teknisi'): ?>
                                    <li>
                                        <a class="dropdown-item text-danger btn-trigger-hapus small" 
                                           href="javascript:void(0)" 
                                           data-id="<?=$t['id_tiket']?>" 
                                           data-view="<?=htmlspecialchars($view)?>" 
                                           data-tanggal="<?=htmlspecialchars($tgl)?>">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-title text-truncate fw-bold" title="<?=htmlspecialchars($t['nama_pelanggan'])?>" style="font-size: 1.05rem;"><?=htmlspecialchars($t['nama_pelanggan'])?></div>

                        <div class="d-flex gap-2 mb-2">
                            <a href="https://wa.me/<?= normalize_wa_number($t['no_kontak']) ?>" 
                               target="_blank" 
                               class="badge bg-success-subtle text-success text-decoration-none" 
                               style="font-size: 0.75rem;">
                                <i class="bi bi-whatsapp"></i> Chat
                            </a>
                            <?php if(!empty($t['url_lokasi'])): ?>
                            <a href="<?= $t['url_lokasi'] ?>" target="_blank" class="badge bg-primary-subtle text-primary text-decoration-none" style="font-size: 0.75rem;">
                                <i class="bi bi-geo-alt-fill"></i> Lokasi
                            </a>
                            <?php endif; ?>
                        </div>

                       <div class="ticket-address text-truncate text-muted">
                            <i class="bi bi-house-door"></i> <?=htmlspecialchars($t['alamat'])?>
                        </div>

                        <div class="kendala-box"><?=htmlspecialchars($t['kendala'])?></div>
                        
                        <div class="card-footer-custom">
                            <?php if($t['status'] == 'OPEN'): ?>
                                <?php if($admin_role != 'teknisi'): ?>
                                <button class="btn btn-warning btn-sm btn-dispatch text-truncate w-100 fw-bold shadow-sm" 
 				        data-id="<?=$t['id_tiket']?>" 
        				data-bs-toggle="modal" 
      					data-bs-target="#modalDispatch">
    					<i class="bi bi-send-check-fill"></i> TUGASKAN
					</button>
                                <?php else: ?>
                                <span class="badge bg-secondary w-100 text-center">Belum Ditugaskan</span>
                                <?php endif; ?>

                            <?php elseif($t['status'] == 'ASSIGNED'): ?>
                                <div class="w-100">
                                    <div class="d-flex align-items-center mb-2"> 
                                        <span class="badge bg-light text-dark text-truncate w-100 text-start" style="font-size: 1.1rem; border: 1px solid #ddd; padding: 5px 12px;">
                                            <i class="bi bi-person-fill text-warning me-2"></i><?= !empty($t['nama_teknisi']) ? explode(' ', $t['nama_teknisi'])[0] : 'Anonim' ?>
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-success btn-sm flex-grow-1 fw-bold btn-trigger-close shadow-sm" data-id="<?=$t['id_tiket']?>">
                                            <i class="bi bi-check-circle"></i> SELESAI
                                        </button>
                                        
                                        <?php if($admin_role != 'teknisi'): ?>
                                        <button class="btn btn-outline-warning btn-sm btn-dispatch px-3" 
                                                data-id="<?=$t['id_tiket']?>" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDispatch" 
                                                title="Pindah Teknisi">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php else: // STATUS CLOSED ?>
                                <div class="w-100">
                                    <div class="alert alert-success py-2 px-3 mb-0 border-0 d-flex align-items-center justify-content-center" 
                                         style="background-color: #f0fdf4; color: #166534; border-radius: 8px; font-size: 0.8rem;">
                                        <div class="text-center">
                                            <div class="fw-bold"><i class="bi bi-check-square-fill"></i> RESOLVED</div>
                                            <div class="small opacity-75 mt-1" style="border-top: 1px dashed #bbf7d0; pt:1">
                                                 Teknisi: <?= !empty($t['nama_teknisi']) ? explode(' ', $t['nama_teknisi'])[0] : '-' ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" alt="Empty" style="opacity:0.3; margin-bottom:15px;">
                <h5 class="text-muted">Tidak ada tiket ditemukan</h5>
                <p class="text-muted small">Silakan ganti filter tanggal atau buat tiket baru.</p>
            </div>
        <?php endif; ?>
    </div>
<!-- Include Modals -->
<?php require_once 'includes/modals.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.SERVER_VIEW = "<?= htmlspecialchars($view, ENT_QUOTES) ?>";
    window.SERVER_DATE = "<?= htmlspecialchars($tgl, ENT_QUOTES) ?>";
</script>
<script src="asset/script.js"></script>

</body>
</html>
