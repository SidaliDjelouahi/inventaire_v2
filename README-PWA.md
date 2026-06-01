# PWA - Inventaire v2

## ✅ Configuration PWA Minimaliste

La PWA a été configurée pour fonctionner en mode **desktop standalone** sans nécessité d'Internet.

## 📁 Fichiers Créés

### 1. **manifest.json** (Racine)
Configuration PWA avec:
- Nom et icônes de l'app
- Mode `standalone` (affichage en application de bureau)
- Thème couleur `#667eea`
- Catégories: productivity, business

### 2. **sw.js** (Racine - Service Worker)
Service Worker minimaliste qui:
- S'enregistre au démarrage
- Gère les requêtes en mode "Network First"
- Supporte le mode offline basique

### 3. **pwa-install.js** (Racine)
Script d'installation qui:
- Enregistre le Service Worker
- Affiche un prompt d'installation (popup bas-droite)
- Gère l'installation comme app de bureau

## 🔧 Modifications des Fichiers

### includes/header.php
```php
<!-- PWA Manifest -->
<link rel="manifest" href="<?php echo ROOT_URL; ?>/manifest.json">

<!-- PWA Meta Tags -->
<meta name="theme-color" content="#667eea">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Inventaire">
```

### includes/footer.php
```php
<!-- PWA Installation Script -->
<script src="/inventaire_v2/pwa-install.js"></script>
```

### index.php
- Même configuration que header.php

## 🎯 Comment Utiliser

### Installation sur Desktop

1. Accéder à `http://localhost/inventaire_v2/`
2. Un popup "📦 Installer Inventaire?" apparaît bas-droite
3. Cliquer sur "Installer"
4. L'app s'installe comme application de bureau
5. Accessible via le menu Démarrer (Windows) ou Launchpad (macOS)

### Lancer l'app installée

- **Windows**: Menu Démarrer → Inventaire ou raccourci Bureau
- **macOS**: Launchpad ou Spotlight → Inventaire
- **Linux**: Menu Applications → Inventaire

## 📱 Mode Affichage

La PWA est configurée en mode **`standalone`**:
- Pas de barre d'adresse
- Pas de boutons du navigateur
- Apparence comme une vraie app desktop
- Fenêtre entière dédiée

## 🔐 Sécurité & HTTPS

⚠️ **Important**: Sur un serveur distant, HTTPS est requis pour les PWA.
En local (localhost), HTTP fonctionne.

## 🧪 Test

Page de test: `http://localhost/inventaire_v2/test-pwa.php`

Vérifier:
- ✅ Manifest.json disponible
- ✅ Service Worker enregistré
- ✅ Support PWA du navigateur

## 📊 Fonctionnalités Incluses

- ✅ Installation comme app desktop
- ✅ Icône personnalisée (logo "I")
- ✅ Thème couleur personnalisé
- ✅ Service Worker pour gestion offline
- ✅ Manifest complet (iOS, Android compatible)

## ❌ Non Inclus (Volontairement)

- Cache complexe (Network First)
- Push notifications
- Sync en arrière-plan
- Icônes PNG (SVG utilisé à la place)

## 🛠️ Personnalisation Future

Pour ajouter des icônes PNG au lieu de SVG:
1. Générer les icônes (192x192, 512x512)
2. Placer dans `assets/icons/`
3. Mettre à jour les URLs dans `manifest.json`

## 📝 Notes

- La PWA nécessite que le Service Worker soit enregistré
- Le prompt d'installation s'affiche si la PWA remplit les critères
- L'app fonctionne avec ou sans Internet (dépend de la configuration offline)
