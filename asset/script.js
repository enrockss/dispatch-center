$(document).ready(function() {
    
    // --- 1. VARIABEL GLOBAL & URL ---
    const urlParams = new URLSearchParams(window.location.search);
    const currentView = urlParams.get('view') || window.SERVER_VIEW || 'all';
    const currentDate = urlParams.get('tanggal') || window.SERVER_DATE || new Date().toISOString().slice(0, 10);
    let selectedTickets = [];

    // --- 2. SIDEBAR LOGIC (PERBAIKAN) ---
    // Menggunakan Class .sidebar sesuai CSS Anda
    const $sidebar = $('.sidebar');
    const $overlay = $('#sidebarOverlay');
    
    function toggleSidebar() {
        $sidebar.toggleClass('show'); // Menggunakan .show (sesuai CSS transform mobile)
        $overlay.toggleClass('active');
    }

    function closeSidebar() {
        $sidebar.removeClass('show');
        $overlay.removeClass('active');
        $('body').css('overflow', 'auto');
    }

    $('#btnSidebarToggle').on('click', function(e) {
        e.preventDefault();
        toggleSidebar();
    });

    $('#btnCloseSidebar, #sidebarOverlay').on('click', closeSidebar);

    // Tutup sidebar saat menu diklik di mobile
    $('.sidebar .nav-link').on('click', function() {
        if(!$(this).attr('data-bs-toggle') && $(window).width() < 992) {
            closeSidebar();
        }
    });

    // --- 3. MANAJEMEN TEKNISI (Gabungan) ---
    function loadTeknisi() {
        $("#listTeknisi").load("ajax/get_teknisi.php");
    }

    $('#modalTeknisi').on('show.bs.modal', function() {
        loadTeknisi();
        if ($(window).width() < 992) closeSidebar();
    });

    $(document).on("click", ".btn-edit-tech", function() {
        $("#tech_id").val($(this).data('id')); 
        $("#tech_nama").val($(this).data('nama'));
        $("#tech_tele").val($(this).data('tele'));
        const btn = $("#btnSimpanTeknisi");
        btn.text("Update").removeClass("btn-primary").addClass("btn-info");
    });

    $("#formTeknisi").on("submit", function(e) {
        e.preventDefault();
        const btn = $("#btnSimpanTeknisi");
        if(btn.prop("disabled")) return false;

        btn.prop("disabled", true).text("...");
        $.post("ajax/proses_teknisi_ajax.php", $(this).serialize(), function() {
            $("#formTeknisi")[0].reset();
            $("#tech_id").val("");
            btn.prop("disabled", false).text("Simpan").removeClass("btn-info").addClass("btn-primary");
            loadTeknisi();
        }).fail(function() {
            alert("Gagal menyimpan teknisi.");
            btn.prop("disabled", false).text("Simpan");
        });
    });

    $(document).on("click", ".btn-hapus-tech", function() {
        if (confirm("Hapus data teknisi ini?")) {
            const id = $(this).data('id');
            $.post("hapus_teknisi.php", { id_teknisi: id }, function() {
                loadTeknisi();
            });
        }
    });

    // --- 4. MANAJEMEN TIKET ---
    
    // Buat Tiket Baru
    $("#formTiket").on("submit", function(e) {
        e.preventDefault();
        const btn = $("#btnSimpanTiket");
        if(btn.prop("disabled")) return false;

        btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Simpan...');
        
        $.post("ajax/proses_ticket.php", $(this).serialize(), function(res) {
            // Anggap success jika tidak ada error text dari PHP
            if(res.trim() === 'success' || res.trim().length < 5) { 
                 location.href = `index.php?view=${currentView}&tanggal=${currentDate}`;
            } else {
                alert("Gagal menyimpan: " + res);
                btn.prop("disabled", false).html('Simpan');
            }
        }).fail(function() {
            alert("Koneksi error.");
            btn.prop("disabled", false).html('Simpan');
        });
    });

    // Checkbox Selection
    $(document).on('change', '.ticket-checkbox', function() {
        selectedTickets = $('.ticket-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        const count = selectedTickets.length;
        if (count > 0) {
            $('#btnMultiDispatch').removeClass('d-none');
            $('#selectedCount').text(count);
        } else {
            $('#btnMultiDispatch').addClass('d-none');
        }
    });

    // Buka Modal Dispatch
    $(document).on("click", ".btn-dispatch, #btnMultiDispatch", function() {
        const singleId = $(this).data('id'); 
        if(!singleId && selectedTickets.length === 0) {
            alert("Pilih tiket terlebih dahulu!");
            return;
        }
        // Reset form & button state
        $("#formDispatch")[0].reset();
        $("#btnKirimDispatch").prop("disabled", false).text("Kirim Penugasan");
        
        $("#dispatch_id_tiket").val(singleId ? singleId : selectedTickets.join(','));
        
        // Buka modal via Bootstrap API
        var myModal = new bootstrap.Modal(document.getElementById('modalDispatch'));
        myModal.show();
    });


   // --- 5. PROSES DISPATCH (SATU HANDLER YANG BENAR) ---
    // Kita menggunakan .off() untuk memastikan tidak ada duplikasi event
    $(document).off("submit", "#formDispatch").on("submit", "#formDispatch", function(e) {
        e.preventDefault();
        
        const btn = $("#btnKirimDispatch");
        const formData = $(this).serialize();

        // Cek double submit
        if(btn.prop("disabled")) return false;

        // UI Loading
        btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...');

        $.ajax({
            url: "ajax/proses_dispatch_ajax.php",
            type: "POST",
            data: formData,
            success: function(res) {
    if(res.trim() === 'success') {
        const modalEl = document.getElementById('modalDispatch');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);

        // 1. PASTIKAN Tombol Reload Bersih (Reset listener lama agar tidak dobel)
        $("#modalSukses").off('hidden.bs.modal');

        // 2. LISTENER: Tunggu Modal Dispatch BENAR-BENAR TUTUP
        modalEl.addEventListener('hidden.bs.modal', function () {
            
            // --- BERSIHKAN DULU (Agar tidak Freeze) ---
            $('.modal-backdrop').remove();             // Hapus backdrop Dispatch
            $('#sidebarOverlay').removeClass('active'); // Pastikan overlay sidebar mati
            $('body').removeClass('modal-open').css({  // Reset body CSS
                'overflow': '', 
                'padding-right': '' 
            });

            // --- BARU TAMPILKAN MODAL SUKSES ---
            const modalSukses = new bootstrap.Modal(document.getElementById('modalSukses'));
            modalSukses.show();

        }, { once: true }); // 'once: true' sangat penting agar listener tidak menumpuk jika diklik berkali-kali

        // 3. TRIGGER: Mulai proses menutup Modal Dispatch
        modalInstance.hide();

        // 4. LOGIKA RELOAD: Saat Modal Sukses ditutup user
        $("#modalSukses").on('hidden.bs.modal', function () {
            location.reload();
        });

    } else {
        alert("Gagal: Server merespon " + res);
        btn.prop("disabled", false).text("Kirim Penugasan");
    }
},
            error: function() {
                alert("Terjadi kesalahan koneksi.");
                btn.prop("disabled", false).text("Kirim Penugasan");
            }
        });
   
    });



    
    // --- 6. HAPUS & SELESAI TIKET ---
    
    // Trigger Hapus
    $(document).on("click", ".btn-trigger-hapus", function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const targetUrl = `includes/hapus_tiket.php?id=${id}&view=${currentView}&tanggal=${currentDate}`;
        $("#btnLinkHapus").attr("href", targetUrl); 
        var modalHapus = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus'));
        modalHapus.show();
    });

    // Trigger Selesai
    let idTiketSelesai = null;
    $(document).on("click", ".btn-trigger-close", function() {
        idTiketSelesai = $(this).data('id');
        var modalClose = new bootstrap.Modal(document.getElementById('modalKonfirmasiClose'));
        modalClose.show();
    });

    $("#btnConfirmClose").on("click", function() {
        const btn = $(this);
        if(btn.prop("disabled")) return false;

        btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');
        
        $.post("ajax/proses_close_ajax.php", { id_tiket: idTiketSelesai }, function(res) {
            if(res.trim() === 'success') {
                location.href = `index.php?view=${currentView}&tanggal=${currentDate}`;
            } else {
                alert("Gagal menyelesaikan tiket.");
                btn.prop("disabled", false).text("Ya, Selesai");
            }
        });
    });

    // Auto Refresh (Opsional, 10 menit)
    setInterval(function() {
        if ($('.modal.show').length === 0) {
            // location.reload(); // Dinonaktifkan sementara agar tidak mengganggu testing
        }
    }, 600000);

    $(document).on("click", ".btn-delete-user", function() {
    const id = $(this).data('id');
    const urlHapus = `ajax/proses_user.php?action=delete&id=${id}`;
    $("#btnLinkHapusUser").attr("href", urlHapus);
    $("#confirmDeleteUser").modal('show');
});


});

 $(document).ready(function() {
    // 1. Fungsi Toggle Password (Show/Hide)
    window.togglePassword = function() {
        var input = document.getElementById("passwordInput");
        var icon = document.getElementById("toggleIcon");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("bi-eye", "bi-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("bi-eye-slash", "bi-eye");
        }
    };

    // 2. Toggle Telegram Field
    function checkRole() {
        var role = $('#roleSelect').val();
        if(role === 'teknisi') {
            $('#telegramField').slideDown();
            $('#telegramInput').prop('required', true);
        } else {
            $('#telegramField').slideUp();
            $('#telegramInput').prop('required', false).val('');
        }
    }
    $('#roleSelect').on('change', checkRole);

    // 3. Reset Form (Mode Tambah)
    window.resetUserForm = function() {
        $('#formUser')[0].reset();
        $('#userIdInput').val('');
        $('#actionInput').val('add');
        
        // Reset Password UI
        document.getElementById("passwordInput").type = "password"; 
        document.getElementById("toggleIcon").classList.replace("bi-eye-slash", "bi-eye");
        
        // Reset Tampilan ke Mode Tambah
        $('#modalUserTitle').html('<i class="bi bi-person-gear me-2"></i> Tambah User');
        $('#btnSaveUser').removeClass('btn-warning').addClass('btn-primary').html('<i class="bi bi-plus"></i>');
        
        $('#passwordInput').prop('required', true); 
        $('#passwordInput').attr('placeholder', 'Password');
        
        checkRole();
    };

    // 4. Handle Click Tombol Edit
    $('.btn-edit-user').on('click', function() {
        var data = $(this).data();
        
        // Isi Form dengan Data
        $('#userIdInput').val(data.id);
        $('#usernameInput').val(data.username);
        // PASSWORD DIKOSONGKAN MANUAL (Karena data.password sudah tidak ada di HTML)
        $('#passwordInput').val(''); 
        $('#namaInput').val(data.nama);
        $('#roleSelect').val(data.role);
        $('#telegramInput').val(data.telegram);

        // Reset Password UI
        document.getElementById("passwordInput").type = "password";
        document.getElementById("toggleIcon").classList.replace("bi-eye-slash", "bi-eye");

        // Ubah Mode ke Edit
        $('#actionInput').val('edit');
        $('#modalUserTitle').html('<i class="bi bi-pencil-square me-2"></i> Edit User');
        $('#btnSaveUser').removeClass('btn-primary').addClass('btn-warning').html('<i class="bi bi-check-lg"></i>'); 
        
        $('#passwordInput').prop('required', false); 
        $('#passwordInput').attr('placeholder', 'password baru');

        // Trigger role select
        checkRole();
    });
});


 // PERBAIKAN MODAL STUCK (Backdrop)
    $('#modalDispatch').on('hidden.bs.modal', function () {
        // 1. Hapus SEMUA backdrop modal (jika ada lebih dari 1)
        $('.modal-backdrop').remove();
        
        // 2. PASTIKAN Sidebar Overlay mati total (Ini sering jadi biang kerok "Freeze" di mobile)
        $('#sidebarOverlay').removeClass('active');
        
        // 3. Reset kondisi Body agar bisa di-scroll dan diklik
        $('body').removeClass('modal-open');
        $('body').css({
            'padding-right': '',
            'overflow': '',        // Menghilangkan lock scroll
            'pointer-events': 'auto' // Memastikan body bisa diklik
        });
    });
