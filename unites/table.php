<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once("../includes/header.php");
require_once("../includes/sidebar.php");

// --- Ajouter une unité ---
if (isset($_POST['add_unite'])) {
    $nom = $_POST['nom'];

    $stmt = $pdo->prepare("INSERT INTO unites (nom) VALUES (?)");
    $stmt->execute([$nom]);

    header("Location: " . ROOT_URL . "/unites/table.php");
exit;
}

// --- Récupération des unités ---
$stmt = $pdo->query("SELECT * FROM unites ORDER BY id DESC");
$unites = $stmt->fetchAll();
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">

    <h2>Gestion des unités</h2>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Ajouter</button>

    <!-- Tableau des unités -->
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($unites as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td>
                    <!-- Lien vers modifier -->
                    <a href="modifier.php?id=<?= $u['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <!-- Lien vers supprimer avec confirmation -->
                    <a href="supprimer.php?id=<?= $u['id'] ?>" 
                       onclick="return confirm('Voulez-vous vraiment supprimer l’unité <?= htmlspecialchars($u['nom']) ?> ?');" 
                       class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter unité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_unite" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php require_once("../includes/footer.php"); ?>
