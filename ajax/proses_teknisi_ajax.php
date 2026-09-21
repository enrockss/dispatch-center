<?php
require_once '../config/database.php';

if ($_POST) {
    // Pastikan nama variabel di $_POST sama dengan name="" di atribut HTML <input>
    $id    = $_POST['id_teknisi'] ?? ''; 
    $nama  = $_POST['nama'];
    $tele  = $_POST['tele_id'];
if ($_SESSION['role'] !== 'superadmin') {
    header("Location: index.php?error=unauthorized");
    exit();
}
    if (!empty($id)) {
        // Logika Update
        $stmt = $db->prepare("UPDATE teknisi SET nama_teknisi = ?, telegram_id = ? WHERE id_teknisi = ?");
        $result = $stmt->execute([$nama, $tele, $id]);
    } else {
        // Logika Insert
        $stmt = $db->prepare("INSERT INTO teknisi (nama_teknisi, telegram_id, status_aktif) VALUES (?, ?, 1)");
        $result = $stmt->execute([$nama, $tele]);
    }

    if ($result) {
        echo "success";
    } else {
        echo "failed";
    }
}
?>