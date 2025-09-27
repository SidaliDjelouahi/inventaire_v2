<?php
// includes/header.php
require_once __DIR__ . "/config.php"; // on inclut la config
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaire v2</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Ton CSS perso si nécessaire -->
    <link rel="stylesheet" href="<?php echo ROOT_URL; ?>/assets/css/style.css">
</head>
<body>

<header class="bg-dark text-white p-3 mb-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Inventaire v2</h2>
        <nav>
            <!-- lien accueil dynamique -->
            <a href="<?php echo ROOT_URL; ?>/dashboard.php" class="text-white me-3">Accueil</a>
        </nav>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
