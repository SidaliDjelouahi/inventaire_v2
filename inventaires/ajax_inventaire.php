<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    exit("Non autorisé");
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

$sql = "SELECT 
            inventaire.id,
            inventaire.id_achats_details,
            produits.nom AS produit_nom,
            inventaire.sn,
            CONCAT(
                inventaire.inventaire, 
                '/', 
                IFNULL(categories.code,''), 
                '/', 
                YEAR(achats.date)
            ) AS inventaire_format
        FROM inventaire
        LEFT JOIN achats_details 
            ON inventaire.id_achats_details = achats_details.id
        LEFT JOIN produits 
            ON achats_details.id_produit = produits.id
        LEFT JOIN categories 
            ON produits.id_categorie = categories.id
        LEFT JOIN achats 
            ON achats_details.id_achat = achats.id";

$params = [];
if ($search !== "") {
    $sql .= " WHERE 
        inventaire.id LIKE :search
        OR inventaire.inventaire LIKE :search
        OR inventaire.sn LIKE :search
        OR produits.nom LIKE :search
        OR categories.code LIKE :search
        OR YEAR(achats.date) LIKE :search";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY inventaire.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0): 
    foreach ($rows as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['produit_nom'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['inventaire_format'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['sn']) ?></td>
            <td>
                <a href="details_inventaire.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye"></i> Détails
                </a>
            </td>
        </tr>
    <?php endforeach; 
else: ?>
    <tr><td colspan="5" class="text-center">Aucun enregistrement trouvé</td></tr>
<?php endif; 
