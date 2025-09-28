<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// On récupère le terme de recherche
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT * FROM categories";
$params = [];

if ($q !== '') {
    $sql .= " WHERE nom LIKE :q OR code LIKE :q";
    $params[':q'] = "%$q%";
}
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Générer les lignes <tr>
if ($categories) {
    foreach ($categories as $c) {
        ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['nom']) ?></td>
            <td><?= htmlspecialchars($c['code']) ?></td>
            <td>
                <a href="modifier.php?id=<?= $c['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil"></i>
                </a>
                <a href="supprimer.php?id=<?= $c['id'] ?>" 
                   onclick="return confirm('Voulez-vous vraiment supprimer la catégorie <?= htmlspecialchars($c['nom']) ?> ?');" 
                   class="btn btn-danger btn-sm">
                    <i class="bi bi-trash"></i>
                </a>
            </td>
        </tr>
        <?php
    }
} else {
    echo '<tr><td colspan="4" class="text-center">Aucune catégorie trouvée</td></tr>';
}
