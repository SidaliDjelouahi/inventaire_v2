<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM unites WHERE nom LIKE ? ORDER BY id DESC");
    $stmt->execute(['%' . $q . '%']);
    $unites = $stmt->fetchAll();
} else {
    $unites = $pdo->query("SELECT * FROM unites ORDER BY id DESC")->fetchAll();
}
?>

<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($unites): ?>
        <?php foreach ($unites as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td>
                    <a href="modifier.php?id=<?= $u['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="supprimer.php?id=<?= $u['id'] ?>" 
                       onclick="return confirm('Voulez-vous vraiment supprimer l’unité <?= htmlspecialchars($u['nom']) ?> ?');" 
                       class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="3" class="text-center">Aucune unité trouvée</td></tr>
    <?php endif; ?>
    </tbody>
</table>
