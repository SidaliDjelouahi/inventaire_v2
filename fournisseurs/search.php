<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/db.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT * FROM fournisseurs";
$params = [];
if ($q !== '') {
    $sql .= " WHERE nom LIKE :q OR adresse LIKE :q OR telephone LIKE :q";
    $params[':q'] = "%$q%";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($fournisseurs) {
    foreach ($fournisseurs as $f) {
        ?>
        <tr>
            <td><?= $f['id'] ?></td>
            <td><?= htmlspecialchars($f['nom']) ?></td>
            <td><?= htmlspecialchars($f['adresse']) ?></td>
            <td><?= htmlspecialchars($f['telephone']) ?></td>
            <td><?= $f['created_at'] ?></td>
            <td>
                <a href="modifier.php?id=<?= $f['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil"></i>
                </a>
                <a href="supprimer.php?id=<?= $f['id'] ?>" 
                   onclick="return confirm('Voulez-vous vraiment supprimer ce fournisseur ?');" 
                   class="btn btn-danger btn-sm">
                    <i class="bi bi-trash"></i>
                </a>
            </td>
        </tr>
        <?php
    }
} else {
    echo '<tr><td colspan="6" class="text-center">Aucun fournisseur trouvé</td></tr>';
}
