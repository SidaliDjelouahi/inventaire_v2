<?php
// bureaux/supprimer.php
require_once "../includes/db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: table.php");
    exit();
}

$id = intval($_GET['id']);

// Supprimer le bureau
$stmt = $pdo->prepare("DELETE FROM bureaux WHERE id = ?");
$stmt->execute([$id]);

header("Location: table.php");
exit();
