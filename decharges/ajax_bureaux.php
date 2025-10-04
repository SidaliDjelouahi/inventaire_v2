<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT b.id,
               b.bureau,
               s.nom AS service
        FROM bureaux b
        LEFT JOIN services s ON b.id_service = s.id
        WHERE b.bureau LIKE :q OR s.nom LIKE :q
        ORDER BY s.nom, b.bureau";
$stmt = $pdo->prepare($sql);
$stmt->execute([':q' => "%$q%"]);

$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = [
        'id' => $row['id'],
        // on construit directement le libellé service + bureau
        'bureau_service' => $row['service'] . ' – ' . $row['bureau']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
