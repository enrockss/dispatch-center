<?php
require_once '../config/database.php';
$res = $db->query("SELECT * FROM teknisi ORDER BY nama_teknisi ASC");
$teknisi = $res->fetchAll(PDO::FETCH_ASSOC);

foreach ($teknisi as $t) {
    echo "<tr>
            <td class='fw-bold text-dark'>{$t['nama_teknisi']}</td>
            <td><code>{$t['telegram_id']}</code></td>
            <td class='text-end'>
                <button class='btn btn-sm btn-outline-info me-1 btn-edit-tech' 
                        data-id='{$t['id_teknisi']}' 
                        data-nama='{$t['nama_teknisi']}' 
                        data-tele='{$t['telegram_id']}'>
                    <i class='bi bi-pencil'></i>
                </button>
                <button class='btn btn-sm btn-outline-danger btn-hapus-tech' data-id='{$t['id_teknisi']}'>
                    <i class='bi bi-trash'></i>
                </button>
            </td>
          </tr>";
}
?>