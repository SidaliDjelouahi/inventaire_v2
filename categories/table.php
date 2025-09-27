<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once("../includes/header.php");
require_once("../includes/sidebar.php");

// --- Ajouter une catégorie ---
if (isset($_POST['add_categorie'])) {
    $nom = $_POST['nom'];
    $code = $_POST['code'];

    $stmt = $pdo->prepare("INSERT INTO categories (nom, code) VALUES (?, ?)");
    $stmt->execute([$nom, $code]);

    header("Location: table.php");
    exit;
}

// --- Récupération des catégories ---
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">

    <h2>Gestion des catégories</h2>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Ajouter</button>

    <!-- Tableau des catégories -->
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['nom']) ?></td>
                <td><?= htmlspecialchars($c['code']) ?></td>
                <td>
                    <!-- Lien vers modifier -->
                    <a href="modifier.php?id=<?= $c['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <!-- Lien vers supprimer avec confirmation -->
                    <a href="supprimer.php?id=<?= $c['id'] ?>" 
                       onclick="return confirm('Voulez-vous vraiment supprimer la catégorie <?= htmlspecialchars($c['nom']) ?> ?');" 
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
                <h5 class="modal-title">Ajouter catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_categorie" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php require_once("../includes/footer.php"); ?>
