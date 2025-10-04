<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

$id_decharge = isset($_GET['id_decharge']) ? intval($_GET['id_decharge']) : 0;
$id_detail   = isset($_GET['id_detail']) ? intval($_GET['id_detail']) : 0;

if ($id_decharge <= 0 || $id_detail <= 0) die("Paramètres manquants");

try {
    $pdo->beginTransaction();

    // Récupérer l’id du produit de cette ligne
    $stmt = $pdo->prepare("SELECT id_produit FROM decharges_details WHERE id = ? AND id_decharge = ?");
    $stmt->execute([$id_detail, $id_decharge]);
    $id_produit = $stmt->fetchColumn();

    if (!$id_produit) {
        throw new Exception("Ligne introuvable");
    }

    // Supprimer les états d’inventaire liés à cette décharge et à ce produit
    // (en passant par inventaire.id_achats_details → achats_details.id_produit)
    $sqlEtat = "DELETE ie FROM inventaire_etat ie
                INNER JOIN inventaire i ON ie.id_inventaire = i.id
                INNER JOIN achats_details ad ON i.id_achats_details = ad.id
                WHERE ad.id_produit = :id_produit AND ie.id_action = :id_decharge";
    $stmtEtat = $pdo->prepare($sqlEtat);
    $stmtEtat->execute([
        ':id_produit'  => $id_produit,
        ':id_decharge' => $id_decharge
    ]);

    // Supprimer la ligne du produit dans decharges_details
    $pdo->prepare("DELETE FROM decharges_details WHERE id = ? AND id_decharge = ?")->execute([$id_detail, $id_decharge]);

    $pdo->commit();

    header("Location: details.php?id=" . $id_decharge);
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur suppression : " . $e->getMessage());
}
