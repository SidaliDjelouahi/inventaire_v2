<?php
/**
 * Page de test - Icônes Bootstrap en local
 */
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Icônes</title>
    <link href="/inventaire_v2/includes/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="/inventaire_v2/includes/bootstrap/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 15px;
        }
        .icon-item {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .icon-item:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
        }
        .icon-item i {
            font-size: 2rem;
            display: block;
            margin-bottom: 10px;
            color: #007bff;
        }
        .icon-item span {
            font-size: 0.85rem;
            color: #666;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Test Icônes Bootstrap - Mode Local</h1>
        <hr>
        
        <h3>Statut des Ressources</h3>
        <ul class="list-group mb-4">
            <li class="list-group-item">
                ✅ Bootstrap CSS: <code>/inventaire_v2/includes/bootstrap/bootstrap.min.css</code>
            </li>
            <li class="list-group-item">
                ✅ Bootstrap Icons CSS: <code>/inventaire_v2/includes/bootstrap/bootstrap-icons.css</code>
            </li>
            <li class="list-group-item">
                ✅ Police WOFF2: <code>/inventaire_v2/includes/bootstrap/fonts/bootstrap-icons.woff2</code>
            </li>
            <li class="list-group-item">
                ✅ Police WOFF: <code>/inventaire_v2/includes/bootstrap/fonts/bootstrap-icons.woff</code>
            </li>
        </ul>

        <h3>Icônes Courantes Testées</h3>
        <div class="icon-grid">
            <div class="icon-item">
                <i class="bi bi-house-fill"></i>
                <span>house-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-gear-fill"></i>
                <span>gear-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-cart-fill"></i>
                <span>cart-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-box-fill"></i>
                <span>box-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-archive-fill"></i>
                <span>archive-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-trash-fill"></i>
                <span>trash-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-pencil-fill"></i>
                <span>pencil-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-plus-circle-fill"></i>
                <span>plus-circle-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-search"></i>
                <span>search</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-download"></i>
                <span>download</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-file-pdf-fill"></i>
                <span>file-pdf-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-printer-fill"></i>
                <span>printer-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-door-closed"></i>
                <span>door-closed</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-person-fill"></i>
                <span>person-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-shield-lock-fill"></i>
                <span>shield-lock-fill</span>
            </div>
            <div class="icon-item">
                <i class="bi bi-question-circle-fill"></i>
                <span>question-circle-fill</span>
            </div>
        </div>

        <hr>
        <p><a href="dashboard.php" class="btn btn-primary">← Retour au Dashboard</a></p>
    </div>

    <script src="/inventaire_v2/includes/jquery/jquery-3.6.4.min.js"></script>
    <script src="/inventaire_v2/includes/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
