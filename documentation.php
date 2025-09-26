<?php
session_start();

// Définir ROOT_PATH et INCLUDE_PATH
define('ROOT_PATH', __DIR__ . '/');
define('INCLUDE_PATH', ROOT_PATH . 'includes/');

// Inclure db.php
if (!file_exists(INCLUDE_PATH . 'db.php')) {
    die("Erreur : db.php introuvable dans " . INCLUDE_PATH);
}
include INCLUDE_PATH . 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Charger infos utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, rank FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Inclure header.php
if (!file_exists(INCLUDE_PATH . 'header.php')) {
    die("Erreur : header.php introuvable dans " . INCLUDE_PATH);
}
include INCLUDE_PATH . 'header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation - Inventaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .doc-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1, h2 {
            font-weight: 600;
        }
    </style>
</head>
<body>





<div class="doc-container">
    <h1 class="mb-4 text-center">📖 Documentation</h1>

    <h2>À propos</h2>
    <p>Cette application gère l’inventaire d’une administration, incluant les achats, décharges, et suivi des produits.</p>

    <h2>Fonctionnalités</h2>
    <ul>
        <li>👥 Gestion des utilisateurs (connexion, déconnexion, rôles)</li>
        <li>📦 Gestion des produits, catégories et unités</li>
        <li>🧾 Gestion des achats et détails d’achats</li>
        <li>📑 Gestion des inventaires avec suivi des numéros d’inventaire</li>
        <li>📤 Gestion des décharges (sorties de stock)</li>
    </ul>

    <h2>Utilisation</h2>
    <p>Depuis le <strong>Dashboard</strong>, naviguez vers les modules disponibles via le menu latéral. 
    Vous pouvez ajouter des produits, enregistrer des achats et suivre l’historique.</p>

    <h2>Contacts</h2>
    <p>Pour toute assistance technique, contactez l’administrateur du système.</p>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary">⬅ Retour au Dashboard</a>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('d-none');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
