<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Vérifier si l'ID est fourni ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . ROOT_URL . "/unites/table.php");
    exit;
}

$id = intval($_GET['id']);

// --- Vérifier que l'unité existe ---
$stmt = $pdo->prepare("SELECT * FROM unites WHERE id = ?");
$stmt->execute([$id]);
$unite = $stmt->fetch();

if (!$unite) {
    // Unité introuvable, redirection vers la liste
    header("Location: " . ROOT_URL . "/unites/table.php");
    exit;
}

// --- Suppression ---
$stmt = $pdo->prepare("DELETE FROM unites WHERE id = ?");
$stmt->execute([$id]);

// Redirection vers la liste après suppression
header("Location: " . ROOT_URL . "/unites/table.php");
exit;
