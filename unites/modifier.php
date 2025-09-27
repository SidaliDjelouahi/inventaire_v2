<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Vérifier si l'ID est fourni ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . ROOT_URL . "/unites/table.php");
    exit;
}

$id = intval($_GET['id']);

// --- Récupération de l'unité ---
$stmt = $pdo->prepare("SELECT * FROM unites WHERE id = ?");
$stmt->execute([$id]);
$unite = $stmt->fetch();

if (!$unite) {
    // Unité introuvable, redirection vers la liste
    header("Location: " . ROOT_URL . "/unites/table.php");
    exit;
}

// --- Mise à jour ---
if (isset($_POST['update_unite'])) {
    $nom = trim($_POST['nom']);

    if ($nom) {
        $stmt = $pdo->prepare("UPDATE unites SET nom = ? WHERE id = ?");
        $stmt->execute([$nom, $id]);

        // Redirection après modification
        header("Location: " . ROOT_URL . "/unites/table.php");
        exit;
    } else {
        $error_msg = "Le nom de l'unité ne peut pas être vide.";
    }
}

// --- Inclure header et sidebar après tout traitement PHP ---
require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2>Modifier unité</h2>

    <?php if (!empty($error_msg)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4">
        <div class="mb-3">
            <label>Nom de l'unité</label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($unite['nom']) ?>" required>
        </div>
        <button type="submit" name="update_unite" class="btn btn-primary">Enregistrer</button>
        <a href="table.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require_once("../includes/footer.php"); ?>
