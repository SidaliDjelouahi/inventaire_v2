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

// --- Suppression ---
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

header("Location: table.php");
exit;
?>
