<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// Vérifier l'ID du bon
$id_achat = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_achat <= 0) {
    die("Bon d'achat introuvable");
}

try {
    // Récupérer tous les id_achats_details liés à ce bon
    $sql = "SELECT id FROM achats_details WHERE id_achat = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_achat]);
    $idsDetails = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($idsDetails)) {
        header("Location: details.php?id=" . $id_achat);
        exit();
    }

    // Supprimer les inventaires liés à ces détails
    $placeholders = implode(',', array_fill(0, count($idsDetails), '?'));
    $sqlDelete = "DELETE FROM inventaire WHERE id_achats_details IN ($placeholders)";
    $stmt = $pdo->prepare($sqlDelete);
    $stmt->execute($idsDetails);

    // Rediriger avec message
    $_SESSION['message'] = "✅ Tous les inventaires liés à ce bon ont été supprimés.";
    header("Location: details.php?id=" . $id_achat);
    exit();

} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
