<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// ID du détail à supprimer
$id_detail = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_detail <= 0) {
    die("Produit introuvable");
}

// Récupérer info de la ligne (id_achat + produit)
$sql = "SELECT ad.id_achat, ad.id_produit, p.type
        FROM achats_details ad
        JOIN produits p ON ad.id_produit = p.id
        WHERE ad.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_detail]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    die("Détail introuvable");
}
$id_achat = $row['id_achat'];
$type_produit = $row['type'];

// Récupérer combien d’inventaires liés
$sqlInv = "SELECT COUNT(*) FROM inventaire WHERE id_achats_details=?";
$stmtInv = $pdo->prepare($sqlInv);
$stmtInv->execute([$id_detail]);
$nbInv = $stmtInv->fetchColumn();

// Si c’est un produit inventoré et qu’il y a des inventaires → demander confirmation
if ($type_produit === 'inventoree' && $nbInv > 0 && !isset($_POST['confirm_suppression'])) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Suppression produit</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    </head>
    <body class="bg-light">
    <?php include("../includes/header.php"); ?>
    <div class="container-fluid">
        <div class="row">
            <?php include("../includes/sidebar.php"); ?>

            <div class="col-md-9 col-lg-10 p-4">
                <div class="alert alert-warning">
                    <h3>⚠️ Attention !</h3>
                    <p>
                        Ce produit est de type <strong>inventorée</strong> et il y a 
                        <strong><?php echo $nbInv; ?></strong> inventaire(s) lié(s).
                    </p>
                    <p>
                        La suppression de ce détail va aussi supprimer les inventaires associés 
                        (tables <code>inventaire</code> et <code>inventaire_etat</code>).
                    </p>
                    <form method="post">
                        <button type="submit" name="confirm_suppression" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Confirmer la suppression
                        </button>
                        <a href="details.php?id=<?php echo $id_achat; ?>" class="btn btn-secondary">
                            Annuler
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// Ici on est en mode suppression (confirmation déjà donnée ou pas d’inventaires)
$pdo->beginTransaction();
try {
    // Supprimer inventaire_etat (si des inventaires existent)
    $stmtGetInv = $pdo->prepare("SELECT id FROM inventaire WHERE id_achats_details=?");
    $stmtGetInv->execute([$id_detail]);
    $inventaires = $stmtGetInv->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($inventaires)) {
        $in = implode(',', array_fill(0, count($inventaires), '?'));
        $pdo->prepare("DELETE FROM inventaire_etat WHERE id_inventaire IN ($in)")->execute($inventaires);
        $pdo->prepare("DELETE FROM inventaire WHERE id IN ($in)")->execute($inventaires);
    }

    // Supprimer la ligne d'achat
    $pdo->prepare("DELETE FROM achats_details WHERE id=?")->execute([$id_detail]);

    $pdo->commit();

    // Redirection
    header("Location: details.php?id=".$id_achat);
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur suppression : ".$e->getMessage());
}
