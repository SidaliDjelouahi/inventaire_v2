<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = $_GET['q'] ?? '';
$stmt = $pdo->prepare("SELECT id, nom FROM produits WHERE nom LIKE ? ORDER BY nom LIMIT 10");
$stmt->execute(["%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
