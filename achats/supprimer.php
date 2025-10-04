<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

$id_achat = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_achat <= 0) {
    die("Bon d'achat introuvable.");
}

// --- Indicateur de succès ---
$success = false;

// Étape 1 : Si confirmation déjà donnée → suppression
if (isset($_POST['confirm_suppression'])) {
    $pdo->beginTransaction();
    try {
        // Récupérer les id_inventaire liés à ce bon
        $sqlInv = "SELECT inv.id 
                   FROM inventaire inv
                   JOIN achats_details ad ON inv.id_achats_details = ad.id
                   WHERE ad.id_achat = ?";
        $stmtInv = $pdo->prepare($sqlInv);
        $stmtInv->execute([$id_achat]);
        $inventaires = $stmtInv->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($inventaires)) {
            // Supprimer inventaire_etat
            $in = implode(',', array_fill(0, count($inventaires), '?'));
            $pdo->prepare("DELETE FROM inventaire_etat WHERE id_inventaire IN ($in)")
                ->execute($inventaires);
            // Supprimer inventaire
            $pdo->prepare("DELETE FROM inventaire WHERE id IN ($in)")
                ->execute($inventaires);
        }

        // Supprimer les détails d’achat et le bon
        $pdo->prepare("DELETE FROM achats_details WHERE id_achat=?")->execute([$id_achat]);
        $pdo->prepare("DELETE FROM achats WHERE id=?")->execute([$id_achat]);

        $pdo->commit();

        // Marquer succès pour affichage du message + redirection JS
        $success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur suppression: ".$e->getMessage());
    }
}

// Étape 2 : Compter les produits inventoriés liés à ce bon
$sqlCount = "SELECT COUNT(*) AS nb_inventoree
             FROM achats_details ad
             JOIN produits p ON ad.id_produit = p.id
             WHERE ad.id_achat=? AND p.type='inventoree'";
$stmt = $pdo->prepare($sqlCount);
$stmt->execute([$id_achat]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$nbInventoree = $row['nb_inventoree'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppression Bon d'achat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<?php include("../includes/header.php"); ?>
<div class="container-fluid">
    <div class="row">
        <?php include("../includes/sidebar.php"); ?>

        <div class="col-md-9 col-lg-10 p-4">
            <h2 class="mb-4">Suppression du Bon d'achat</h2>

            <?php if ($success): ?>
                <!-- Message succès -->
                <div class="alert alert-success">
                    Bon d'achat supprimé avec succès. Redirection en cours...
                </div>
                <script>
                    setTimeout(function(){
                        window.location.href = "achats/historique.php";
                    }, 2000); // 2 secondes
                </script>
            <?php elseif ($nbInventoree > 0): ?>
                <div class="alert alert-warning">
                    <h5>Attention !</h5>
                    <p>Ce bon d'achat contient <strong><?php echo $nbInventoree; ?></strong> produit(s) de type “inventorée”.</p>
                    <p>La suppression va aussi supprimer les inventaires associés (tables <code>inventaire</code> et <code>inventaire_etat</code>).</p>
                </div>
                <form method="post">
                    <button type="submit" name="confirm_suppression" class="btn btn-danger">
                        Confirmer la suppression
                    </button>
                    <a href="liste_achats.php" class="btn btn-secondary">Annuler</a>
                </form>
            <?php else: ?>
                <?php
                // Pas de produit inventorié : suppression directe
                try {
                    $pdo->beginTransaction();
                    $pdo->prepare("DELETE FROM achats_details WHERE id_achat=?")->execute([$id_achat]);
                    $pdo->prepare("DELETE FROM achats WHERE id=?")->execute([$id_achat]);
                    $pdo->commit();
                    // Message succès + redirection JS
                    echo '<div class="alert alert-success">Bon d\'achat supprimé avec succès. Redirection en cours...</div>';
                    echo '<script>setTimeout(function(){window.location.href="historique.php";},2000);</script>';
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo '<div class="alert alert-danger">Erreur suppression: '.htmlspecialchars($e->getMessage()).'</div>';
                }
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
