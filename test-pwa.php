<?php
/**
 * Page de test PWA
 */
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PWA - Inventaire</title>
    <link href="/inventaire_v2/includes/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="/inventaire_v2/includes/bootstrap/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="/inventaire_v2/manifest.json">
    <meta name="theme-color" content="#667eea">
    <style>
        body { padding: 20px; }
        .status { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .status.ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Test PWA - Inventaire v2</h1>
        <hr>
        
        <h3>État de la PWA</h3>
        
        <div class="status ok" id="manifest-status">
            ✅ <strong>Manifest.json</strong> - <a href="/inventaire_v2/manifest.json" target="_blank">/inventaire_v2/manifest.json</a>
        </div>

        <div class="status ok" id="sw-status">
            ✅ <strong>Service Worker</strong> - Enregistrement en cours...
        </div>

        <div class="status ok" id="support-status">
            ✅ <strong>Support PWA</strong> - Vérification...
        </div>

        <hr>

        <h3>📋 Informations Techniques</h3>
        <table class="table table-sm">
            <tr>
                <td><strong>Nom APP:</strong></td>
                <td><code>Inventaire v2</code></td>
            </tr>
            <tr>
                <td><strong>Scope:</strong></td>
                <td><code>/inventaire_v2/</code></td>
            </tr>
            <tr>
                <td><strong>Display:</strong></td>
                <td><code>standalone</code> (Mode Desktop)</td>
            </tr>
            <tr>
                <td><strong>Start URL:</strong></td>
                <td><code>/inventaire_v2/</code></td>
            </tr>
            <tr>
                <td><strong>Service Worker:</strong></td>
                <td><code>/inventaire_v2/sw.js</code></td>
            </tr>
        </table>

        <hr>

        <h3>🎯 Comment Installer?</h3>
        <ol>
            <li>Ouvrir <a href="/inventaire_v2/">http://localhost/inventaire_v2/</a></li>
            <li>Un popup "Installer Inventaire?" devrait apparaître</li>
            <li>Cliquer sur "Installer"</li>
            <li>L'app s'installera comme application de bureau</li>
        </ol>

        <div class="alert alert-info mt-4">
            <strong>💡 Note:</strong> Sur Chrome/Edge/Firefox, vérifiez que le Service Worker est enregistré dans DevTools 
            (<kbd>F12</kbd> → Application → Service Workers).
        </div>

        <hr>
        <p><a href="/inventaire_v2/" class="btn btn-primary">← Retour à l'application</a></p>
    </div>

    <script src="/inventaire_v2/includes/jquery/jquery-3.6.4.min.js"></script>
    <script src="/inventaire_v2/includes/bootstrap/bootstrap.bundle.min.js"></script>
    
    <script>
        // Test du support PWA
        if ('serviceWorker' in navigator) {
            document.getElementById('sw-status').innerHTML = '✅ <strong>Service Worker</strong> - Supporté par le navigateur';
            
            navigator.serviceWorker.getRegistrations().then(registrations => {
                if (registrations.length > 0) {
                    document.getElementById('sw-status').innerHTML = '✅ <strong>Service Worker</strong> - Enregistré avec succès!';
                }
            });
        } else {
            document.getElementById('sw-status').classList.remove('ok');
            document.getElementById('sw-status').classList.add('error');
            document.getElementById('sw-status').innerHTML = '❌ <strong>Service Worker</strong> - Non supporté';
        }

        // Test du support manifest
        const manifest = document.querySelector('link[rel="manifest"]');
        if (manifest) {
            document.getElementById('manifest-status').innerHTML = '✅ <strong>Manifest.json</strong> - Lié correctement';
        }

        // Test du support PWA
        if ('serviceWorker' in navigator || 'storage' in navigator) {
            document.getElementById('support-status').innerHTML = '✅ <strong>Support PWA</strong> - Complètement supporté';
        }
    </script>
</body>
</html>
