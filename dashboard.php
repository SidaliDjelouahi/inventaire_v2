<?php
session_start();

define('ROOT_PATH', __DIR__ . '/');
define('INCLUDE_PATH', ROOT_PATH . 'includes/');

if (!file_exists(INCLUDE_PATH . 'db.php')) {
    die("Erreur : db.php introuvable dans " . INCLUDE_PATH);
}
include INCLUDE_PATH . 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($pdo)) {
    die("Erreur : la connexion à la base de données a échoué !");
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, rank FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Bouton flottant (toujours visible) */
        #menuButton {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
            border-radius: 50%;
            width: 55px;
            height: 55px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            font-size: 1.5rem;
        }
        /* Sidebar */
        #sidebarMenu {
            width: 260px;
        }
    </style>
</head>
<body class="bg-light">

<!-- ✅ Navbar toujours visible sur desktop -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand ms-auto fw-bold">Inventaire</span>
        <div class="ms-auto d-flex align-items-center">
            <a href="documentation.php" class="nav-link text-white me-3">Documentation</a>
            <span class="text-white me-3"><?= htmlspecialchars($user['username']); ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
        </div>
    </div>
</nav>

<!-- ✅ Bouton flottant visible partout -->
<button id="menuButton" class="btn btn-primary">
    ☰
</button>

<!-- ✅ Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <?php include INCLUDE_PATH . 'sidebar.php'; ?>
    </div>
</div>

<!-- ✅ Contenu -->
<div class="container text-center mt-5">
    <h1 class="display-5 fw-bold">Bienvenue sur Inventaire</h1>
    <p class="lead">Utilisez le menu flottant ou la barre de navigation pour accéder aux modules.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('menuButton').addEventListener('click', function () {
        var sidebar = new bootstrap.Offcanvas(document.getElementById('sidebarMenu'));
        sidebar.show();
    });
</script>
</body>
</html>
