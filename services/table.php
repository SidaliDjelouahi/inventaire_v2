<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once("../includes/header.php");
require_once("../includes/sidebar.php");

// --- Ajouter un service ---
if (isset($_POST['add_service'])) {
    $nom = $_POST['nom'];

    $stmt = $pdo->prepare("INSERT INTO services (nom) VALUES (?)");
    $stmt->execute([$nom]);
    header("Location: table.php");
    exit;
}

// --- Récupération des services ---
$stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
$services = $stmt->fetchAll();
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">

    <h2>Gestion des services</h2>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Ajouter</button>

    <!-- Tableau des services -->
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($services as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['nom']) ?></td>
                <td>
                    <!-- Lien vers modifier -->
                    <a href="modifier.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <!-- Lien vers supprimer avec confirmation -->
                    <a href="supprimer.php?id=<?= $s['id'] ?>" 
                       onclick="return confirm('Voulez-vous vraiment supprimer <?= htmlspecialchars($s['nom']) ?> ?');" 
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
                <h5 class="modal-title">Ajouter service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Nom du service</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_service" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php require_once("../includes/footer.php"); ?>
