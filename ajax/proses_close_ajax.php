<?php
require_once '../config/database.php';
require_once '../config/telegram.php';

$token = TELEGRAM_BOT_TOKEN;

if (isset($_POST['id_tiket'])) {
    $id_tiket = $_POST['id_tiket'];
    $waktu_close = date('d/m/Y H:i:s');

    // --- PERBAIKAN QUERY: Ambil telegram_id dari tabel USERS ---
    $stmt = $db->prepare("SELECT t.*, u.telegram_id 
                          FROM tiket t 
                          LEFT JOIN users u ON t.id_teknisi = u.id_user AND u.role = 'teknisi' 
                          WHERE t.id_tiket = ?");
    $stmt->execute([$id_tiket]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Update status ke CLOSED
        // Saya tambahkan updated_at agar waktu close tercatat
        $upd = $db->prepare("UPDATE tiket SET status = 'CLOSED', updated_at = CURRENT_TIMESTAMP WHERE id_tiket = ?");
        
        if ($upd->execute([$id_tiket])) {
            // Mengubah format waktu created_at
            $waktu_buka = date('d/m/Y H:i:s', strtotime($data['created_at']));

            $message = "✅ *TIKET SELESAI: #{$id_tiket}*\n\n"
                     . "👤 *Pelanggan:* {$data['nama_pelanggan']}\n"
                     . "🛠 *Kendala:* {$data['kendala']}\n"
                     . "📥 *Waktu Buka:* $waktu_buka\n"
                     . "📤 *Waktu Close:* $waktu_close\n" 
                     . "🏁 *RESOLVED* \n\n"
                     . "Terima kasih atas kerja samanya! 🚀";

            // Kirim Notifikasi jika ada Telegram ID
            if (!empty($data['telegram_id'])) {
                $url = "https://api.telegram.org/bot$token/sendMessage?chat_id={$data['telegram_id']}&text=" . urlencode($message) . "&parse_mode=Markdown";
                @file_get_contents($url);
            }
            
            echo "success";
        }
    }
}
?>