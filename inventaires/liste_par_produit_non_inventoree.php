<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// --- Récupérer l'ID produit ---
$id_produit = isset($_GET['id_produit']) ? intval($_GET['id_produit']) : 0;
if ($id_produit <= 0) {
    die("Produit invalide");
}

// --- Infos sur le produit ---
$stmtProd = $pdo->prepare("SELECT id, code, nom, stock_initial FROM produits WHERE id=?");
$stmtProd->execute([$id_produit]);
$produit = $stmtProd->fetch(PDO::FETCH_ASSOC);
if (!$produit) {
    die("Produit introuvable");
}

// --- Stock initial déjà inventorié ---
$sqlInit = "
    SELECT COUNT(*) AS qte_inv
    FROM inventaire i
    LEFT JOIN achats_details ad ON ad.id = i.id_achats_details
    WHERE i.id_achats_details=0
      AND ad.id IS NULL
";
$stmtInv = $pdo->prepare("SELECT COUNT(*) AS qte_inv FROM inventaire WHERE id_achats_details=0");
$stmtInv->execute();
$qteInventoreeInit = $stmtInv->fetch(PDO::FETCH_ASSOC)['qte_inv'];
$qteRestanteInit = $produit['stock_initial'] - $qteInventoreeInit;

// --- Achats de ce produit ---
$sql = "
SELECT 
    ad.id AS id_detail,
    ad.prix_achat,
    ad.quantite,
    ad.date_peremption,
    IFNULL(inv.qte_inventoree,0) AS qte_inventoree,
    (ad.quantite - IFNULL(inv.qte_inventoree,0)) AS qte_restante
FROM achats_details ad
LEFT JOIN (
    SELECT id_achats_details, COUNT(*) AS qte_inventoree 
    FROM inventaire 
    WHERE id_achats_details<>0
    GROUP BY id_achats_details
) inv ON inv.id_achats_details=ad.id
WHERE ad.id_produit=? 
ORDER BY ad.date_peremption
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_produit]);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Produits non inventoriés</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3><i class="bi bi-box-seam me-2"></i>Produits non inventoriés</h3>
  <p><strong>Produit :</strong> <?=htmlspecialchars($produit['code'])?> – <?=htmlspecialchars($produit['nom'])?></p>

  <div class="table-responsive mb-4">
    <table class="table table-bordered table-sm">
      <thead class="table-light">
        <tr>
          <th>Type</th>
          <th>Quantité</th>
          <th>Inventoriée</th>
          <th>Restante</th>
          <th>Date péremption</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if($produit['stock_initial']>0): ?>
        <tr class="table-warning">
          <td>Stock initial</td>
          <td><?=$produit['stock_initial']?></td>
          <td><?=$qteInventoreeInit?></td>
          <td><?=$qteRestanteInit?></td>
          <td>-</td>
          <td>
            <?php if($qteRestanteInit>0): ?>
            <a href="inventorer_stock_initial.php?id_produit=<?=$id_produit?>" class="btn btn-sm btn-primary">
              Inventorier stock initial
            </a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>

        <?php foreach($lignes as $l): ?>
        <tr>
          <td>Achat #<?=$l['id_detail']?></td>
          <td><?=$l['quantite']?></td>
          <td><?=$l['qte_inventoree']?></td>
          <td><?=$l['qte_restante']?></td>
          <td><?=$l['date_peremption']?></td>
          <td>
            <?php if($l['qte_restante']>0): ?>
            <a href="../inventaires/inventorer.php?id_achats_details=<?=$l['id_detail']?>&id_produit=<?=$id_produit?>" 
               class="btn btn-sm btn-success">
              Inventorier
            </a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <a href="liste_tout_produits_non_inventoree.php" class="btn btn-secondary btn-sm">← Retour</a>
</div>
</body>
</html>
