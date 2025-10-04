<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// terme recherché
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT id, nom, stock_initial FROM produits 
        WHERE nom LIKE :q 
        ORDER BY nom ASC 
        LIMIT 20";
$stmt = $pdo->prepare($sql);
$stmt->execute([':q' => "%$q%"]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($produits as $p) {
    $id_produit = $p['id'];

    // Total achats (entrées)
    $sqlAchats = "SELECT IFNULL(SUM(ad.quantite),0) AS qte_achats 
                  FROM achats_details ad
                  INNER JOIN achats a ON a.id = ad.id_achat
                  WHERE ad.id_produit = ?";
    $stmtAch = $pdo->prepare($sqlAchats);
    $stmtAch->execute([$id_produit]);
    $qte_achats = $stmtAch->fetchColumn();

    // Total décharges (sorties)
    $sqlDech = "SELECT IFNULL(SUM(dd.quantite),0) AS qte_decharges 
                FROM decharges_details dd
                INNER JOIN decharges d ON d.id = dd.id_decharge
                WHERE dd.id_produit = ?";
    $stmtDech = $pdo->prepare($sqlDech);
    $stmtDech->execute([$id_produit]);
    $qte_decharges = $stmtDech->fetchColumn();

    // Calcul stock actuel
    $stock_actuel = $p['stock_initial'] + $qte_achats - $qte_decharges;

    $result[] = [
        'id' => $id_produit,
        'nom' => $p['nom'],
        'stock' => $stock_actuel
    ];
}

header('Content-Type: application/json');
echo json_encode($result);
