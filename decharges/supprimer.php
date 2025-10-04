<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) die("Décharge introuvable");

try {
    $pdo->beginTransaction();

    // Supprimer d’abord tous les états d’inventaire liés à cette décharge
    $pdo->prepare("DELETE FROM inventaire_etat WHERE id_action = ?")->execute([$id]);

    // Supprimer les détails de la décharge (sera supprimé de toute façon par ON DELETE CASCADE, mais on peut forcer)
    $pdo->prepare("DELETE FROM decharges_details WHERE id_decharge = ?")->execute([$id]);

    // Supprimer la décharge
    $pdo->prepare("DELETE FROM decharges WHERE id = ?")->execute([$id]);

    $pdo->commit();

    header("Location: historique.php?msg=supprimé");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur suppression : " . $e->getMessage());
}
