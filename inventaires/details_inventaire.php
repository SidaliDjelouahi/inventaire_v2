<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

$id_inventaire = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_inventaire <= 0) {
    die("Inventaire introuvable.");
}

// --- Récupération info inventaire ---
$sqlInv = "SELECT i.*, p.nom AS produit_nom 
           FROM inventaire i
           LEFT JOIN achats_details ad ON i.id_achats_details = ad.id
           LEFT JOIN produits p ON ad.id_produit = p.id
           WHERE i.id = ?";
$stmt = $pdo->prepare($sqlInv);
$stmt->execute([$id_inventaire]);
$inventaire = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inventaire) {
    die("Inventaire introuvable."); 
}

// --- Historique des états ---
$sqlHist = "SELECT e.*
            FROM inventaire_etat e
            WHERE e.id_inventaire = ?
            ORDER BY e.id DESC";
$stmtHist = $pdo->prepare($sqlHist);
$stmtHist->execute([$id_inventaire]);
$etats = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Détails inventaire</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <h3><i class="bi bi-clipboard-data me-2"></i>Inventaire n° <?= htmlspecialchars($inventaire['id']) ?></h3>
  <p><strong>Produit :</strong> <?= htmlspecialchars($inventaire['produit_nom']) ?></p>
  <p><strong>Référence :</strong> <?= htmlspecialchars($inventaire['inventaire']) ?></p>
  <p><strong>SN :</strong> <?= htmlspecialchars($inventaire['sn']) ?></p>

  <h4 class="mt-4">Historique des états</h4>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>État</th>
        <th>Action</th>
        <th>Détails</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($etats): ?>
        <?php foreach ($etats as $etat): ?>
          <tr>
            <td><?= htmlspecialchars($etat['id']) ?></td>
            <td><?= htmlspecialchars($etat['etat']) ?></td>
            <td>
              <?php
              // exemple simple : si etat contient le mot 'décharge'
              if (stripos($etat['etat'], 'decharge') !== false) {
                  echo "Décharge";
              } else {
                  echo "Autre";
              }
              ?>
            </td>
            <td>
              <?php
              // lien selon action
              if (stripos($etat['etat'], 'decharge') !== false) {
                  // si tu stockes l'id_decharge dans un champ, récupère-le
                  // par exemple $etat['id_action'] = id_decharge
                  echo '<a href="../decharges/details.php?id='.intval($etat['id_action']).'" class="btn btn-sm btn-primary">
                          <i class="bi bi-eye"></i> Détails
                        </a>';
              } else {
                  // autre action (par ex. page générique)
                  echo '<a href="../actions/details.php?id='.intval($etat['id_action']).'" class="btn btn-sm btn-secondary">
                          <i class="bi bi-eye"></i> Détails
                        </a>';
              }
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" class="text-center">Aucun état trouvé</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <a href="table.php" class="btn btn-secondary mt-3">← Retour</a>
</div>
</body>
</html>
