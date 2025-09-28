<?php
require_once "../includes/config.php";
require_once "../includes/db.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$params = [];
$where = '';

if ($q !== '') {
    $where = "WHERE b.bureau LIKE :search OR s.nom LIKE :search";
    $params[':search'] = "%$q%";
}

$sql = "SELECT b.id, b.bureau, s.nom AS service_nom
        FROM bureaux b
        JOIN services s ON b.id_service = s.id
        $where
        ORDER BY b.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bureaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($bureaux) {
    foreach ($bureaux as $b) {
        echo '<tr>';
        echo '<td>'.htmlspecialchars($b['id']).'</td>';
        echo '<td>'.htmlspecialchars($b['bureau']).'</td>';
        echo '<td>'.htmlspecialchars($b['service_nom']).'</td>';
        echo '<td>
                <a href="modifier.php?id='.$b['id'].'" class="btn btn-sm btn-warning">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="supprimer.php?id='.$b['id'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Supprimer ce bureau ?\');">
                  <i class="bi bi-trash"></i>
                </a>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-center">Aucun bureau trouvé</td></tr>';
}
