// Script PWA - Installation et enregistrement du Service Worker
document.addEventListener('DOMContentLoaded', function() {
    registerServiceWorker();
});

// Enregistrer le Service Worker
function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/inventaire_v2/sw.js')
            .then(registration => {
                console.log('✓ Service Worker enregistré:', registration);
            })
            .catch(error => {
                console.log('✗ Erreur Service Worker:', error);
            });
    }
}

// Gérer l'installation de l'app
let deferredPrompt;

window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredPrompt = event;
    showInstallPrompt();
});

function showInstallPrompt() {
    const existingPrompt = document.getElementById('pwa-install-prompt');
    if (existingPrompt) return;

    const prompt = document.createElement('div');
    prompt.id = 'pwa-install-prompt';
    prompt.innerHTML = `
        <div style="
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        ">
            <div style="margin-bottom: 10px; font-weight: bold; color: #333;">
                📦 Installer Inventaire?
            </div>
            <div style="margin-bottom: 15px; font-size: 0.9em; color: #666;">
                Utilisez comme application de bureau.
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="installApp()" style="
                    background: #667eea;
                    color: white;
                    border: none;
                    padding: 8px 15px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: 500;
                ">Installer</button>
                <button onclick="closePrompt()" style="
                    background: #f0f0f0;
                    color: #333;
                    border: none;
                    padding: 8px 15px;
                    border-radius: 5px;
                    cursor: pointer;
                ">Fermer</button>
            </div>
        </div>
    `;
    document.body.appendChild(prompt);
}

function installApp() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(choiceResult => {
            if (choiceResult.outcome === 'accepted') {
                console.log('✓ App installée');
            }
            deferredPrompt = null;
            closePrompt();
        });
    }
}

function closePrompt() {
    const prompt = document.getElementById('pwa-install-prompt');
    if (prompt) prompt.remove();
}

// Gérer l'installation
window.addEventListener('appinstalled', () => {
    console.log('✓ Application installée avec succès');
    closePrompt();
});
