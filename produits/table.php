<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Ajouter un produit ---
if (isset($_POST['add_produit'])) {
    $code = trim($_POST['code']);
    $nom = trim($_POST['nom']);
    $id_categorie = $_POST['id_categorie'];
    $type = $_POST['type'];
    $id_unite = $_POST['id_unite'] ?: null;
    $stock_initial = floatval($_POST['stock_initial']);
    $prix_achat = isset($_POST['prix_achat']) && $_POST['prix_achat'] !== '' ? floatval($_POST['prix_achat']) : null;

    // Vérifier le nom obligatoire
    if (empty($nom)) {
        $error_msg = "Le nom du produit ne peut pas être vide.";
    }

    // Génération auto du code si vide
    if (empty($error_msg) && $code === '') {
        do {
            $code_propose = 'PRD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $check = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE code = ?");
            $check->execute([$code_propose]);
            $exists = $check->fetchColumn() > 0;
        } while ($exists);
        $code = $code_propose;
    }

    // Vérifier doublons
    if (empty($error_msg)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE code = ?");
        $check->execute([$code]);
        if ($check->fetchColumn() > 0) {
            $error_msg = "Ce code existe déjà. Merci d'en choisir un autre.";
        }

        $checkNom = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE nom = ?");
        $checkNom->execute([$nom]);
        if ($checkNom->fetchColumn() > 0) {
            $error_msg = "Ce nom existe déjà. Merci d'en choisir un autre.";
        }
    }

    // Si stock > 0, prix_achat obligatoire
    if (empty($error_msg) && $stock_initial > 0 && ($prix_achat === null || $prix_achat <= 0)) {
        $error_msg = "Veuillez saisir un prix d'achat pour les produits avec un stock initial.";
    }

    // --- Insertion produit ---
    if (empty($error_msg)) {
        $pdo->beginTransaction();
        try {
            // 1️⃣ Insérer le produit
            $stmt = $pdo->prepare("INSERT INTO produits 
                (code, nom, id_categorie, type, id_unite, stock_initial, prix_achat)
                VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$code, $nom, $id_categorie, $type, $id_unite, $stock_initial, $prix_achat]);
            $id_produit = $pdo->lastInsertId();

            // 2️⃣ Si stock_initial > 0 => créer automatiquement un bon d’achat "STOCK_INITIAL"
            if ($stock_initial > 0) {
                // Vérifier ou créer le fournisseur 'stock_initial'
                $stmtF = $pdo->prepare("SELECT id FROM fournisseurs WHERE nom = 'stock_initial'");
                $stmtF->execute();
                $id_fournisseur = $stmtF->fetchColumn();

                if (!$id_fournisseur) {
                    $pdo->prepare("INSERT INTO fournisseurs (nom, adresse, telephone) VALUES ('stock_initial', '', '')")->execute();
                    $id_fournisseur = $pdo->lastInsertId();
                }

                // ✅ Vérifier ou créer le bon d'achat STOCK_INITIAL spécifique à chaque produit
                $num_achat = 'STOCK_INITIAL' . $id_produit;

                $stmtA = $pdo->prepare("SELECT id FROM achats WHERE num_achat = ?");
                $stmtA->execute([$num_achat]);
                $id_achat = $stmtA->fetchColumn();

                if (!$id_achat) {
                    $pdo->prepare("INSERT INTO achats (num_achat, date, id_fournisseur) VALUES (?, NOW(), ?)")
                         ->execute([$num_achat, $id_fournisseur]);
                    $id_achat = $pdo->lastInsertId();
                }


                // Insérer dans achats_details
                $stmtD = $pdo->prepare("INSERT INTO achats_details (id_achat, id_produit, prix_achat, quantite) VALUES (?,?,?,?)");
                $stmtD->execute([$id_achat, $id_produit, $prix_achat, $stock_initial]);

                // Mettre le stock_initial du produit à 0
                $pdo->prepare("UPDATE produits SET stock_initial = 0 WHERE id = ?")->execute([$id_produit]);
            }

            $pdo->commit();
            header("Location: " . ROOT_URL . "/produits/table.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Erreur lors de l'ajout du produit : " . $e->getMessage();
        }
    }
}

// --- Récupération des produits ---
$sql = "SELECT p.*, c.nom AS categorie_nom, u.nom AS unite_nom
        FROM produits p
        LEFT JOIN categories c ON p.id_categorie = c.id
        LEFT JOIN unites u ON p.id_unite = u.id
        ORDER BY p.id DESC";
$produits = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// --- Récupération des catégories et unités ---
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$unites = $pdo->query("SELECT * FROM unites ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2>Gestion des produits</h2>

    <?php if (!empty($error_msg)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ Ajouter</button>
        <input type="text" id="search" class="form-control w-25" placeholder="Rechercher un produit...">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Type</th>
                    <th>Unité</th>
                    <th>Stock initial</th>
                    <th>Prix achat</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="resultats">
            <?php if ($produits): ?>
                <?php foreach ($produits as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['code']) ?></td>
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= htmlspecialchars($p['categorie_nom']) ?></td>
                        <td><?= htmlspecialchars($p['type']) ?></td>
                        <td><?= htmlspecialchars($p['unite_nom']) ?></td>
                        <td><?= htmlspecialchars($p['stock_initial']) ?></td>
                        <td><?= $p['prix_achat'] ? htmlspecialchars($p['prix_achat']) : '-' ?></td>
                        <td>
                            <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i></a>
                            <a href="supprimer.php?id=<?= $p['id'] ?>" 
                               onclick="return confirm('Voulez-vous vraiment supprimer <?= htmlspecialchars($p['nom']) ?> ?');" 
                               class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
                            <a href="imprimer_code.php?id=<?= $p['id'] ?>" target="_blank"
                               class="btn btn-secondary btn-sm"><i class="bi bi-printer"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center">Aucun produit trouvé</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajouter Produit -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter produit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label>Code</label>
                    <input type="text" name="code" class="form-control" 
                           value="<?= 'PRD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6)) ?>">
                    <small class="text-muted">Laissez vide pour générer automatiquement</small>
                </div>
                <div class="col-md-6">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required autocomplete="off">
                </div>

                <div class="col-md-6">
                    <label>Catégorie</label>
                    <select name="id_categorie" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Type</label>
                    <select name="type" class="form-control" required>
                        <option value="consommable">Consommable</option>
                        <option value="inventoree">Inventoriée</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Unité</label>
                    <select name="id_unite" class="form-control">
                        <option value="">-- Facultatif --</option>
                        <?php foreach ($unites as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Stock initial</label>
                    <input type="number" step="0.01" name="stock_initial" id="stock_initial" class="form-control" value="0">
                </div>

                <div class="col-md-6 d-none" id="prix_achat_container">
                    <label>Prix d'achat (DA)</label>
                    <input type="number" step="0.01" name="prix_achat" id="prix_achat" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" name="add_produit" class="btn btn-success">Ajouter</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('search').addEventListener('keyup', function() {
    const query = this.value;
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'search.php?q=' + encodeURIComponent(query), true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('resultats').innerHTML = this.responseText;
        }
    };
    xhr.send();
});

const stockInput = document.getElementById('stock_initial');
const prixContainer = document.getElementById('prix_achat_container');
stockInput.addEventListener('input', function() {
    if (parseFloat(this.value) > 0) {
        prixContainer.classList.remove('d-none');
    } else {
        prixContainer.classList.add('d-none');
        document.getElementById('prix_achat').value = '';
    }
});
</script>

<?php require_once("../includes/footer.php"); ?>
