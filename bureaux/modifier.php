<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/db.php";

// --- Vérifier l'ID ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . ROOT_URL . "/bureaux/table.php");
    exit();
}

$id = intval($_GET['id']);

// --- Récupérer le bureau ---
$stmt = $pdo->prepare("SELECT * FROM bureaux WHERE id = ?");
$stmt->execute([$id]);
$bureau = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bureau) {
    die("<div class='alert alert-danger'>Bureau introuvable</div>");
}

// --- Récupérer les services ---
$services = $pdo->query("SELECT id, nom FROM services ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- Traitement POST pour mise à jour ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_bureau = trim($_POST['bureau']);
    $id_service = intval($_POST['id_service']);

    if ($nom_bureau && $id_service) {
        $stmt = $pdo->prepare("UPDATE bureaux SET bureau = ?, id_service = ? WHERE id = ?");
        $stmt->execute([$nom_bureau, $id_service, $id]);

        // Redirection après mise à jour
        header("Location: " . ROOT_URL . "/bureaux/table.php");
        exit();
    } else {
        $error_msg = "Veuillez remplir tous les champs";
    }
}

// --- Inclure header et sidebar après toute logique PHP ---
require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2 class="mb-4">Modifier un bureau</h2>

    <?php if (!empty($error_msg)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4">
        <div class="mb-3">
            <label class="form-label">Nom du bureau</label>
            <input type="text" name="bureau" class="form-control" value="<?= htmlspecialchars($bureau['bureau']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Service associé</label>
            <select name="id_service" class="form-control" required>
                <option value="">-- Choisir un service --</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $bureau['id_service'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Enregistrer
        </button>
        <a href="table.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require_once "../includes/footer.php"; ?>
