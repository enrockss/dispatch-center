<!-- includes/sidebar.php -->
 
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="?view=all" class="sidebar-brand">
            <i class="bi bi-ethernet"></i> Dispatch Center
        </a>
        <button class="btn-close-sidebar" id="btnCloseSidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="px-4 py-3 border-bottom border-secondary border-opacity-10 bg-black bg-opacity-10">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                 style="width: 35px; height: 35px; font-size: 0.9rem;">
                <?= strtoupper(substr($admin_nama, 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-white text-truncate" style="font-size: 0.8rem;"><?= $admin_nama ?></div>
                <div class="text-warning small" style="font-size: 0.7rem;"><i class="bi bi-dot text-success"></i> <?= ucfirst($admin_role) ?></div>
            </div>
        </div>
    </div>
    <div class="nav-section">
        <div class="nav-label">Menu Utama</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= $view=='all'?'active':'' ?>" href="?view=all&tanggal=<?=$tgl?>">
                <span><i class="bi bi-grid-fill text-primary"></i> Dashboard</span>
            </a>
        
            

            <!-- MENU TIKET OPEN -->
    <a class="nav-link <?= $view=='open'?'active':'' ?>" href="?view=open&tanggal=<?=$tgl?>">
     <span>
    <i class="bi bi-exclamation-triangle-fill text-danger"></i> Tiket Open
     </span>
        <!-- TAMBAHKAN KONDISI: Jika teknisi, jangan tampilkan badge -->
        <?php if($admin_role != 'teknisi' && $count_open > 0): ?>
        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;"><?=$count_open?></span>
        <?php endif; ?>
        </a>
            
            <!-- MENU TIKET DISPATCHED -->
<a class="nav-link <?= $view=='dispatched'?'active':'' ?>" href="?view=dispatched&tanggal=<?=$tgl?>">
    <span>
        <i class="bi bi-person-fill-check text-warning"></i> Tiket Dispatched
    </span>
    <!-- TAMBAHKAN KONDISI: Jika teknisi, jangan tampilkan badge -->
    <?php if($admin_role != 'teknisi' && $count_disp > 0): ?>
        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.65rem;"><?=$count_disp?></span>
    <?php endif; ?>
</a>
            
            <a class="nav-link <?= $view=='closed'?'active':'' ?>" href="?view=closed&tanggal=<?=$tgl?>">
                <span>
                    <i class="bi bi-check-square-fill text-success"></i> Tiket Closed
                </span>
            </a>

            <div class="nav-label mt-4">System</div>
            <nav class="nav flex-column">
                <?php if($_SESSION['role'] === 'superadmin'): ?>
                    
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalUser">
                        <span><i class="bi bi-shield-lock-fill text-warning"></i> Manajemen User</span>
                    </a>
                <?php endif; ?>

                <a class="nav-link text-danger mt-2" href="#" data-bs-toggle="modal" data-bs-target="#modalLogout">
                    <span><i class="bi bi-box-arrow-left"></i> Logout</span>
                </a>
            </nav>
        </nav>
    </div>
</div>