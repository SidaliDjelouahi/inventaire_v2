<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// --- Requête SQL ---
$sql = "SELECT 
            inventaire.id,
            inventaire.id_achats_details,
            produits.nom AS produit_nom,
            inventaire.sn,
            CONCAT(
                inventaire.inventaire, 
                '/', 
                IFNULL(categories.code,''), 
                '/', 
                YEAR(achats.date)
            ) AS inventaire_format
        FROM inventaire
        LEFT JOIN achats_details 
            ON inventaire.id_achats_details = achats_details.id
        LEFT JOIN produits 
            ON achats_details.id_produit = produits.id
        LEFT JOIN categories 
            ON produits.id_categorie = categories.id
        LEFT JOIN achats 
            ON achats_details.id_achat = achats.id";

$params = [];

if ($search !== "") {
    $sql .= " WHERE 
        inventaire.id LIKE :search
        OR inventaire.inventaire LIKE :search
        OR inventaire.sn LIKE :search
        OR produits.nom LIKE :search
        OR categories.code LIKE :search
        OR YEAR(achats.date) LIKE :search";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY inventaire.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <title>Inventaire</title>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h3 class="mb-4"><i class="bi bi-clipboard-data me-2"></i>Registre d'Inventaire</h3>

            <!-- Barre de recherche -->
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" id="search" class="form-control" placeholder="Rechercher...">
            </div>


            <!-- Tableau -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Produit</th>
                            <th>Inventaire</th>
                            <th>SN</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resultat">
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['produit_nom'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['inventaire_format'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['sn']) ?></td>
                                    <td>
                                      <a href="details_inventaire.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                                          <i class="bi bi-eye"></i> Détails
                                      </a>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucun enregistrement trouvé</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>


                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    function charger(search='') {
        $.get('ajax_inventaire.php', {search: search}, function(data){
            $('#resultat').html(data);
        });
    }

    // Charger dès l’ouverture
    charger();

    // Recherche en direct
    $('#search').on('keyup', function(){
        let val = $(this).val();
        charger(val);
    });
});
</script>
<script>
$(document).ready(function(){
    // Fonction pour charger les résultats
    function charger(search='') {
        $.get('ajax_inventaire.php', {search: search}, function(data){
            $('#resultat').html(data);
        });
    }

    // Charger dès l’ouverture
    charger();

    // Déclenchement avec délai
    let timer = null;
    $('#search').on('input', function(){
        clearTimeout(timer);
        let val = $(this).val();
        timer = setTimeout(function(){
            charger(val);
        }, 300); // 300 ms après la dernière frappe
    });
});
</script>


</body>
</html>
