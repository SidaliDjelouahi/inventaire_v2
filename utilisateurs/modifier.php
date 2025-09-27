<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Vérifier si l’ID est fourni ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . ROOT_URL . "/utilisateurs/table.php");
    exit;
}

$id = intval($_GET['id']);

// --- Récupérer l’utilisateur depuis la base ---
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$utilisateur = $stmt->fetch();

if (!$utilisateur) {
    // Redirection si utilisateur introuvable
    header("Location: " . ROOT_URL . "/utilisateurs/table.php");
    exit;
}

// --- Mise à jour après soumission du formulaire ---
if (isset($_POST['edit_user'])) {
    $username = trim($_POST['username']);
    $rank = $_POST['rank'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET username=?, password=?, rank=? WHERE id=?");
        $stmt->execute([$username, $password, $rank, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET username=?, rank=? WHERE id=?");
        $stmt->execute([$username, $rank, $id]);
    }

    // --- Solution 1 : redirection avant tout HTML ---
    header("Location: " . ROOT_URL . "/utilisateurs/table.php");
    exit;
}

// --- Inclure le header et sidebar après traitement ---
require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">
    <h2>Modifier utilisateur</h2>
    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label>Nom d’utilisateur</label>
            <input type="text" name="username" class="form-control" 
                   value="<?= htmlspecialchars($utilisateur['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Nouveau mot de passe (laisser vide si inchangé)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label>Rang</label>
            <select name="rank" class="form-control">
                <option value="admin" <?= $utilisateur['rank']=='admin'?'selected':'' ?>>Admin</option>
                <option value="user" <?= $utilisateur['rank']=='user'?'selected':'' ?>>User</option>
                <option value="viewer" <?= $utilisateur['rank']=='viewer'?'selected':'' ?>>Viewer</option>
            </select>
        </div>
        <div class="d-flex justify-content-between">
            <a href="<?= ROOT_URL ?>/utilisateurs/table.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" name="edit_user" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>

<?php require_once("../includes/footer.php"); ?>
