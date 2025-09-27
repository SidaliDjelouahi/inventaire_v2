<?php
session_start();
require_once "includes/config.php";
require_once "includes/db.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les infos utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, rank FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Inclure header
require_once "includes/header.php";

// Inclure sidebar
require_once "includes/sidebar.php";
?>

<!-- Contenu principal -->
<div class="col-md-9 col-lg-10 p-4">
    <h1 class="display-5 fw-bold">Bienvenue sur Inventaire</h1>
    <p class="lead">Utilisez le menu à gauche pour accéder aux modules.</p>
</div>

<?php
// Inclure footer
require_once "includes/footer.php";
?>
