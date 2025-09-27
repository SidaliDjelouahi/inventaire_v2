<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once("../includes/header.php");
require_once("../includes/sidebar.php");

// --- Ajouter un utilisateur ---
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rank = $_POST['rank'];

    $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password, rank) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $rank]);
    header("Location: table.php");
    exit;
}

// --- Récupération des utilisateurs ---
$stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY created_at DESC");
$utilisateurs = $stmt->fetchAll();
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">

    <h2>Gestion des utilisateurs</h2>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Ajouter</button>

    <!-- Tableau des utilisateurs -->
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
        <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= $u['rank'] ?></td>
                <td><?= $u['created_at'] ?></td>
                <td>
                    <!-- Lien vers modifier -->
                    <a href="modifier.php?id=<?= $u['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <!-- Lien vers supprimer avec confirmation -->
                    <a href="supprimer.php?id=<?= $u['id'] ?>" 
                       onclick="return confirm('Voulez-vous vraiment supprimer <?= htmlspecialchars($u['username']) ?> ?');" 
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
                <h5 class="modal-title">Ajouter utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Nom d’utilisateur</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Rang</label>
                    <select name="rank" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="user" selected>User</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_user" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php require_once("../includes/footer.php"); ?>
