<?php
// config/database.php
session_start();

// Setting Timezone
date_default_timezone_set('Asia/Jakarta');

// Menggunakan __DIR__ untuk memastikan path selalu benar
// __DIR__ = folder tempat file ini berada (config/)
// dirname(__DIR__) = naik satu level dari folder config (yaitu root project)
 $dbPath = dirname(__DIR__) . '/dbs/ticketing_isp.db';

// Koneksi Database
try {
    $db = new PDO("sqlite:" . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Tampilkan pesan error beserta path yang dicoba untuk debugging
    die("Koneksi Database Gagal: " . $e->getMessage() . "<br>Path: " . $dbPath);
}
?>