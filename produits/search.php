<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT p.*, c.nom AS categorie_nom, u.nom AS unite_nom
        FROM produits p
        LEFT JOIN categories c ON p.id_categorie = c.id
        LEFT JOIN unites u ON p.id_unite = u.id
        WHERE p.nom LIKE :q OR p.code LIKE :q OR c.nom LIKE :q
        ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['q' => "%$q%"]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($produits) {
    foreach ($produits as $p) {
        echo "<tr>
            <td>{$p['id']}</td>
            <td>".htmlspecialchars($p['code'])."</td>
            <td>".htmlspecialchars($p['nom'])."</td>
            <td>".htmlspecialchars($p['categorie_nom'])."</td>
            <td>".htmlspecialchars($p['type'])."</td>
            <td>".htmlspecialchars($p['unite_nom'])."</td>
            <td>".htmlspecialchars($p['stock_initial'])."</td>
            <td>
                <a href='modifier.php?id={$p['id']}' class='btn btn-primary btn-sm'><i class='bi bi-pencil'></i></a>
                <a href='supprimer.php?id={$p['id']}' onclick='return confirm(\"Voulez-vous vraiment supprimer ".htmlspecialchars($p['nom'])." ?\");' class='btn btn-danger btn-sm'><i class='bi bi-trash'></i></a>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='8' class='text-center'>Aucun produit trouvé</td></tr>";
}
