<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// --- Paramètres ---
$id_decharge = isset($_GET['id_decharge']) ? intval($_GET['id_decharge']) : 0;
$id_detail   = isset($_GET['id_detail']) ? intval($_GET['id_detail']) : 0;

if ($id_decharge <= 0 || $id_detail <= 0) {
    die("Paramètres invalides");
}

// --- on récupère le produit concerné par cette ligne de décharge ---
$stmt = $pdo->prepare("SELECT id_produit, quantite FROM decharges_details WHERE id=:id_detail AND id_decharge=:id_decharge");
$stmt->execute([':id_detail' => $id_detail, ':id_decharge' => $id_decharge]);
$detail = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$detail) {
    die("Ligne de décharge introuvable");
}
$id_produit = $detail['id_produit'];
$quantite_total = (int)$detail['quantite'];

// --- combien déjà affectés pour ce détail ---
$stmt = $pdo->prepare("SELECT COUNT(*) FROM inventaire_etat WHERE etat='decharge' AND id_action=:id_decharge");
$stmt->execute([':id_decharge'=>$id_decharge]);
$deja_affectes = (int)$stmt->fetchColumn();

$reste = max($quantite_total - $deja_affectes, 0);

// --- Traitement AJAX d'affectation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_inventaire'])) {
    // on vérifie que l'on n'a pas dépassé
    if ($reste <= 0) {
        echo json_encode(['status' => 'limit']);
        exit;
    }

    $id_inventaire = intval($_POST['id_inventaire']);
    $etat = 'decharge';

    $sql = "INSERT INTO inventaire_etat (id_inventaire, etat, id_action)
            VALUES (:id_inventaire, :etat, :id_action)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_inventaire' => $id_inventaire,
        ':etat' => $etat,
        ':id_action' => $id_decharge // on met id_decharge comme action
    ]);

    echo json_encode(['status' => 'ok']);
    exit;
}

// --- Moteur de recherche ---
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// --- On récupère les inventaires de ce produit ---
$sql = "SELECT i.id AS id_inventaire,
               i.inventaire AS num_inventaire,
               i.sn AS sn,
               p.nom AS produit_nom
        FROM inventaire i
        INNER JOIN achats_details ad ON i.id_achats_details = ad.id
        INNER JOIN produits p ON ad.id_produit = p.id
        WHERE ad.id_produit = :id_produit
          AND NOT EXISTS (
              SELECT 1 FROM inventaire_etat ie
              WHERE ie.id_inventaire = i.id
          )";

if ($search !== '') {
    $sql .= " AND (p.nom LIKE :search OR i.inventaire LIKE :search OR i.sn LIKE :search)";
}

$sql .= " ORDER BY i.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_produit', $id_produit, PDO::PARAM_INT);
if ($search !== '') {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$inventaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<title>Affecter inventaire</title>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h3 class="mb-4"><i class="bi bi-box-arrow-in-down me-2"></i>Affecter inventaire à la décharge</h3>

            <p><strong>Produit concerné :</strong> <?= htmlspecialchars($inventaires[0]['produit_nom'] ?? '') ?></p>
            <p><strong>Quantité totale :</strong> <?= $quantite_total ?> | <strong>Déjà affectés :</strong> <?= $deja_affectes ?> | <strong>Restant :</strong> <?= $reste ?></p>

            <!-- Tableau des numéros sélectionnés -->
            <div class="mb-3">
                <h5>Numéros sélectionnés :</h5>
                <ul id="selected-list" class="list-group"></ul>
            </div>

            <!-- Formulaire de recherche -->
            <form method="get" class="mb-3">
                <input type="hidden" name="id_decharge" value="<?= htmlspecialchars($id_decharge) ?>">
                <input type="hidden" name="id_detail" value="<?= htmlspecialchars($id_detail) ?>">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par inventaire ou SN" value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Rechercher</button>
                </div>
            </form>

            <!-- Tableau inventaire -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Inventaire</th>
                            <th>SN</th>
                            <th>Affecter</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($inventaires) > 0): ?>
                            <?php foreach ($inventaires as $row): ?>
                                <tr id="row-<?= $row['id_inventaire'] ?>">
                                    <td><?= htmlspecialchars($row['id_inventaire']) ?></td>
                                    <td><?= htmlspecialchars($row['num_inventaire']) ?></td>
                                    <td><?= htmlspecialchars($row['sn']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-select"
                                            data-id="<?= $row['id_inventaire'] ?>"
                                            data-num="<?= htmlspecialchars($row['num_inventaire']) ?>">
                                            <i class="bi bi-check2-circle"></i> Sélectionner
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Aucun inventaire trouvé pour ce produit</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <a href="details.php?id=<?= htmlspecialchars($id_decharge) ?>" class="btn btn-secondary mt-3">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    let selectedCount = 0;
    let limit = <?= $reste ?>;

    $('.btn-select').click(function(){
        var btn = $(this);
        if(selectedCount >= limit){
            alert("Vous avez atteint la limite de "+limit+" sélection(s).");
            return;
        }

        var idInventaire = btn.data('id');
        var numInventaire = btn.data('num');

        $.post('affecter_inventaire.php?id_decharge=<?= $id_decharge ?>&id_detail=<?= $id_detail ?>',
        { id_inventaire: idInventaire }, function(resp){
            try {
                var data = JSON.parse(resp);
                if(data.status==='ok'){
                    selectedCount++;
                    btn.removeClass('btn-primary')
                       .addClass('btn-success')
                       .prop('disabled', true)
                       .text('Affecté');

                    $('#selected-list').append('<li class="list-group-item">'+numInventaire+'</li>');
                }else if(data.status==='limit'){
                    alert("Limite atteinte");
                }
            } catch(e){
                alert('Erreur serveur');
            }
        });
    });
});
</script>
</body>
</html>
