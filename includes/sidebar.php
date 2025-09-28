<?php
// includes/sidebar.php
require_once __DIR__ . "/config.php"; // pour utiliser ROOT_URL

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header("Location: " . ROOT_URL . "/dashboard.php");
    exit();
}
?>

<!-- Colonne Sidebar -->
<div class="col-md-3 col-lg-2 bg-light border-end min-vh-100 p-0">
    <div class="list-group list-group-flush">
        <a href="<?= ROOT_URL ?>/dashboard.php" class="list-group-item list-group-item-action d-flex align-items-center">
            <i class="bi bi-house me-2"></i> Tableau de bord
        </a>

        <!-- Menu Paramètres (Collapse) -->
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
           data-bs-toggle="collapse" href="#collapseParametres" role="button" aria-expanded="false" aria-controls="collapseParametres">
            <span><i class="bi bi-sliders me-2"></i> Paramètres</span>
            <i class="bi bi-caret-down"></i>
        </a>
        <div class="collapse" id="collapseParametres">
            <div class="list-group ms-3">
                <a href="<?= ROOT_URL ?>/categories/table.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-tags me-2"></i> Catégories
                </a>
                <a href="<?= ROOT_URL ?>/unites/table.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-bounding-box me-2"></i> Unités
                </a>
                <a href="<?= ROOT_URL ?>/produits/table.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-box me-2"></i> Produits
                </a>
                <a href="<?= ROOT_URL ?>/services/table.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-gear me-2"></i> Services
                </a>
                <a href="<?= ROOT_URL ?>/bureaux/table.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-building-gear me-2"></i> Bureaux
                </a>
                <a href="<?= ROOT_URL ?>/fournisseurs/table.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-truck me-2"></i> Fournisseurs
                </a>
            </div>
        </div>

        <!-- Menu Achats (Collapse) -->
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
           data-bs-toggle="collapse" href="#collapseAchats" role="button" aria-expanded="false" aria-controls="collapseAchats">
            <span><i class="bi bi-bag-check me-2"></i> Achats</span>
            <i class="bi bi-caret-down"></i>
        </a>
        <div class="collapse" id="collapseAchats">
            <div class="list-group ms-3">
                <a href="<?= ROOT_URL ?>/achats/bon.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-plus-circle me-2"></i> Bon d'achat
                </a>
                <a href="<?= ROOT_URL ?>/achats/historique.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-clock-history me-2"></i> Historique achats
                </a>
            </div>
        </div>

        <!-- Autres menus -->
        <a href="<?= ROOT_URL ?>/documentation.php" class="list-group-item list-group-item-action d-flex align-items-center">
            <i class="bi bi-journal-text me-2"></i> Documentation
        </a>

        <?php if (isset($user['rank']) && $user['rank'] === 'admin'): ?>
        <a href="<?= ROOT_URL ?>/utilisateurs/table.php" class="list-group-item list-group-item-action d-flex align-items-center">
            <i class="bi bi-shield-lock me-2"></i> Gestion utilisateurs
        </a>
        <?php endif; ?>

        <a href="<?= ROOT_URL ?>/profil.php" class="list-group-item list-group-item-action d-flex align-items-center">
            <i class="bi bi-person-circle me-2"></i> Mon profil
        </a>
        <a href="<?= ROOT_URL ?>/logout.php" class="list-group-item list-group-item-action text-danger d-flex align-items-center">
            <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
        </a>
    </div>
</div>
