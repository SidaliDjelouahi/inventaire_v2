<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// --- Récupérer tous les achats avec total ---
$sql = "SELECT 
            a.id,
            a.num_achat,
            a.date,
            f.nom AS fournisseur_nom,
            SUM(ad.prix_achat * ad.quantite) AS total
        FROM achats a
        LEFT JOIN fournisseurs f ON a.id_fournisseur = f.id
        LEFT JOIN achats_details ad ON ad.id_achat = a.id
        GROUP BY a.id, a.num_achat, a.date, f.nom
        ORDER BY a.date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$achats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Achats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<?php include("../includes/header.php"); ?>
<div class="container-fluid">
    <div class="row">
        <?php include("../includes/sidebar.php"); ?>

        <div class="col-md-9 col-lg-10 p-4">
            <h2 class="mb-4">Historique des Achats</h2>
            <p><a href="bon.php" class="btn btn-success">Créer un nouveau Bon d'achat</a></p>

            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Numéro d'achat</th>
                    <th>Date</th>
                    <th>Fournisseur</th>
                    <th>Total</th>
                    <th>Détails</th>
                    <th>Inventaire</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($achats as $achat): ?>
                    <?php
                    // --- Vérification inventaire pour cet achat ---
                    $etatInventaire = 'inventorie'; // état par défaut

                    // Récupérer les détails de l'achat avec quantité inventoriée
                    $requeteDetails = $pdo->prepare("
                        SELECT 
                            ad.id AS id_detail,
                            ad.id_produit,
                            ad.quantite,
                            ad.prix_achat,
                            p.type,
                            (
                              SELECT COUNT(*) 
                              FROM inventaire i 
                              WHERE i.id_achats_details = ad.id
                            ) AS nb_inventaire
                        FROM achats_details ad
                        INNER JOIN produits p ON ad.id_produit = p.id
                        WHERE ad.id_achat = ?
                    ");
                    $requeteDetails->execute([$achat['id']]);
                    $details = $requeteDetails->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($details as $det) {
                        // Condition: prix > 300 et produit type 'inventoree'
                        if ($det['prix_achat'] > 300 && $det['type'] === 'inventoree') {
                            if ($det['nb_inventaire'] == 0) {
                                $etatInventaire = 'non'; // Non inventorié
                                break; // on peut s'arrêter dès qu'on trouve un non inventorié
                            } elseif ($det['nb_inventaire'] < $det['quantite']) {
                                $etatInventaire = 'partiel'; // Inventorié partiel
                                // on continue pour vérifier s'il y a encore pire
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($achat['id']) ?></td>
                        <td><?= htmlspecialchars($achat['num_achat']) ?></td>
                        <td><?= htmlspecialchars($achat['date']) ?></td>
                        <td><?= htmlspecialchars($achat['fournisseur_nom'] ?? '—') ?></td>
                        <td><?= number_format($achat['total'] ?? 0, 2, ',', ' ') ?> DA</td>
                        <td>
                            <a href="details.php?id=<?= $achat['id'] ?>" class="btn btn-sm btn-primary">
                                Voir détails
                            </a>
                        </td>
                        <td>
                            <?php if ($etatInventaire === 'non'): ?>
                                <button class="btn btn-sm btn-danger">Non inventorié</button>
                            <?php elseif ($etatInventaire === 'partiel'): ?>
                                <button class="btn btn-sm btn-warning">Inventorié partiel</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-info">Inventorié</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
