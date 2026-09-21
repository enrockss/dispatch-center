<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $view = $_GET['view'] ?? 'all';
    $tgl = $_GET['tanggal'] ?? date('Y-m-d');
    $tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl) ? $tgl : date('Y-m-d');

    // Ambil data tiket + telegram_id teknisi SEBELUM dihapus,
    // supaya kalau tiket sudah didispatch, teknisinya bisa dikabari.
    $stmt = $db->prepare("SELECT t.*, u.telegram_id
                          FROM tiket t
                          LEFT JOIN users u ON t.id_teknisi = u.id_user AND u.role = 'teknisi'
                          WHERE t.id_tiket = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $del = $db->prepare("DELETE FROM tiket WHERE id_tiket = ?");
    $del->execute([$id]);

    // Kirim notif "dibatalkan" HANYA jika tiket sudah didispatch (status ASSIGNED)
    // dan teknisinya punya telegram_id. Tiket OPEN/CLOSED tidak perlu notif ini.
    if ($data && $data['status'] === 'ASSIGNED' && !empty($data['telegram_id'])) {
        $token = "8391361473:AAE0L64Y68-w2kO5MtjkZaqfXi4Yp0Uq_AI";

        $message = "🚫 *TIKET DIBATALKAN: #{$id}*\n\n"
                 . "👤 *Pelanggan:* {$data['nama_pelanggan']}\n"
                 . "🛠 *Kendala:* {$data['kendala']}\n\n"
                 . "Tiket ini telah dihapus oleh admin. Mohon hentikan pengerjaan jika sudah berjalan.";

        $url = "https://api.telegram.org/bot$token/sendMessage?chat_id={$data['telegram_id']}&text=" . urlencode($message) . "&parse_mode=Markdown";
        @file_get_contents($url);
    }
}

// Kembali ke halaman asal dengan parameter tab & tanggal tetap terjaga
header("Location: ../index.php?view=" . urlencode($view) . "&tanggal=" . urlencode($tgl));
exit;
?>
