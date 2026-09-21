<?php
declare(strict_types=1);

// ============================================================
// setup.php — First-install wizard untuk Ticketing ISP (Enrocks Dispatcher)
//
// Yang dilakukan:
// 1. Bikin folder dbs/ (kalau belum ada) + file ticketing_isp.db (kalau belum ada)
// 2. Bikin struktur tabel: users, teknisi, tiket (kalau belum ada / CREATE TABLE IF NOT EXISTS)
// 3. Kalau users masih kosong -> tampilkan form buat superadmin pertama
// 4. Sukses -> redirect ke login.php
//
// Taruh file ini di root project (sejajar dengan index.php & login.php).
// ============================================================

// Path relatif terhadap lokasi file ini: \ticket\setup.php -> \ticket\dbs\ticketing_isp.db
const DB_DIR    = __DIR__ . '/dbs';
const DB_PATH   = DB_DIR . '/ticketing_isp.db';
const LOGIN_URL = 'login.php';

session_start();

/**
 * Bikin folder dbs/ kalau belum ada, lalu buka koneksi PDO ke SQLite.
 * PDO sqlite otomatis bikin file .db kosong kalau belum ada.
 */
function getDb(): PDO
{
    if (!is_dir(DB_DIR)) {
        if (!mkdir(DB_DIR, 0755, true) && !is_dir(DB_DIR)) {
            throw new RuntimeException('Gagal membuat folder dbs/. Cek permission folder project.');
        }
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}

/**
 * Bikin struktur tabel kalau belum ada. Aman dijalankan berkali-kali
 * (CREATE TABLE IF NOT EXISTS) — jadi tidak menghapus data yang sudah ada.
 */
function ensureSchema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id_user INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            nama_lengkap TEXT,
            role TEXT DEFAULT 'admin',
            telegram_id TEXT
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS teknisi (
            id_teknisi INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_teknisi TEXT NOT NULL,
            telegram_id TEXT,
            status_aktif INTEGER DEFAULT 1
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tiket (
            id_tiket INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_pelanggan TEXT NOT NULL,
            alamat TEXT,
            kendala TEXT,
            nama_admin TEXT,
            id_teknisi INTEGER,
            status TEXT DEFAULT 'OPEN', -- OPEN, ASSIGNED, IN_PROGRESS, CLOSED
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME,
            no_kontak TEXT,
            url_lokasi TEXT,
            FOREIGN KEY (id_teknisi) REFERENCES teknisi(id_teknisi)
        )
    ");
}

function usersExist(PDO $pdo): bool
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    return $count > 0;
}

$errors = [];

try {
    $pdo = getDb();
    ensureSchema($pdo);
} catch (Throwable $e) {
    http_response_code(500);
    die('Setup gagal: ' . htmlspecialchars($e->getMessage()));
}

// Guard: kalau superadmin/user sudah pernah dibuat, setup dianggap selesai
if (usersExist($pdo)) {
    header('Location: ' . LOGIN_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['password_confirm'] ?? '';
    $namaLengkap = trim($_POST['nama_lengkap'] ?? '');

    if ($username === '' || strlen($username) < 3) {
        $errors[] = 'Username minimal 3 karakter.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Re-check di dalam transaksi, jaga-jaga race condition first-run
            if (usersExist($pdo)) {
                $pdo->rollBack();
                header('Location: ' . LOGIN_URL);
                exit;
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                'INSERT INTO users (username, password, nama_lengkap, role, telegram_id)
                 VALUES (:username, :password, :nama_lengkap, :role, NULL)'
            );
            $stmt->execute([
                ':username'     => $username,
                ':password'     => $hash,
                ':nama_lengkap' => $namaLengkap !== '' ? $namaLengkap : $username,
                ':role'         => 'superadmin',
            ]);

            $pdo->commit();

            header('Location: ' . LOGIN_URL . '?setup=success');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Gagal membuat akun: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>First Install - Ticketing ISP</title>
<style>
  body { font-family: system-ui, sans-serif; background:#0f172a; color:#e2e8f0; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
  .card { background:#1e293b; padding:2rem 2.5rem; border-radius:12px; width:100%; max-width:400px; box-shadow:0 10px 30px rgba(0,0,0,.4); }
  h1 { font-size:1.25rem; margin-bottom:.25rem; }
  p.sub { color:#94a3b8; font-size:.85rem; margin-top:0; margin-bottom:1.5rem; }
  .badge-ok { display:inline-block; background:#064e3b; color:#6ee7b7; font-size:.7rem; padding:.15rem .5rem; border-radius:99px; margin-bottom:1rem; }
  label { display:block; font-size:.85rem; margin-bottom:.25rem; color:#cbd5e1; }
  input { width:100%; padding:.6rem .7rem; margin-bottom:1rem; border-radius:6px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; box-sizing:border-box; }
  button { width:100%; padding:.7rem; border:none; border-radius:6px; background:#2563eb; color:#fff; font-weight:600; cursor:pointer; }
  button:hover { background:#1d4ed8; }
  .errors { background:#7f1d1d; color:#fecaca; padding:.75rem 1rem; border-radius:6px; margin-bottom:1rem; font-size:.85rem; }
  .errors ul { margin:0; padding-left:1.1rem; }
</style>
</head>
<body>
  <div class="card">
    <span class="badge-ok">✓ Struktur database siap (dbs/ticketing_isp.db)</span>
    <h1>Setup Awal — Superadmin</h1>
    <p class="sub">Ini instalasi baru. Buat akun superadmin pertama untuk mulai memakai sistem.</p>

    <?php if (!empty($errors)): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

      <label for="nama_lengkap">Nama Lengkap (opsional)</label>
      <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="8">

      <label for="password_confirm">Konfirmasi Password</label>
      <input type="password" id="password_confirm" name="password_confirm" required minlength="8">

      <button type="submit">Buat Superadmin & Selesaikan Setup</button>
    </form>
  </div>
</body>
</html>