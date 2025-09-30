<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

$id_achat = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_achat <= 0) {
    die("Bon d'achat introuvable");
}

// --- Infos du bon ---
$sqlBon = "
SELECT a.*, f.nom AS fournisseur_nom
FROM achats a
LEFT JOIN fournisseurs f ON a.id_fournisseur=f.id
WHERE a.id=?
";
$stmt = $pdo->prepare($sqlBon);
$stmt->execute([$id_achat]);
$achat = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$achat) {
    die("Bon d'achat introuvable");
}

// --- Détails du bon ---
$sqlDetails = "
SELECT ad.id AS id_detail,
       ad.id_produit,
       p.nom AS produit_nom,
       ad.quantite,
       ad.prix_achat
FROM achats_details ad
LEFT JOIN produits p ON ad.id_produit=p.id
WHERE ad.id_achat=?
";
$stmt = $pdo->prepare($sqlDetails);
$stmt->execute([$id_achat]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Détails du Bon d'achat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<?php include("../includes/header.php"); ?>
<div class="container-fluid">
  <div class="row">
    <?php include("../includes/sidebar.php"); ?>

    <div class="col-md-9 col-lg-10 p-4">
      <h2 class="mb-4">Bon d'achat #<?= htmlspecialchars($achat['id']) ?></h2>

      <div class="mb-3">
        <a href="supprimer.php?id=<?= urlencode($achat['id']) ?>" 
           class="btn btn-danger"
           onclick="return confirm('Supprimer ce bon d\'achat ?');">
          <i class="bi bi-trash"></i> Supprimer ce bon
        </a>
        <a href="../inventaires/inventorer_tout.php?id_achat=<?= urlencode($achat['id']) ?>" 
           class="btn btn-success">
          <i class="bi bi-box-seam"></i> Inventorier tout
        </a>
        <a href="historique.php" class="btn btn-secondary">← Retour</a>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <dl class="row">
            <dt class="col-sm-3">Numéro d'achat</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($achat['num_achat']) ?></dd>

            <dt class="col-sm-3">Date</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($achat['date']) ?></dd>

            <dt class="col-sm-3">Fournisseur</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($achat['fournisseur_nom'] ?? '—') ?></dd>
          </dl>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Produit</th>
              <th>Quantité</th>
              <th>Prix Achat</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($details as $det): ?>
            <tr>
              <td><?= htmlspecialchars($det['id_detail']) ?></td>
              <td><?= htmlspecialchars($det['produit_nom']) ?></td>
              <td><?= htmlspecialchars($det['quantite']) ?></td>
              <td><?= number_format($det['prix_achat'],2,',',' ') ?> DA</td>
              <td>
                <!-- Bouton inventorier -->
                <a href="../inventaires/inventorer.php?id_achats_details=<?= urlencode($det['id_detail']) ?>&id_produit=<?= urlencode($det['id_produit']) ?>" 
                   class="btn btn-sm btn-primary">
                   <i class="bi bi-box-seam"></i> Inventorier
                </a>

                <!-- Bouton supprimer -->
                <a href="supprimer_produit.php?id=<?= urlencode($det['id_detail']) ?>" 
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Supprimer ce produit de ce bon ?');">
                   <i class="bi bi-trash"></i> Supprimer
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
