<?php
// Paramètres de connexion
$host = "localhost";
$dbname = "inventaire_v2";
$username = "root"; // ⚠️ changer si tu as un autre user MySQL
$password = "";     // ⚠️ changer si ton root a un mot de passe

try {
    // Connexion via PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Activer les erreurs en mode Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mode par défaut : fetch associatif
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
