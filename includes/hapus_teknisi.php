<?php
$db = new PDO("sqlite:ticketing_isp.db");

if (isset($_POST['id_teknisi'])) {
    $id = $_POST['id_teknisi'];
    $stmt = $db->prepare("DELETE FROM teknisi WHERE id_teknisi = ?");
    if ($stmt->execute([$id])) {
        echo "success";
    }
}
?>