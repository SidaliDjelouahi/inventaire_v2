<?php 
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// Récupération paramètres
$id_detail = isset($_GET['id_achats_details']) ? intval($_GET['id_achats_details']) : 0;
$id_produit = isset($_GET['id_produit']) ? intval($_GET['id_produit']) : 0;
if ($id_detail <= 0 || $id_produit <= 0) {
    die("Paramètres manquants");
}

// Infos du détail d'achat
$sql = "SELECT ad.*, a.num_achat, a.id AS id_achat, 
               p.nom AS produit_nom, p.type AS produit_type, ad.prix_achat
        FROM achats_details ad
        JOIN achats a ON ad.id_achat=a.id
        JOIN produits p ON ad.id_produit=p.id
        WHERE ad.id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_detail]);
$detail = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$detail) {
    die("Détail introuvable");
}

// Vérifier combien d'articles sont déjà inventoriés
$stmtInv = $pdo->prepare("SELECT COUNT(*) FROM inventaire WHERE id_achats_details = ?");
$stmtInv->execute([$id_detail]);
$nbInventories = $stmtInv->fetchColumn();

// Nombre total prévu
$quantite = (int)$detail['quantite'];

// Calculer combien restent à inventorier
$reste = max(0, $quantite - $nbInventories);

// Dernier numéro d'inventaire
$lastNum = $pdo->query("SELECT MAX(inventaire) AS max_inv FROM inventaire")->fetch(PDO::FETCH_ASSOC);
$startNum = $lastNum && $lastNum['max_inv'] ? intval($lastNum['max_inv']) + 1 : 1;

// Sauvegarde des nouveaux inventaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inventaire'])) {
    $inventaires = $_POST['inventaire']; // tableau numéros
    $sns = $_POST['sn']; // tableau sn
    $pdo->beginTransaction();
    try {
        for ($i=0; $i<count($inventaires); $i++) {
            $invNum = intval($inventaires[$i]);
            $snVal = trim($sns[$i]);
            $stmtIns = $pdo->prepare("INSERT INTO inventaire (id_achats_details, id_produit, inventaire, sn) VALUES (?,?,?,?)");
            $stmtIns->execute([$id_detail, $id_produit, $invNum, $snVal]);
        }
        $pdo->commit();
        header("Location: ../achats/details.php?id=".$detail['id_achat']);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Erreur: ".$e->getMessage()."</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Inventorier produit</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-3">Inventorier produit</h2>

  <div class="card mb-4">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-3">Bon d'achat</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($detail['num_achat']) ?></dd>

        <dt class="col-sm-3">Produit</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($detail['produit_nom']) ?></dd>

        <dt class="col-sm-3">Type</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($detail['produit_type']) ?></dd>

        <dt class="col-sm-3">Prix Achat</dt>
        <dd class="col-sm-9"><?= number_format($detail['prix_achat'],2,',',' ') ?> DA</dd>

        <dt class="col-sm-3">Quantité totale</dt>
        <dd class="col-sm-9"><?= $quantite ?></dd>

        <dt class="col-sm-3">Inventoriés</dt>
        <dd class="col-sm-9"><?= $nbInventories ?></dd>

        <dt class="col-sm-3">Restant à inventorier</dt>
        <dd class="col-sm-9"><?= $reste ?></dd>
      </dl>
    </div>
  </div>

  <?php if ($detail['produit_type'] == 'inventoree'): ?>
    <?php if ($nbInventories >= $quantite): ?>
      <div class="alert alert-success d-flex align-items-center">
        <i class="bi bi-check-circle me-2"></i>
        Inventaire fait pour ce produit.
      </div>
      <a href="../achats/details.php?id=<?= urlencode($detail['id_achat']) ?>" class="btn btn-secondary">Retour</a>
    <?php else: ?>
      <form method="post">
        <div class="table-responsive mb-3">
          <table class="table table-bordered">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Numéro inventaire</th>
                <th>Numéro de série (SN)</th>
              </tr>
            </thead>
            <tbody>
            <?php for($i=0;$i<$reste;$i++): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td>
                  <input type="number" name="inventaire[]" class="form-control" 
                         value="<?= $startNum+$i ?>" required>
                </td>
                <td>
                  <input type="text" name="sn[]" class="form-control" placeholder="SN...">
                </td>
              </tr>
            <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <button type="submit" class="btn btn-success">
          <i class="bi bi-save"></i> Sauvegarder
        </button>
        <a href="../achats/details.php?id=<?= urlencode($detail['id_achat']) ?>" class="btn btn-secondary">Annuler</a>
      </form>
    <?php endif; ?>
  <?php else: ?>
    <div class="alert alert-info">
      Ce produit est de type <strong>consommable</strong> : pas d’inventaire requis.
    </div>
    <a href="../achats/details.php?id=<?= urlencode($detail['id_achat']) ?>" class="btn btn-secondary">Retour</a>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
