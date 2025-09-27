<?php
// fournisseurs/modifier.php
require_once "../includes/db.php";
require_once "../includes/header.php";
require_once "../includes/sidebar.php";

// Vérifier si un ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: table.php");
    exit();
}

$id = intval($_GET['id']);

// Récupérer les données du fournisseur
$sql = "SELECT * FROM fournisseurs WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fournisseur) {
    echo "<div class='alert alert-danger'>Fournisseur introuvable.</div>";
    require_once "../includes/footer.php";
    exit();
}

$message = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $adresse = trim($_POST['adresse']);
    $telephone = trim($_POST['telephone']);

    if (!empty($nom)) {
        $update = "UPDATE fournisseurs SET nom = ?, adresse = ?, telephone = ? WHERE id = ?";
        $stmt = $pdo->prepare($update);
        $stmt->execute([$nom, $adresse, $telephone, $id]);

        $message = "<div class='alert alert-success'>Fournisseur mis à jour avec succès.</div>";

        // Recharger les données mises à jour
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = "<div class='alert alert-danger'>Le nom est obligatoire.</div>";
    }
}
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2 class="mb-4">Modifier Fournisseur</h2>

    <?= $message ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" 
                   value="<?= htmlspecialchars($fournisseur['nom']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Adresse</label>
            <textarea name="adresse" class="form-control"><?= htmlspecialchars($fournisseur['adresse']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Téléphone</label>
            <input type="text" name="telephone" class="form-control" 
                   value="<?= htmlspecialchars($fournisseur['telephone']) ?>">
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Sauvegarder
        </button>
        <a href="table.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </form>
</div>

<?php require_once "../includes/footer.php"; ?>
