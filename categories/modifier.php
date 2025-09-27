<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once("../includes/header.php");
require_once("../includes/sidebar.php");

// --- Vérifier si l'ID est fourni ---
if (!isset($_GET['id'])) {
    header("Location: table.php");
    exit;
}

$id = intval($_GET['id']);

// --- Récupération de la catégorie ---
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$cat = $stmt->fetch();

if (!$cat) {
    echo "<div class='alert alert-danger'>Catégorie introuvable.</div>";
    require_once("../includes/footer.php");
    exit;
}

// --- Mise à jour ---
if (isset($_POST['edit_cat'])) {
    $nom = $_POST['nom'];
    $code = $_POST['code'];

    $stmt = $pdo->prepare("UPDATE categories SET nom = ?, code = ? WHERE id = ?");
    $stmt->execute([$nom, $code, $id]);

    header("Location: table.php");
    exit;
}
?>

<!-- Colonne principale -->
<div class="col-md-9 col-lg-10 p-4">
    <h2>Modifier Catégorie</h2>
    <form method="post">
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" 
                   value="<?= htmlspecialchars($cat['nom']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Code</label>
            <input type="text" name="code" class="form-control" 
                   value="<?= htmlspecialchars($cat['code']) ?>">
        </div>
        <button type="submit" name="edit_cat" class="btn btn-primary">Enregistrer</button>
        <a href="table.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require_once("../includes/footer.php"); ?>
