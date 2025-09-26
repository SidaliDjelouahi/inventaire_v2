<?php
session_start();

// Détruire toutes les variables de session
$_SESSION = [];

// Détruire la session
session_destroy();

// Supprimer les cookies éventuels (si tu utilises un "remember me")
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}

// Rediriger vers la page de login
header("Location: index.php");
exit();
