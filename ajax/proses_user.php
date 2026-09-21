<?php
require_once '../config/database.php';

// Cek Login & Role
if (!isset($_SESSION['user_id'])) { die("Akses ditolak!"); }
if ($_SESSION['role'] !== 'superadmin') { die("Anda tidak memiliki izin."); }

 $action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- TAMBAH USER ---
if ($action == 'add') {
    $username   = $_POST['username'];
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama       = $_POST['nama_lengkap'];
    $role       = $_POST['role'];
    $telegram   = isset($_POST['telegram_id']) ? $_POST['telegram_id'] : '';

    if ($role == 'teknisi' && empty($telegram)) {
        die("Error: Teknisi wajib memiliki ID Telegram.");
    }

    $stmt = $db->prepare("INSERT INTO users (username, password, nama_lengkap, role, telegram_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $nama, $role, $telegram]);

} 
// --- EDIT USER ---
elseif ($action == 'edit') {
    $id         = $_POST['id_user'];
    $username   = $_POST['username'];
    $password   = $_POST['password'];
    $nama       = $_POST['nama_lengkap'];
    $role       = $_POST['role'];
    $telegram   = isset($_POST['telegram_id']) ? $_POST['telegram_id'] : '';

    if ($role == 'teknisi' && empty($telegram)) {
        die("Error: Teknisi wajib memiliki ID Telegram.");
    }

    // Cek apakah password diisi
    if (!empty($password)) {
        // Jika password diisi, update password (hash ulang)
        $new_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET username=?, password=?, nama_lengkap=?, role=?, telegram_id=? WHERE id_user=?");
        $stmt->execute([$username, $new_pass, $nama, $role, $telegram, $id]);
    } else {
        // Jika password kosong, jangan update password (biarkan yang lama)
        $stmt = $db->prepare("UPDATE users SET username=?, nama_lengkap=?, role=?, telegram_id=? WHERE id_user=?");
        $stmt->execute([$username, $nama, $role, $telegram, $id]);
    }

} 
// --- HAPUS USER ---
elseif ($action == 'delete') {
    $id = $_GET['id'];
    if ($id == $_SESSION['user_id']) { die("Tidak bisa menghapus diri sendiri."); }
    
    $stmtCheck = $db->prepare("SELECT username FROM users WHERE id_user = ?");
    $stmtCheck->execute([$id]);
    $user = $stmtCheck->fetch();
    
    if ($user && $user['username'] == 'enrocks') { die("User utama tidak boleh dihapus."); }

    $stmt = $db->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->execute([$id]);
}

header("Location: ../index.php");
exit();
?>