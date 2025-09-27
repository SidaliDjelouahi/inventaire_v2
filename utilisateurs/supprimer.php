<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once("../includes/header.php");
require_once("../includes/sidebar.php");

// Vérifier si l’ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: table.php");
    exit;
}

$id = intval($_GET['id']);

// Récupérer l’utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$utilisateur = $stmt->fetch();

if (!$utilisateur) {
    echo "<div class='alert alert-danger'>Utilisateur introuvable.</div>";
    require_once("../includes/footer.php");
    exit;
}

// --- Suppression après confirmation ---
if (isset($_POST['delete_user'])) {
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id=?");
    $stmt->execute([$id]);

    header("Location: table.php");
    exit;
}
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">
    <h2>Supprimer utilisateur</h2>

    <div class="alert alert-warning">
        Voulez-vous vraiment supprimer l’utilisateur 
        <b><?= htmlspecialchars($utilisateur['username']) ?></b> ?
    </div>

    <form method="post" class="d-flex gap-2">
        <a href="table.php" class="btn btn-secondary">Annuler</a>
        <button type="submit" name="delete_user" class="btn btn-danger">
            Supprimer
        </button>
    </form>
</div>

<?php require_once("../includes/footer.php"); ?>
