<?php
require_once '../config/database.php';
require_once '../config/telegram.php';
require_once '../includes/functions.php';
// Sembunyikan error agar tidak merusak respon JSON/AJAX
error_reporting(0);

try {
    // Variabel $db sudah diambil dari config/database.php
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $token = TELEGRAM_BOT_TOKEN;

    if (isset($_POST['id_tiket']) && isset($_POST['id_teknisi'])) {
        $id_input = $_POST['id_tiket'];
        $id_tech  = $_POST['id_teknisi']; // Ini sekarang adalah id_user
        $ids = explode(',', $id_input); 
        
        // --- PERUBAHAN QUERY TEKNISI ---
        // Mengambil data dari tabel USERS (bukan tabel teknisi lama)
        // Kita alias 'nama_lengkap' menjadi 'nama_teknisi' agar variabel di bawah konsisten
        $stmt_tk = $db->prepare("SELECT nama_lengkap AS nama_teknisi, telegram_id 
                                 FROM users 
                                 WHERE id_user = ? AND role = 'teknisi'");
        $stmt_tk->execute([$id_tech]);
        $tk = $stmt_tk->fetch(PDO::FETCH_ASSOC);

        if ($tk && !empty($tk['telegram_id'])) {
            $success_count = 0;

            foreach ($ids as $id_tiket) {
                $id_tiket = trim($id_tiket);
                
                // Ambil detail tiket
                $stmt_t = $db->prepare("SELECT * FROM tiket WHERE id_tiket = ?");
                $stmt_t->execute([$id_tiket]);
                $t = $stmt_t->fetch(PDO::FETCH_ASSOC);

                if ($t) {
                    // Update status tiket
                    // CATATAN: Jika tabel tiket Anda punya kolom 'updated_at', tambahkan ', updated_at = CURRENT_TIMESTAMP' di set
                    $upd = $db->prepare("UPDATE tiket SET id_teknisi = ?, status = 'ASSIGNED' WHERE id_tiket = ?");
                    
                    if ($upd->execute([$id_tech, $id_tiket])) {
                        $success_count++;

                        // Pembersihan nomor WhatsApp
                        $wa_number = normalize_wa_number($t['no_kontak']);
                        if (substr($wa_number, 0, 1) === '0') {
                            $wa_number = '62' . substr($wa_number, 1);
                        }

                        // Susun Pesan (Variabel $tk['nama_teknisi'] tetap bekerja karena kita pakai Alias di SQL)
                        $message = "‼️ *PENUGASAN BARU: #{$id_tiket}*\n\n"
                                 . "👤 *Pelanggan:* {$t['nama_pelanggan']}\n"
                                 . "📞 *Kontak:* [{$t['no_kontak']}](https://wa.me/{$wa_number})\n"
                                 . "📍 *Alamat:* {$t['alamat']}\n"
                                 . "🛠 *Kendala:* {$t['kendala']}\n";

                        if (!empty($t['url_lokasi'])) {
                            $message .= "🗺 *Lokasi:* [Klik Buka Map](" . $t['url_lokasi'] . ")\n";
                        }

                        if(count($ids) > 1) {
                            $message .= "\n_(Multi dispatch)_";
                        }

                        // --- KIRIM TELEGRAM VIA CURL ---
                        $payload = [
                            'chat_id' => $tk['telegram_id'], // Mengambil dari tabel users
                            'text' => $message,
                            'parse_mode' => 'Markdown',
                            'disable_web_page_preview' => true 
                        ];

                        $ch = curl_init("https://api.telegram.org/bot$token/sendMessage");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
                        curl_exec($ch);
                        curl_close($ch);
                    }
                }
            }

            echo ($success_count > 0) ? "success" : "error_no_tickets";
        } else {
            // Pesan error diperjelas
            echo "error_invalid_tech: User teknisi tidak ditemukan atau belum punya Telegram ID.";
        }
    } else {
        echo "error_data_missing";
    }
} catch (Exception $e) {
    echo "error_db: " . $e->getMessage();
}
?>