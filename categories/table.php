<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Ajouter une catégorie ---
$error = "";
if (isset($_POST['add_categorie'])) {
    $nom = trim($_POST['nom']);
    $code = trim($_POST['code']);

    // Vérifier si le code existe déjà
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE code = ?");
    $stmt_check->execute([$code]);
    if ($stmt_check->fetchColumn() > 0) {
        $error = "Le code '$code' existe déjà !";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (nom, code) VALUES (?, ?)");
        $stmt->execute([$nom, $code]);

        // Redirection avant tout HTML
        header("Location: " . ROOT_URL . "/categories/table.php");
        exit;
    }
}

// --- Récupération des catégories ---
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">
    <h2>Gestion des catégories</h2>

    <!-- Affichage de l'erreur si code dupliqué -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
        + Ajouter
    </button>

    <!-- Champ de recherche instantané -->
    <div class="mb-3">
        <input type="text" id="searchCategorie" class="form-control" placeholder="Rechercher une catégorie...">
    </div>

    <!-- Tableau des catégories -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="categoriesTableBody">
            <?php if ($categories): ?>
                <?php foreach ($categories as $c): ?>
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
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">Aucune catégorie trouvée</td></tr>
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

<!-- Script AJAX recherche instantanée -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const input = document.getElementById('searchCategorie');
    input.addEventListener('keyup', function() {
        const query = this.value;
        // Requête AJAX vers ton search.php existant
        fetch("search.php?q=" + encodeURIComponent(query) + "&type=categories")
            .then(response => response.text())
            .then(data => {
                document.getElementById('categoriesTableBody').innerHTML = data;
            });
    });
});
</script>

<?php require_once("../includes/footer.php"); ?>
