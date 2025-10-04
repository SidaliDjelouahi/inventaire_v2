<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

$id_produit = isset($_GET['id_produit']) ? intval($_GET['id_produit']) : 0;
if ($id_produit <= 0) {
    die("Produit invalide");
}

// Infos produit (stock initial)
$stmtProd = $pdo->prepare("SELECT id, code, nom, stock_initial FROM produits WHERE id=?");
$stmtProd->execute([$id_produit]);
$produit = $stmtProd->fetch(PDO::FETCH_ASSOC);
if (!$produit) die("Produit introuvable");

// Inventorié déjà sur stock initial (id_achats_details=0)
$stmtInv = $pdo->prepare("SELECT COUNT(*) AS qte_inv FROM inventaire WHERE id_achats_details=0");
$stmtInv->execute();
$qteInventoree = $stmtInv->fetch(PDO::FETCH_ASSOC)['qte_inv'];

$qteRestante = max(0, $produit['stock_initial'] - $qteInventoree);

// Dernier numéro inventaire
$lastNum = $pdo->query("SELECT MAX(inventaire) AS max_inv FROM inventaire")->fetch(PDO::FETCH_ASSOC);
$startNum = $lastNum && $lastNum['max_inv'] ? intval($lastNum['max_inv'])+1 : 1;

// Enregistrement
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['inventaire'])) {
    $invNums = $_POST['inventaire'];
    $sns     = $_POST['sn'];
    $pdo->beginTransaction();
    try {
        for($i=0;$i<count($invNums);$i++){
            $inv=intval($invNums[$i]);
            $sn=trim($sns[$i]);
            // pas de id_produit, juste id_achats_details=0
            $pdo->prepare("INSERT INTO inventaire (id_achats_details,inventaire,sn)
                           VALUES (?,?,?)")
                ->execute([0,$inv,$sn]);
        }
        $pdo->commit();
        header("Location: liste_par_produit_non_inventorer.php?id_produit=".$id_produit);
        exit;
    }catch(Exception $e){
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
<title>Inventorier stock initial</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2>Inventorier stock initial</h2>
  <p><strong>Produit :</strong> <?=htmlspecialchars($produit['code'])?> – <?=htmlspecialchars($produit['nom'])?></p>
  <p><strong>Stock initial :</strong> <?=$produit['stock_initial']?> |
     <strong>Déjà inventorié :</strong> <?=$qteInventoree?> |
     <strong>Restant :</strong> <?=$qteRestante?></p>

  <form method="post">
    <div class="table-responsive mb-3">
      <table class="table table-bordered">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Numéro inventaire</th>
            <th>Numéro série (SN)</th>
          </tr>
        </thead>
        <tbody>
          <?php for($i=0;$i<$qteRestante;$i++): ?>
          <tr>
            <td><?=$i+1?></td>
            <td><input type="number" name="inventaire[]" class="form-control" value="<?=$startNum+$i?>" required></td>
            <td><input type="text" name="sn[]" class="form-control" placeholder="SN..."></td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
    <button type="submit" class="btn btn-success">Sauvegarder</button>
    <a href="liste_par_produit_non_inventorer.php?id_produit=<?=$id_produit?>" class="btn btn-secondary">Annuler</a>
  </form>
</div>
</body>
</html>
