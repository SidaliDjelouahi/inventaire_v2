<?php
// fournisseurs/table.php
session_start();
require_once "../includes/config.php";
require_once "../includes/db.php";
require_once "../includes/header.php";
require_once "../includes/sidebar.php";

// --- Ajouter un fournisseur ---
if (isset($_POST['add_fournisseur'])) {
    $nom = trim($_POST['nom']);
    $adresse = trim($_POST['adresse']);
    $telephone = trim($_POST['telephone']);

    if (!empty($nom)) {
        $stmt = $pdo->prepare("INSERT INTO fournisseurs (nom, adresse, telephone) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $adresse, $telephone]);
        header("Location: table.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Le nom du fournisseur est obligatoire.</div>";
    }
}

// --- Récupérer les fournisseurs ---
$stmt = $pdo->query("SELECT * FROM fournisseurs ORDER BY created_at DESC");
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">

    <h2 class="mb-4">Gestion des fournisseurs</h2>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle"></i> Ajouter
    </button>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Téléphone</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($fournisseurs): ?>
                <?php foreach ($fournisseurs as $f): ?>
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
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Aucun fournisseur trouvé</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un fournisseur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_fournisseur" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
