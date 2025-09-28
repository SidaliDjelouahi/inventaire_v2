<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Vérifier si l'ID est fourni ---
if (!isset($_GET['id'])) {
    header("Location: " . ROOT_URL . "/produits/table.php");
    exit;
}

$id = intval($_GET['id']);

// --- Récupération du produit ---
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);

// Si produit introuvable, rediriger
if (!$prod) {
    header("Location: " . ROOT_URL . "/produits/table.php");
    exit;
}

// --- Récupération des catégories et unités ---
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$unites = $pdo->query("SELECT * FROM unites ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- Mise à jour ---
if (isset($_POST['edit_produit'])) {
    $code = trim($_POST['code']);
    $nom = trim($_POST['nom']);
    $id_categorie = $_POST['id_categorie'];
    $type = $_POST['type'];
    $id_unite = $_POST['id_unite'] ?: null;
    $stock_initial = $_POST['stock_initial'] ?: 0;

    $stmt = $pdo->prepare("UPDATE produits 
        SET code = ?, nom = ?, id_categorie = ?, type = ?, id_unite = ?, stock_initial = ?
        WHERE id = ?");
    $stmt->execute([$code, $nom, $id_categorie, $type, $id_unite, $stock_initial, $id]);

    // Redirection après mise à jour
    header("Location: " . ROOT_URL . "/produits/table.php");
    exit;
}

// --- Inclure header et sidebar après toute logique PHP ---
require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2>Modifier Produit</h2>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-6">
                <label>Code</label>
                <input type="text" name="code" class="form-control" 
                       value="<?= htmlspecialchars($prod['code']) ?>">
            </div>
            <div class="col-md-6">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" 
                       value="<?= htmlspecialchars($prod['nom']) ?>" required>
            </div>
            <div class="col-md-6">
                <label>Catégorie</label>
                <select name="id_categorie" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" 
                            <?= $prod['id_categorie'] == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="consommable" <?= $prod['type']=='consommable'?'selected':'' ?>>Consommable</option>
                    <option value="inventoree" <?= $prod['type']=='inventoree'?'selected':'' ?>>Inventoriée</option>
                </select>
            </div>
            <div class="col-md-6">
                <label>Unité</label>
                <select name="id_unite" class="form-control">
                    <option value="">-- Facultatif --</option>
                    <?php foreach ($unites as $u): ?>
                        <option value="<?= $u['id'] ?>" 
                            <?= $prod['id_unite'] == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Stock initial</label>
                <input type="number" step="0.01" name="stock_initial" class="form-control" 
                       value="<?= htmlspecialchars($prod['stock_initial']) ?>">
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" name="edit_produit" class="btn btn-primary">Enregistrer</button>
            <a href="<?= ROOT_URL ?>/produits/table.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once("../includes/footer.php"); ?>
