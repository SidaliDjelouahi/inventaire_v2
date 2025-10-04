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
            d.id,
            d.num_decharge,
            d.date,
            b.bureau AS bureau_nom
        FROM decharges d
        LEFT JOIN bureaux b ON d.id_bureau = b.id";

$params = [];
if ($search !== "") {
    $sql .= " WHERE 
        d.id LIKE :search
        OR d.num_decharge LIKE :search
        OR b.bureau LIKE :search
        OR DATE_FORMAT(d.date,'%Y-%m-%d %H:%i') LIKE :search";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY d.id DESC";
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
    <title>Historique des décharges</title>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h3 class="mb-4"><i class="bi bi-truck me-2"></i>Historique des décharges</h3>

            <!-- Barre de recherche -->
            <form class="mb-3" method="get" onsubmit="return false;">
                <div class="input-group">
                    <input type="text" id="search" name="search" class="form-control" placeholder="Rechercher...">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                </div>
            </form>

            <!-- Tableau -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Numéro décharge</th>
                            <th>Date</th>
                            <th>Bureau</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resultat">
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['num_decharge']) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['date']))) ?></td>
                                    <td><?= htmlspecialchars($row['bureau_nom'] ?? '') ?></td>
                                    <td>
                                        <a href="details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucune décharge trouvée</td>
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
        $.get('ajax_historique.php', {search: search}, function(data){
            $('#resultat').html(data);
        });
    }
    charger();
    $('#search').on('keyup', function(){
        let val = $(this).val();
        charger(val);
    });
});
</script>
</body>
</html>
