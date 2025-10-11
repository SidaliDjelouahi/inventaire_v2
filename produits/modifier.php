<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// === Vérification de l'ID ===
if (!isset($_GET['id'])) {
    header("Location: " . ROOT_URL . "/produits/table.php");
    exit;
}

$id = intval($_GET['id']);

// === Récupération du produit ===
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prod) {
    header("Location: " . ROOT_URL . "/produits/table.php");
    exit;
}

// === Récupération des catégories et unités ===
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$unites = $pdo->query("SELECT * FROM unites ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

$error_msg = "";

// === Traitement du formulaire ===
if (isset($_POST['edit_produit'])) {
    $code = trim($_POST['code']);
    $nom = trim($_POST['nom']);
    $id_categorie = $_POST['id_categorie'];
    $type = $_POST['type'];
    $id_unite = $_POST['id_unite'] ?: null;
    $stock_initial = floatval($_POST['stock_initial']);
    $prix_achat = isset($_POST['prix_achat']) && $_POST['prix_achat'] !== '' ? floatval($_POST['prix_achat']) : null;

    // Validation
    if (empty($nom)) {
        $error_msg = "Le nom du produit ne peut pas être vide.";
    }

    // Vérifier doublons (hors produit actuel)
    if (empty($error_msg)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE code = ? AND id != ?");
        $check->execute([$code, $id]);
        if ($check->fetchColumn() > 0) {
            $error_msg = "Ce code existe déjà. Merci d'en choisir un autre.";
        }

        $checkNom = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE nom = ? AND id != ?");
        $checkNom->execute([$nom, $id]);
        if ($checkNom->fetchColumn() > 0) {
            $error_msg = "Ce nom existe déjà. Merci d'en choisir un autre.";
        }
    }

    // Si stock > 0 => prix_achat obligatoire
    if (empty($error_msg) && $stock_initial > 0 && ($prix_achat === null || $prix_achat <= 0)) {
        $error_msg = "Veuillez saisir un prix d'achat pour les produits avec un stock initial.";
    }

    // === Vérifier si le stock_initial a été modifié et si un bon STOCK_INITIAL{id_produit} existe ===
    if (empty($error_msg)) {
        $ancien_stock = floatval($prod['stock_initial']);
        if ($stock_initial != $ancien_stock) {
            $num_achat_check = 'STOCK_INITIAL' . $id;
            $checkAchat = $pdo->prepare("SELECT COUNT(*) FROM achats WHERE num_achat = ?");
            $checkAchat->execute([$num_achat_check]);
            $achatExiste = $checkAchat->fetchColumn();

            if ($achatExiste > 0) {
                $error_msg = "⚠️ Impossible de modifier le stock initial :
                le bon d'achat " . $num_achat_check . " existe déjà dans l'historique.
                Veuillez le supprimer avant de modifier le stock initial.";
            }
        }
    }

    // === Mise à jour si pas d'erreur ===
    if (empty($error_msg)) {
        $pdo->beginTransaction();
        try {
            // Mise à jour du produit
            $stmt = $pdo->prepare("UPDATE produits 
                SET code = ?, nom = ?, id_categorie = ?, type = ?, id_unite = ?, stock_initial = ?, prix_achat = ?
                WHERE id = ?");
            $stmt->execute([$code, $nom, $id_categorie, $type, $id_unite, $stock_initial, $prix_achat, $id]);

            // Si stock_initial > 0, on s'assure qu'il existe un bon d'achat STOCK_INITIAL{id_produit}
            if ($stock_initial > 0) {
                // Vérifier ou créer fournisseur
                $stmtF = $pdo->prepare("SELECT id FROM fournisseurs WHERE nom = 'stock_initial'");
                $stmtF->execute();
                $id_fournisseur = $stmtF->fetchColumn();
                if (!$id_fournisseur) {
                    $pdo->prepare("INSERT INTO fournisseurs (nom, adresse, telephone) VALUES ('stock_initial', '', '')")->execute();
                    $id_fournisseur = $pdo->lastInsertId();
                }

                // ✅ Création / vérification du bon d'achat unique par produit
                $num_achat = 'STOCK_INITIAL' . $id;
                $stmtA = $pdo->prepare("SELECT id FROM achats WHERE num_achat = ?");
                $stmtA->execute([$num_achat]);
                $id_achat = $stmtA->fetchColumn();

                if (!$id_achat) {
                    $pdo->prepare("INSERT INTO achats (num_achat, date, id_fournisseur) VALUES (?, NOW(), ?)")
                         ->execute([$num_achat, $id_fournisseur]);
                    $id_achat = $pdo->lastInsertId();
                }

                // Vérifier si déjà présent dans achats_details
                $stmtD = $pdo->prepare("SELECT id FROM achats_details WHERE id_achat = ? AND id_produit = ?");
                $stmtD->execute([$id_achat, $id]);
                $id_detail = $stmtD->fetchColumn();

                if ($id_detail) {
                    $pdo->prepare("UPDATE achats_details SET prix_achat = ?, quantite = ? WHERE id = ?")
                        ->execute([$prix_achat, $stock_initial, $id_detail]);
                } else {
                    $pdo->prepare("INSERT INTO achats_details (id_achat, id_produit, prix_achat, quantite) VALUES (?,?,?,?)")
                        ->execute([$id_achat, $id, $prix_achat, $stock_initial]);
                }

                // Mettre le stock_initial du produit à 0 après création du bon
                $pdo->prepare("UPDATE produits SET stock_initial = 0 WHERE id = ?")->execute([$id]);
            }

            $pdo->commit();
            header("Location: " . ROOT_URL . "/produits/table.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2>Modifier le produit</h2>

    <?php if (!empty($error_msg)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="post" class="mt-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label>Code</label>
                <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($prod['code']) ?>">
            </div>

            <div class="col-md-6">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($prod['nom']) ?>" required>
            </div>

            <div class="col-md-6">
                <label>Catégorie</label>
                <select name="id_categorie" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $prod['id_categorie'] == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="consommable" <?= $prod['type'] == 'consommable' ? 'selected' : '' ?>>Consommable</option>
                    <option value="inventoree" <?= $prod['type'] == 'inventoree' ? 'selected' : '' ?>>Inventoriée</option>
                </select>
            </div>

            <div class="col-md-6">
                <label>Unité</label>
                <select name="id_unite" class="form-control">
                    <option value="">-- Facultatif --</option>
                    <?php foreach ($unites as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $prod['id_unite'] == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label>Stock initial</label>
                <input type="number" step="0.01" name="stock_initial" id="stock_initial" class="form-control"
                       value="<?= htmlspecialchars($prod['stock_initial']) ?>">
            </div>

            <div class="col-md-6" id="prix_achat_container" style="display: <?= $prod['stock_initial'] > 0 ? 'block' : 'none' ?>;">
                <label>Prix d'achat (DA)</label>
                <input type="number" step="0.01" name="prix_achat" id="prix_achat" class="form-control"
                       value="<?= htmlspecialchars($prod['prix_achat']) ?>">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" name="edit_produit" class="btn btn-primary">Enregistrer</button>
            <a href="table.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
document.getElementById('stock_initial').addEventListener('input', function() {
    const prixContainer = document.getElementById('prix_achat_container');
    if (parseFloat(this.value) > 0) {
        prixContainer.style.display = 'block';
    } else {
        prixContainer.style.display = 'none';
        document.getElementById('prix_achat').value = '';
    }
});
</script>

<?php require_once("../includes/footer.php"); ?>
