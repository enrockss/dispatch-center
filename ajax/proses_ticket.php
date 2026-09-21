<?php
// 1. Panggil Koneksi
require_once '../config/database.php';

// 2. Cek apakah ada data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pelanggan'])) {
    
    // Ambil Data
    $pelanggan  = $_POST['pelanggan'];
    $no_kontak  = $_POST['no_kontak'];
    $url_lokasi = $_POST['url_lokasi']; 
    $alamat     = $_POST['alamat'];
    $kendala    = $_POST['kendala'];
    $admin      = $_POST['admin'];
    
    // PENTING: Set tanggal hari ini agar muncul di dashboard
    $created_at = date('Y-m-d H:i:s');

    try {
        // Query INSERT (Sesuaikan kolom dengan database Anda)
        $sql = "INSERT INTO tiket (nama_pelanggan, no_kontak, url_lokasi, alamat, kendala, nama_admin, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'OPEN', ?)";
        
        $stmt = $db->prepare($sql);
        
        // Eksekusi
        $execute = $stmt->execute([
            $pelanggan, 
            $no_kontak, 
            $url_lokasi, 
            $alamat, 
            $kendala, 
            $admin, 
            $created_at
        ]);

        if ($execute) {
            echo "success";
        } else {
            echo "Gagal eksekusi query.";
        }

    } catch (PDOException $e) {
        // Jika error kolom tidak ditemukan
        echo "SQL Error: " . $e->getMessage();
    }
    
    exit;
} else {
    echo "No POST data received";
}
?>