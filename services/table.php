<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Ajouter un service ---
if (isset($_POST['add_service'])) {
    $nom = trim($_POST['nom']);

    if ($nom) {
        $stmt = $pdo->prepare("INSERT INTO services (nom) VALUES (?)");
        $stmt->execute([$nom]);

        // Redirection après ajout
        header("Location: " . ROOT_URL . "/services/table.php");
        exit();
    } else {
        $error_msg = "Le nom du service ne peut pas être vide.";
    }
}

// --- Récupération des services (initial) ---
$services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// --- Inclure header et sidebar après toute logique PHP ---
require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2>Gestion des services</h2>

    <?php if (!empty($error_msg)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
        + Ajouter
    </button>

    <!-- Champ de recherche -->
    <div class="mb-3">
        <input type="text" id="search" class="form-control" placeholder="Rechercher un service...">
    </div>

    <!-- Tableau des services -->
    <div class="table-responsive" id="servicesTable">
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
    </div>
</div>

<!-- Modal Ajouter Service -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
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

<!-- Script AJAX recherche -->
<script>
document.getElementById('search').addEventListener('keyup', function(){
    let query = this.value;
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'search.php?q=' + encodeURIComponent(query), true);
    xhr.onload = function(){
        if (this.status === 200) {
            document.getElementById('servicesTable').innerHTML = this.responseText;
        }
    };
    xhr.send();
});
</script>

<?php require_once("../includes/footer.php"); ?>
