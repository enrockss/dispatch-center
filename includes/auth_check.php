<?php
// includes/auth_check.php

// Session dianggap valid hanya kalau ketiga key ini ada.
// Kalau salah satu hilang (misal session lama/rusak), paksa logout & balik ke login.
if (!isset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['nama_lengkap'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>