<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    exit("Non autorisé");
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

$sql = "SELECT 
            d.id,
            d.num_decharge,
            d.date,
            b.bureau AS bureau_nom
        FROM decharges d
        LEFT JOIN bureaux b ON d.id_bureau = b.id";

$params = [];
if ($search !== "") {
    $sql .= " WHERE 
        d.id LIKE :search
        OR d.num_decharge LIKE :search
        OR b.bureau LIKE :search
        OR DATE_FORMAT(d.date,'%Y-%m-%d %H:%i') LIKE :search";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY d.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0): 
    foreach ($rows as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['num_decharge']) ?></td>
            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['date']))) ?></td>
            <td><?= htmlspecialchars($row['bureau_nom'] ?? '') ?></td>
            <td>
                <a href="details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye"></i> Détails
                </a>
            </td>
        </tr>
    <?php endforeach;
else: ?>
    <tr><td colspan="5" class="text-center">Aucune décharge trouvée</td></tr>
<?php endif; ?>
