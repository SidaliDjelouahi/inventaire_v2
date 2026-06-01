<?php
/**
 * Page de diagnostic - Vérifier l'état du projet
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Inventaire v2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .ok { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic Inventaire v2</h1>
        <hr>
        
        <h3>1. Configuration PHP</h3>
        <ul>
            <li>Version PHP: <?php echo phpversion(); ?></li>
            <li>Extension PDO: <span class="<?php echo extension_loaded('pdo') ? 'ok' : 'error'; ?>">
                <?php echo extension_loaded('pdo') ? '✓ Présente' : '✗ Manquante'; ?>
            </span></li>
            <li>Extension PDO MySQL: <span class="<?php echo extension_loaded('pdo_mysql') ? 'ok' : 'error'; ?>">
                <?php echo extension_loaded('pdo_mysql') ? '✓ Présente' : '✗ Manquante'; ?>
            </span></li>
        </ul>

        <h3>2. Fichiers Essentiels</h3>
        <ul>
            <li>includes/config.php: <span class="<?php echo file_exists('includes/config.php') ? 'ok' : 'error'; ?>">
                <?php echo file_exists('includes/config.php') ? '✓ Existe' : '✗ Manquant'; ?>
            </span></li>
            <li>includes/db.php: <span class="<?php echo file_exists('includes/db.php') ? 'ok' : 'error'; ?>">
                <?php echo file_exists('includes/db.php') ? '✓ Existe' : '✗ Manquant'; ?>
            </span></li>
            <li>includes/header.php: <span class="<?php echo file_exists('includes/header.php') ? 'ok' : 'error'; ?>">
                <?php echo file_exists('includes/header.php') ? '✓ Existe' : '✗ Manquant'; ?>
            </span></li>
        </ul>

        <h3>3. Connexion à la Base de Données</h3>
        <?php
        try {
            include "includes/db.php";
            echo '<span class="ok">✓ Connexion réussie</span>';
            
            // Teste une requête simple
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<br>Utilisateurs en base: ' . $result['count'];
        } catch (Exception $e) {
            echo '<span class="error">✗ Erreur: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
        ?>

        <h3>4. Sessions</h3>
        <ul>
            <li>Session démarrée: <span class="ok">✓ Oui</span></li>
            <li>Session ID: <?php echo session_id(); ?></li>
            <li>User connecté: <span class="<?php echo isset($_SESSION['user_id']) ? 'ok' : 'warning'; ?>">
                <?php echo isset($_SESSION['user_id']) ? '✓ Oui' : '⚠ Non'; ?>
            </span></li>
        </ul>

        <hr>
        <p><a href="index.php" class="btn btn-primary">← Retour à la connexion</a></p>
    </div>
</body>
</html>
