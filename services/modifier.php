<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once("../includes/header.php");
require_once("../includes/sidebar.php");

if (!isset($_GET['id'])) {
    header("Location: table.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    header("Location: table.php");
    exit;
}

// --- Mise à jour ---
if (isset($_POST['update_service'])) {
    $nom = $_POST['nom'];

    $stmt = $pdo->prepare("UPDATE services SET nom = ? WHERE id = ?");
    $stmt->execute([$nom, $id]);

    header("Location: table.php");
    exit;
}
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">
    <h2>Modifier service</h2>

    <form method="post" class="card p-3">
        <div class="mb-3">
            <label>Nom du service</label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($service['nom']) ?>" required>
        </div>
        <button type="submit" name="update_service" class="btn btn-primary">Enregistrer</button>
        <a href="table.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require_once("../includes/footer.php"); ?>
