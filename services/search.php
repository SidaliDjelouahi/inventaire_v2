<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE nom LIKE ? ORDER BY id DESC");
    $stmt->execute(['%' . $q . '%']);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
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
        <?php if ($services): ?>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['id']) ?></td>
                    <td><?= htmlspecialchars($s['nom']) ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="supprimer.php?id=<?= $s['id'] ?>" 
                           onclick="return confirm('Voulez-vous vraiment supprimer <?= htmlspecialchars($s['nom']) ?> ?');" 
                           class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3" class="text-center">Aucun service trouvé</td></tr>
        <?php endif; ?>
    </tbody>
</table>
