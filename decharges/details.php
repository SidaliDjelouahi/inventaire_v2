<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// ID de la décharge
$id_decharge = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_decharge <= 0) {
    die("Décharge introuvable");
}

// --- Récupération info décharge ---
$sql = "SELECT d.id, d.num_decharge, d.date, b.bureau AS bureau_nom
        FROM decharges d
        LEFT JOIN bureaux b ON d.id_bureau = b.id
        WHERE d.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_decharge]);
$decharge = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$decharge) {
    die("Décharge introuvable");
}

// --- Récupération détails produits ---
$sql_details = "SELECT dd.id, p.nom AS produit_nom, dd.quantite
                FROM decharges_details dd
                LEFT JOIN produits p ON dd.id_produit = p.id
                WHERE dd.id_decharge = :id";
$stmt_details = $pdo->prepare($sql_details);
$stmt_details->execute([':id' => $id_decharge]);
$details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <title>Détails décharge</title>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h3 class="mb-4"><i class="bi bi-truck me-2"></i>Détails décharge</h3>

            <!-- Infos générales -->
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>ID :</strong> <?= htmlspecialchars($decharge['id']) ?></p>
                    <p><strong>Numéro de décharge :</strong> <?= htmlspecialchars($decharge['num_decharge']) ?></p>
                    <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($decharge['date']))) ?></p>
                    <p><strong>Bureau :</strong> <?= htmlspecialchars($decharge['bureau_nom'] ?? '') ?></p>
                </div>
            </div>

            <!-- Produits -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Affecter inventaire</th> <!-- nouvelle colonne -->
                        </tr>
                    </thead>
                <tbody>
                <?php if (count($details) > 0): ?>
                    <?php foreach ($details as $row): ?>
                        <?php
                        // on compte combien d’inventaires déjà affectés
                        $sql_count = "SELECT COUNT(*) FROM inventaire_etat ie
                                      INNER JOIN inventaire i ON ie.id_inventaire = i.id
                                      INNER JOIN achats_details ad ON i.id_achats_details = ad.id
                                      WHERE ad.id_produit = (
                                          SELECT id_produit FROM decharges_details WHERE id=:id_detail
                                      )
                                      AND ie.id_action = :id_decharge";
                        $stmt_count = $pdo->prepare($sql_count);
                        $stmt_count->execute([
                            ':id_detail'   => $row['id'],
                            ':id_decharge' => $id_decharge
                        ]);
                        $nb_inventories = (int)$stmt_count->fetchColumn();

                        $quantite = (int)$row['quantite'];
                        $dejaInventorie = $nb_inventories >= $quantite;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['produit_nom']) ?></td>
                            <td><?= htmlspecialchars($quantite) ?></td>
                            <td>
                                <?php if ($dejaInventorie): ?>
                                    <a href="voir_inventaire.php?id_decharge=<?= urlencode($id_decharge) ?>&id_detail=<?= urlencode($row['id']) ?>"
                                       class="btn btn-sm btn-info">
                                       <i class="bi bi-eye"></i> Voir inventaire
                                    </a>
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-check2-circle"></i> Déjà inventorié
                                    </button>
                                <?php else: ?>
                                    <a href="affecter_inventaire.php?id_decharge=<?= urlencode($id_decharge) ?>&id_detail=<?= urlencode($row['id']) ?>"
                                       class="btn btn-sm btn-primary">
                                       <i class="bi bi-box-arrow-in-down"></i> Affecter inventaire
                                    </a>
                                <?php endif; ?>

                                <!-- Bouton supprimer produit -->
                                <a href="supprimer_produit.php?id_decharge=<?= urlencode($id_decharge) ?>&id_detail=<?= urlencode($row['id']) ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Supprimer ce produit de la décharge ?');">
                                   <i class="bi bi-trash"></i>
                                </a>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Aucun produit dans cette décharge</td>
                    </tr>
                <?php endif; ?>
                </tbody>

                </table>
            </div>

            <a href="supprimer.php?id=<?= urlencode($id_decharge) ?>"
               class="btn btn-danger mt-3"
               onclick="return confirm('Voulez-vous vraiment supprimer cette décharge ? Cette action est irréversible.');">
               <i class="bi bi-trash"></i> Supprimer la décharge
            </a>

            <a href="historique.php" class="btn btn-secondary mt-3">
                <i class="bi bi-arrow-left"></i> Retour à l'historique
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
