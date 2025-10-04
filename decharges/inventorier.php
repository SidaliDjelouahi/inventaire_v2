<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Vérifier si id_decharge est passé
$id_decharge = isset($_GET['id_decharge']) ? intval($_GET['id_decharge']) : 0;
if ($id_decharge <= 0) {
    die("Décharge introuvable");
}

// --- Récupérer les infos principales de la décharge ---
$stmt = $pdo->prepare("SELECT d.*, b.bureau 
                       FROM decharges d
                       LEFT JOIN bureaux b ON d.id_bureau = b.id
                       WHERE d.id = ?");
$stmt->execute([$id_decharge]);
$decharge = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$decharge) {
    die("Décharge introuvable");
}

// --- Suppression d’une ligne ---
if (isset($_GET['supprimer_detail'])) {
    $id_detail = intval($_GET['supprimer_detail']);
    $pdo->prepare("DELETE FROM decharges_details WHERE id = ? AND id_decharge = ?")
        ->execute([$id_detail, $id_decharge]);
    header("Location: details.php?id_decharge=".$id_decharge);
    exit();
}

// --- Suppression globale de la décharge ---
if (isset($_GET['supprimer_decharge']) && $_GET['supprimer_decharge'] == '1') {
    // Supprimer la décharge (les détails sont supprimés automatiquement via ON DELETE CASCADE)
    $pdo->prepare("DELETE FROM decharges WHERE id = ?")->execute([$id_decharge]);
    header("Location: liste.php"); // redirige vers la liste des décharges
    exit();
}

// --- Récupérer les détails ---
$sql = "SELECT dd.id, dd.quantite, 
               p.nom AS produit_nom, p.code
        FROM decharges_details dd
        LEFT JOIN produits p ON dd.id_produit = p.id
        WHERE dd.id_decharge = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_decharge]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails Décharge <?php echo htmlspecialchars($decharge['num_decharge']); ?></title>
    <link rel="stylesheet" href="../assets/bootstrap.min.css">
</head>
<body class="p-3">
<div class="container">
    <h2>Détails de la décharge <?php echo htmlspecialchars($decharge['num_decharge']); ?></h2>
    <p><strong>Date :</strong> <?php echo htmlspecialchars($decharge['date']); ?><br>
       <strong>Bureau :</strong> <?php echo htmlspecialchars($decharge['bureau']); ?></p>

    <div class="mb-3">
        <a href="imprimer.php?id_decharge=<?php echo $id_decharge; ?>" target="_blank" class="btn btn-secondary">Imprimer</a>
        <a href="details.php?id_decharge=<?php echo $id_decharge; ?>&supprimer_decharge=1" 
           class="btn btn-danger" onclick="return confirm('Supprimer entièrement cette décharge ?')">Supprimer la décharge</a>
        <a href="liste.php" class="btn btn-light">Retour à la liste</a>
    </div>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Code</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($details): ?>
            <?php foreach ($details as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['code']); ?></td>
                    <td><?php echo htmlspecialchars($row['produit_nom']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantite']); ?></td>
                    <td>
                        <a href="inventorier.php?id_detail=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Inventorier</a>
                        <a href="details.php?id_decharge=<?php echo $id_decharge; ?>&supprimer_detail=<?php echo $row['id']; ?>" 
                           class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet article ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center">Aucun produit dans cette décharge</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
