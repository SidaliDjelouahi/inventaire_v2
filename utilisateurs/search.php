<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs 
                           WHERE username LIKE ? OR rank LIKE ?
                           ORDER BY created_at DESC");
    $stmt->execute(['%' . $q . '%', '%' . $q . '%']);
} else {
    $stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY created_at DESC");
}

$utilisateurs = $stmt->fetchAll();
?>
<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nom d’utilisateur</th>
            <th>Rang</th>
            <th>Date création</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($utilisateurs): ?>
        <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['rank']) ?></td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
                <td>
                    <a href="modifier.php?id=<?= $u['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="supprimer.php?id=<?= $u['id'] ?>" 
                       onclick="return confirm('Voulez-vous vraiment supprimer <?= htmlspecialchars($u['username']) ?> ?');" 
                       class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="5" class="text-center">Aucun utilisateur trouvé</td></tr>
    <?php endif; ?>
    </tbody>
</table>
