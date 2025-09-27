<?php
// fournisseurs/supprimer.php
require_once "../includes/db.php";

// Vérifier si un ID est passé
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: table.php");
    exit();
}

$id = intval($_GET['id']);

// Vérifier si le fournisseur existe
$sql = "SELECT * FROM fournisseurs WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fournisseur) {
    header("Location: table.php?error=notfound");
    exit();
}

// Supprimer le fournisseur
$delete = "DELETE FROM fournisseurs WHERE id = ?";
$stmt = $pdo->prepare($delete);
$stmt->execute([$id]);

header("Location: table.php?success=deleted");
exit();
