<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Vérifier la connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// Vérifier et sécuriser l'ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID invalide");
}

try {
    // 🔎 Vérifier si l'inventaire est utilisé dans la table inventaire_etat
    $check = $pdo->prepare("SELECT COUNT(*) FROM inventaire_etat WHERE id_inventaire = ?");
    $check->execute([$id]);
    $existe = $check->fetchColumn();

    if ($existe > 0) {
        // ❌ Annuler la suppression si des enregistrements liés existent
        echo "<div style='
                margin: 40px auto;
                max-width: 600px;
                background: #fff3cd;
                border: 1px solid #ffeeba;
                color: #856404;
                padding: 20px;
                border-radius: 8px;
                font-family: sans-serif;
                text-align: center;
            '>
                <h3>Suppression annulée ❗</h3>
                <p>L’inventaire (ID : <strong>$id</strong>) ne peut pas être supprimé car des actions lui sont encore liées dans la table <strong>inventaire_etat</strong>.</p>
                <p>Veuillez d’abord supprimer ces enregistrements avant de continuer.</p>
                <a href='table.php' style='
                    display: inline-block;
                    margin-top: 15px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    padding: 8px 15px;
                    border-radius: 5px;
                '>⬅ Retour</a>
            </div>";
        exit();
    }

    // ✅ Si aucun lien trouvé, supprimer l'inventaire
    $stmt = $pdo->prepare("DELETE FROM inventaire WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: table.php?msg=deleted");
    exit();

} catch (PDOException $e) {
    die("Erreur lors de la suppression : " . $e->getMessage());
}
?>




