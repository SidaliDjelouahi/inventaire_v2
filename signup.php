<?php
session_start();
include "includes/db.php"; // connexion à la base

$error = "";
$success = "";

// Si formulaire soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Ce nom d'utilisateur est déjà pris.";
        } else {
            // Hacher le mot de passe
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insérer l'utilisateur
            $stmt = $conn->prepare("INSERT INTO utilisateurs (username, password, rank, created_at) VALUES (?, ?, 'user', NOW())");
            if ($stmt->execute([$username, $hashed_password])) {
                $success = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Erreur lors de la création du compte.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: #f8f9fa;
        }
        .signup-card {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            background: white;
        }
        .logo {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="signup-card">
        <div class="logo">📝 Créer un compte</div>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <div class="text-center">
                <a href="login.php" class="btn btn-success w-100 mt-3">Aller à la page de connexion</a>
            </div>
        <?php else: ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" name="username" id="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php">Déjà un compte ? Se connecter</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
