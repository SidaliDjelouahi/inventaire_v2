<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Récupérer tous les achats avec le fournisseur
$sql = "
SELECT 
    a.id,
    a.num_achat,
    a.date,
    f.nom AS fournisseur_nom,
    SUM(ad.prix_achat * ad.quantite) AS total
FROM achats a
LEFT JOIN fournisseurs f ON a.id_fournisseur = f.id
LEFT JOIN achats_details ad ON ad.id_achat = a.id
GROUP BY a.id, a.num_achat, a.date, f.nom
ORDER BY a.date DESC
";
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

        <!-- Contenu principal -->
        <div class="col-md-9 col-lg-10 p-4">
            <h2 class="mb-4">Historique des Achats</h2>

            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Numéro d'achat</th>
                    <th>Date</th>
                    <th>Fournisseur</th>
                    <th>Total</th>
                    <th>Détails</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($achats as $achat): ?>
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
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <a href="bon.php" class="btn btn-success">Créer un nouveau Bon d'achat</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
