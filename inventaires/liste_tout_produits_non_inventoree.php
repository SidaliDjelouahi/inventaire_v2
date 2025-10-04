<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// --- Récupération du mot-clé de recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// --- Nouvelle requête principale
$sql = "
SELECT 
  p.id AS id_produit,
  p.code,
  p.nom,
  p.stock_initial
    + IFNULL(SUM(ad.quantite),0)
    - IFNULL(SUM(inv_qte.qte_inventoree),0) AS qte_restante
FROM produits p
LEFT JOIN achats_details ad 
  ON ad.id_produit = p.id AND ad.prix_achat > 3000  -- filtrage prix_achat ici
LEFT JOIN (
    SELECT a.id_produit, COUNT(i.id) AS qte_inventoree
    FROM inventaire i
    INNER JOIN achats_details a ON a.id = i.id_achats_details
    GROUP BY a.id_produit
) AS inv_qte ON inv_qte.id_produit = p.id
WHERE 
    p.type = 'inventoree'  -- filtrage type ici
    AND (p.nom LIKE :search OR p.code LIKE :search)
GROUP BY p.id
HAVING qte_restante > 0
ORDER BY qte_restante DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':search' => "%$search%"]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Produits non inventoriés</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="col-md-9 col-lg-10 p-4">
      <h3 class="mb-3"><i class="bi bi-box-seam me-2"></i>Produits non inventoriés</h3>

      <!-- Moteur de recherche AJAX -->
      <input type="text" id="search" class="form-control mb-3" placeholder="Rechercher un produit...">

      <div id="table-container">
        <table class="table table-bordered table-sm">
          <thead class="table-light">
            <tr>
              <th>Code</th>
              <th>Nom</th>
              <th>Quantité restante</th>
              <th>Détails</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($produits as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['code']) ?></td>
              <td><?= htmlspecialchars($p['nom']) ?></td>
              <td><?= htmlspecialchars($p['qte_restante']) ?></td>
              <td>
                <a href="liste_par_produit_non_inventoree.php?id_produit=<?= $p['id_produit'] ?>" class="btn btn-primary btn-sm">
                  Détails
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

<script>
$(document).ready(function(){
  $('#search').on('keyup', function(){
    var search = $(this).val();
    $.get('liste_tout_produits_non_inventoree.php', {search: search}, function(data){
      // recharge seulement la div contenant le tableau
      $('#table-container').html($(data).find('#table-container').html());
    });
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
