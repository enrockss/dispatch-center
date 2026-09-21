<?php
// 1. Koneksi ke Database
require_once '../config/database.php';

// 2. Logika Simpan Data
$pesan_status = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $tele_id = $_POST['tele_id'];

    $stmt = $db->prepare("INSERT INTO teknisi (nama_teknisi, telegram_id) VALUES (?, ?)");
    if ($stmt->execute([$nama, $tele_id])) {
        $pesan_status = "✅ Teknisi $nama berhasil ditambahkan!";
    } else {
        $pesan_status = "❌ Gagal menambahkan teknisi.";
    }
}

// 3. Ambil daftar teknisi yang sudah ada
$list_teknisi = $db->query("SELECT * FROM teknisi")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Teknisi</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 40px; }
        .box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #34495e; color: white; }
    </style>
</head>
<body>

    <div class="box">
        <h2>Tambah Teknisi / Group Chat</h2>
        <?php if($pesan_status): ?>
            <p style="color: green;"><?= $pesan_status ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="nama" placeholder="Nama Teknisi (Misal: Tim Lapangan)" required>
            <input type="text" name="tele_id" placeholder="Chat ID (Contoh: -5215457856)" required>
            <button type="submit">Simpan Teknisi</button>
        </form>
    </div>

    <h3>Daftar Teknisi Terdaftar</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Chat ID Telegram</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list_teknisi as $t): ?>
            <tr>
                <td><?= $t['id_teknisi'] ?></td>
                <td><?= $t['nama_teknisi'] ?></td>
                <td><code><?= $t['telegram_id'] ?></code></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="index.php">← Kembali ke Dashboard</a></p>

</body>
</html>