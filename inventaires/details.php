<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// --- Récupérer l'ID ---
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Identifiant invalide.");
}

// --- Vérifier si c’est un achat ---
$sqlAchat = "SELECT a.id, a.num_achat, a.date, f.nom AS fournisseur
             FROM achats a
             LEFT JOIN fournisseurs f ON a.id_fournisseur = f.id
             WHERE a.id = ?";
$stmtAchat = $pdo->prepare($sqlAchat);
$stmtAchat->execute([$id]);
$achat = $stmtAchat->fetch(PDO::FETCH_ASSOC);

// --- Vérifier si c’est une décharge ---
$sqlDecharge = "SELECT d.id, d.num_decharge, d.date, b.bureau AS bureau_nom
                FROM decharges d
                LEFT JOIN bureaux b ON d.id_bureau = b.id
                WHERE d.id = ?";
$stmtDecharge = $pdo->prepare($sqlDecharge);
$stmtDecharge->execute([$id]);
$decharge = $stmtDecharge->fetch(PDO::FETCH_ASSOC);

// --- Si aucun résultat ---
if (!$achat && !$decharge) {
    die("Aucun achat ni décharge trouvé pour cet ID.");
}

// --- Récupérer les détails des produits ---
$details = [];
if ($achat) {
    $sqlDetails = "SELECT p.nom AS produit, ad.quantite, ad.prix_achat
                   FROM achats_details ad
                   INNER JOIN produits p ON ad.id_produit = p.id
                   WHERE ad.id_achat = ?";
    $stmtDet = $pdo->prepare($sqlDetails);
    $stmtDet->execute([$id]);
    $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sqlDetails = "SELECT p.nom AS produit, dd.quantite
                   FROM decharges_details dd
                   INNER JOIN produits p ON dd.id_produit = p.id
                   WHERE dd.id_decharge = ?";
    $stmtDet = $pdo->prepare($sqlDetails);
    $stmtDet->execute([$id]);
    $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails mouvement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Détails du mouvement</h1>

        <?php if ($achat): ?>
            <h3>Achat n° <?= htmlspecialchars($achat['num_achat']) ?></h3>
            <p><strong>Date :</strong> <?= htmlspecialchars($achat['date']) ?></p>
            <p><strong>Fournisseur :</strong> <?= htmlspecialchars($achat['fournisseur']) ?></p>
        <?php else: ?>
            <h3>Décharge n° <?= htmlspecialchars($decharge['num_decharge']) ?></h3>
            <p><strong>Date :</strong> <?= htmlspecialchars($decharge['date']) ?></p>
            <p><strong>Bureau :</strong> <?= htmlspecialchars($decharge['bureau_nom']) ?></p>
        <?php endif; ?>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <?php if ($achat): ?><th>Prix achat</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($details as $det): ?>
                    <tr>
                        <td><?= htmlspecialchars($det['produit']) ?></td>
                        <td><?= htmlspecialchars($det['quantite']) ?></td>
                        <?php if ($achat): ?>
                            <td><?= htmlspecialchars($det['prix_achat']) ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="../inventaires/fiche.php" class="btn btn-secondary mt-3">← Retour</a>
    </div>
</body>
</html>
