<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Vérifier l'ID ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . ROOT_URL . "/services/table.php");
    exit();
}

$id = intval($_GET['id']);

// --- Charger le service ---
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    // Si service introuvable, redirection
    header("Location: " . ROOT_URL . "/services/table.php");
    exit();
}

// --- Mise à jour du service ---
if (isset($_POST['update_service'])) {
    $nom = trim($_POST['nom']);

    if ($nom) {
        $stmt = $pdo->prepare("UPDATE services SET nom = ? WHERE id = ?");
        $stmt->execute([$nom, $id]);

        // Redirection après mise à jour
        header("Location: " . ROOT_URL . "/services/table.php");
        exit();
    } else {
        $error_msg = "Le nom du service ne peut pas être vide.";
    }
}

// --- Inclure header et sidebar après toute logique PHP ---
require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2>Modifier service</h2>

    <?php if (!empty($error_msg)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-3">
        <div class="mb-3">
            <label>Nom du service</label>
            <input type="text" name="nom" class="form-control" 
                   value="<?= htmlspecialchars($service['nom']) ?>" required>
        </div>
        <button type="submit" name="update_service" class="btn btn-primary">
            Enregistrer
        </button>
        <a href="table.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require_once("../includes/footer.php"); ?>
