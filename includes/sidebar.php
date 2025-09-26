<?php
// includes/sidebar.php

// --- Si tu veux ajouter une sécurité pour que sidebar ne soit pas ouvert directement ---
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header("Location: ../dashboard.php");
    exit();
}
?>

<div class="list-group">
    <a href="dashboard.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-house me-2"></i> Tableau de bord
    </a>

    <!-- Produits -->
    <a href="produits/liste.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-box me-2"></i> Produits
    </a>

    <!-- Achats -->
    <a href="achats/liste.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-bag-check me-2"></i> Achats
    </a>

    <!-- Bons de Livraison -->
    <a href="bons_livraison/liste.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-truck me-2"></i> Bons de livraison
    </a>

    <!-- Clients -->
    <a href="clients/liste.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-people me-2"></i> Clients
    </a>

    <!-- Fournisseurs -->
    <a href="fournisseurs/liste.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-building me-2"></i> Fournisseurs
    </a>

    <!-- Mouvements Caisse -->
    <a href="mouvement_caisse/liste.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-cash-stack me-2"></i> Mouvements de caisse
    </a>

    <!-- Historique Global -->
    <a href="ventes/historique.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-clock-history me-2"></i> Historique global
    </a>

    <!-- Utilisateurs (admin uniquement) -->
    <?php if (isset($user['rank']) && $user['rank'] === 'admin'): ?>
    <a href="utilisateurs/liste.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-shield-lock me-2"></i> Gestion utilisateurs
    </a>
    <?php endif; ?>

    <!-- Documentation -->
    <a href="documentation.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-journal-text me-2"></i> Documentation
    </a>

    <!-- Mon Profil -->
    <a href="profil.php" class="list-group-item list-group-item-action d-flex align-items-center">
        <i class="bi bi-person-circle me-2"></i> Mon profil
    </a>

    <!-- Déconnexion -->
    <a href="logout.php" class="list-group-item list-group-item-action text-danger d-flex align-items-center">
        <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
    </a>
</div>
