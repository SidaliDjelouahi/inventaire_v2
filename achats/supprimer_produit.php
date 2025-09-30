<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// ID du détail à supprimer
$id_detail = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_detail <= 0) {
    die("Produit introuvable");
}

// Récupérer l'id_achat avant suppression
$stmt = $pdo->prepare("SELECT id_achat FROM achats_details WHERE id=?");
$stmt->execute([$id_detail]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    die("Détail introuvable");
}
$id_achat = $row['id_achat'];

// Supprimer les inventaires liés
$pdo->prepare("DELETE FROM inventaire WHERE id_achats_details=?")
    ->execute([$id_detail]);

// Supprimer la ligne d'achat
$pdo->prepare("DELETE FROM achats_details WHERE id=?")
    ->execute([$id_detail]);

// Rediriger vers la page du bon
header("Location: details.php?id=".$id_achat);
exit;
