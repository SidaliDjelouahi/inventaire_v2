<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Vérifier si l'ID est fourni ---
if (!isset($_GET['id'])) {
    header("Location: table.php");
    exit;
}

$id = intval($_GET['id']);

// --- Vérifier si l'utilisateur a confirmé la suppression totale ---
if (isset($_GET['confirm']) && $_GET['confirm'] === 'oui') {
    try {
        // Supprimer d'abord les lignes liées dans decharges_details
        $deleteDetails = $pdo->prepare("DELETE FROM decharges_details WHERE id_produit = ?");
        $deleteDetails->execute([$id]);

        // Puis supprimer le produit
        $deleteProduit = $pdo->prepare("DELETE FROM produits WHERE id = ?");
        $deleteProduit->execute([$id]);

        $_SESSION['success'] = "✅ Produit et ses détails supprimés avec succès.";
        header("Location: table.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        header("Location: table.php");
        exit;
    }
}

// --- Vérifier si le produit est utilisé ---
$check = $pdo->prepare("SELECT COUNT(*) FROM decharges_details WHERE id_produit = ?");
$check->execute([$id]);
$usedCount = $check->fetchColumn();

if ($usedCount > 0) {
    // Le produit est lié → afficher un message d’avertissement
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Suppression du produit</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="card shadow-lg border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">⚠️ Suppression impossible directement</h4>
                </div>
                <div class="card-body">
                    <p class="lead">
                        Ce produit est encore utilisé dans <strong><?= $usedCount; ?></strong> décharge(s).
                    </p>
                    <p>
                        Pour éviter toute perte de données, vous pouvez :
                    </p>
                    <ul>
                        <li><strong>Annuler</strong> pour revenir sans rien supprimer.</li>
                        <li><strong>Supprimer tout de même</strong> pour effacer le produit et toutes les entrées associées dans <code>decharges_details</code>.</li>
                    </ul>
                    <div class="mt-4">
                        <a href="table.php" class="btn btn-secondary">
                            ⬅️ Annuler / Retour
                        </a>
                        <a href="supprimer.php?id=<?= $id; ?>&confirm=oui" class="btn btn-danger">
                            🗑️ Supprimer tout de même
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- Si le produit n'est pas utilisé, suppression directe ---
try {
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "✅ Produit supprimé avec succès.";
    header("Location: table.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    header("Location: table.php");
    exit;
}
?>
