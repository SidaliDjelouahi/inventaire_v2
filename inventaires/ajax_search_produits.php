<?php
require_once __DIR__ . '/../includes/db.php';

$term = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($term !== '') {
    $stmt = $pdo->prepare("SELECT id, nom FROM produits WHERE nom LIKE ? ORDER BY nom LIMIT 10");
    $stmt->execute(["%$term%"]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($produits);
}
