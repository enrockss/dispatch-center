<?php
// 1. Panggil Koneksi Database
require_once 'config/database.php';

// 2. Cek apakah sudah login
if (isset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['nama_lengkap'])) { 
    header("Location: index.php"); 
    exit(); 
}

 $login_success = false; 
 $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil input dan hilangkan spasi kosong di awal/akhir
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Query User
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Cek User
    if (!$user) {
        // User tidak ditemukan di database
        $error = "Username <b>'$username'</b> tidak ditemukan. Cek kembali penulisan.";
    } else {
        // User ditemukan, cek password
        if (password_verify($password, $user['password'])) {
            // Password Benar
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            $login_success = true;
        } else {
            // Password Salah
            $error = "Password salah untuk username <b>'$username'</b>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Center</title>
    
    <!-- Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS (Grid/Base only, styles overridden) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="asset/login.css">
</head>
<body>

    <!-- Background Shapes -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <div class="login-wrapper">
        <div class="login-card">
            
            <?php if ($login_success): ?>
                <!-- SUCCESS VIEW -->
                <div class="success-content">
                    <div class="checkmark-wrapper">
                        <div class="background"></div>
                        <div class="checkmark draw"></div>
                    </div>
                    
                    <h2 class="success-text">Login Berhasil!</h2>
                    <p class="success-subtext">
                        Selamat datang kembali,<br>
                        <b><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></b><br>
                        Mengalihkan ke Dashboard...
                        <span class="spinner-grow spinner-grow-sm text-success" role="status" aria-hidden="true"></span>
                    </p>
                </div>

                <script>
                    setTimeout(function() {
                        window.location.href = "index.php";
                    }, 800); // Sedikit lebih lama agar user bisa melihat animasi sukses
                </script>

            <?php else: ?>
                <!-- LOGIN FORM VIEW -->
                <div class="brand-container">
                    <div class="brand-icon">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                </div>

                <h3 class="login-title">Dispatch Center</h3>
                <p class="login-subtitle">Login to dispatch center</p>

                <?php if(!empty($error)): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                        <span><?= $error ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="custom-input-group">
                            <span class="input-icon"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="user" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="custom-input-group">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        Login <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>

                <div class="footer-text">
                   &copy; <?= date('Y') ?> Enrocks Network System
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
