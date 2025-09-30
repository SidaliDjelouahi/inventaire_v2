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

try {
    // Commencer une transaction
    $pdo->beginTransaction();

    // 1. Récupérer tous les id des détails liés à ce bon
    $stmt = $pdo->prepare("SELECT id FROM achats_details WHERE id_achat=?");
    $stmt->execute([$id_achat]);
    $idsDetails = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($idsDetails && count($idsDetails) > 0) {
        // 2. Supprimer dans inventaire tout ce qui correspond à ces détails
        $in = implode(',', array_fill(0, count($idsDetails), '?'));
        $stmtDelInv = $pdo->prepare("DELETE FROM inventaire WHERE id_achats_details IN ($in)");
        $stmtDelInv->execute($idsDetails);
    }

    // 3. Supprimer les détails
    $stmtDelDetails = $pdo->prepare("DELETE FROM achats_details WHERE id_achat=?");
    $stmtDelDetails->execute([$id_achat]);

    // 4. Supprimer le bon d’achat
    $stmtDelAchat = $pdo->prepare("DELETE FROM achats WHERE id=?");
    $stmtDelAchat->execute([$id_achat]);

    // Valider la transaction
    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    echo "<div class='alert alert-danger'>Erreur lors de la suppression : " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Suppression réussie</title>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="alert alert-success">
        ✅ Le bon d'achat et ses lignes ont été supprimés avec succès.
    </div>
    <a href="historique.php" class="btn btn-primary">
        ⬅ Retour à l'historique
    </a>
</div>
</body>
</html>
