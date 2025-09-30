<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

$id_achat = isset($_GET['id_achat']) ? intval($_GET['id_achat']) : 0;
if ($id_achat <= 0) {
    die("Bon d'achat introuvable");
}

// Infos du bon d'achat
$sqlBon = "SELECT a.*, f.nom AS fournisseur_nom
           FROM achats a
           LEFT JOIN fournisseurs f ON a.id_fournisseur=f.id
           WHERE a.id=?";
$stmt = $pdo->prepare($sqlBon);
$stmt->execute([$id_achat]);
$achat = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$achat) {
    die("Bon d'achat introuvable");
}

// Récupérer toutes les lignes (produits) de ce bon
$sqlDetails = "SELECT ad.id AS id_detail,
                      ad.id_produit,
                      p.nom AS produit_nom,
                      ad.quantite,
                      ad.prix_achat
               FROM achats_details ad
               LEFT JOIN produits p ON ad.id_produit=p.id
               WHERE ad.id_achat=?";
$stmt = $pdo->prepare($sqlDetails);
$stmt->execute([$id_achat]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dernier numéro d’inventaire global
$lastNum = $pdo->query("SELECT MAX(inventaire) AS max_inv FROM inventaire")->fetch(PDO::FETCH_ASSOC);
$startNum = $lastNum && $lastNum['max_inv'] ? intval($lastNum['max_inv'])+1 : 1;

// Construction d’une grille unique pour tout le bon
$rows = [];
$numInv = $startNum;
foreach ($details as $det) {
    for ($i=0; $i < $det['quantite']; $i++) {
        $rows[] = [
            'id_detail' => $det['id_detail'],
            'id_produit'=> $det['id_produit'],
            'produit_nom'=> $det['produit_nom'],
            'prix_achat'=> $det['prix_achat'],
            'inv_num'=> $numInv++
        ];
    }
}

// Sauvegarde
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['inventaire'])) {
    $inventaires = $_POST['inventaire']; // array de numéros
    $sns = $_POST['sn']; // array de SN
    $id_details = $_POST['id_detail']; // array id_detail correspondant
    $pdo->beginTransaction();
    try {
        for ($i=0;$i<count($inventaires);$i++) {
            $invNum = intval($inventaires[$i]);
            $snVal = trim($sns[$i]);
            $idDet = intval($id_details[$i]);
            $stmtIns = $pdo->prepare("INSERT INTO inventaire (id_achats_details, inventaire, sn) VALUES (?,?,?)");
            $stmtIns->execute([$idDet,$invNum,$snVal]);
        }
        $pdo->commit();
        header("Location: ../achats/details.php?id=".$id_achat);
        exit;
    } catch(Exception $e) {
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
<title>Inventorier tout le bon</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-3">Inventorier tout le bon d'achat</h2>
  <div class="card mb-4">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-3">Numéro du bon</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($achat['num_achat']) ?></dd>

        <dt class="col-sm-3">Date</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($achat['date']) ?></dd>

        <dt class="col-sm-3">Fournisseur</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($achat['fournisseur_nom'] ?? '—') ?></dd>
      </dl>
    </div>
  </div>

  <form method="post">
    <div class="table-responsive mb-3">
      <table class="table table-bordered">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Produit</th>
            <th>Prix Achat</th>
            <th>Numéro inventaire</th>
            <th>Numéro de série (SN)</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $index=>$row): ?>
          <tr>
            <td><?= $index+1 ?></td>
            <td><?= htmlspecialchars($row['produit_nom']) ?></td>
            <td><?= number_format($row['prix_achat'],2,',',' ') ?> DA</td>
            <td>
              <input type="hidden" name="id_detail[]" value="<?= $row['id_detail'] ?>">
              <input type="number" name="inventaire[]" class="form-control" value="<?= $row['inv_num'] ?>" required>
            </td>
            <td>
              <input type="text" name="sn[]" class="form-control" placeholder="SN...">
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <button type="submit" class="btn btn-success">
      <i class="bi bi-save"></i> Sauvegarder tout
    </button>
    <a href="../achats/details.php?id=<?= urlencode($id_achat) ?>" class="btn btn-secondary">Annuler</a>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
