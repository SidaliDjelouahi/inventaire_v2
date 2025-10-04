<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../default.php");
    exit();
}

// --- Liste produits pour le select ---
$stmtProd = $pdo->query("SELECT id, nom FROM produits ORDER BY nom");
$produits = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

// --- Produit sélectionné ---
$id_produit = isset($_GET['id_produit']) ? intval($_GET['id_produit']) : 0;
$mouvements = [];
$balance = 0;
$stock_initial = 0;
$nom_produit = "";

if ($id_produit > 0) {
    // --- Infos produit ---
    $stmtInfo = $pdo->prepare("SELECT nom, stock_initial FROM produits WHERE id=?");
    $stmtInfo->execute([$id_produit]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
    if ($info) {
        $nom_produit = $info['nom'];
        $stock_initial = (float)$info['stock_initial'];
    }

    // --- Achats qui augmentent le stock ---
    $sqlAchats = "SELECT a.date, ad.quantite, 'achat' AS type_action, NULL AS bureau, a.id AS id_action
                  FROM achats_details ad
                  INNER JOIN achats a ON ad.id_achat = a.id
                  WHERE ad.id_produit=?";
    $stmtAchats = $pdo->prepare($sqlAchats);
    $stmtAchats->execute([$id_produit]);
    $achats = $stmtAchats->fetchAll(PDO::FETCH_ASSOC);

    // --- Décharges qui diminuent le stock ---
    $sqlDecharges = "SELECT d.date, dd.quantite, 'decharge' AS type_action, b.bureau, d.id AS id_action
                     FROM decharges_details dd
                     INNER JOIN decharges d ON dd.id_decharge = d.id
                     LEFT JOIN bureaux b ON d.id_bureau=b.id
                     WHERE dd.id_produit=?";
    $stmtDech = $pdo->prepare($sqlDecharges);
    $stmtDech->execute([$id_produit]);
    $decharges = $stmtDech->fetchAll(PDO::FETCH_ASSOC);

    // --- Construire tableau des mouvements ---
    // Stock initial
    $mouvements[] = [
        'date' => null,
        'type_action' => 'stock_initial',
        'quantite' => $stock_initial,
        'bureau' => null,
        'id_action' => null
    ];
    // Achats
    foreach ($achats as $a) {
        $mouvements[] = [
            'date' => $a['date'],
            'type_action' => 'achat',
            'quantite' => $a['quantite'],
            'bureau' => $a['bureau'],
            'id_action' => $a['id_action']
        ];
    }
    // Décharges
    foreach ($decharges as $d) {
        $mouvements[] = [
            'date' => $d['date'],
            'type_action' => 'decharge',
            'quantite' => $d['quantite'],
            'bureau' => $d['bureau'],
            'id_action' => $d['id_action']
        ];
    }

    // --- Trier par date (stock initial reste premier) ---
    usort($mouvements, function($a, $b){
        if ($a['type_action']==='stock_initial') return -1;
        if ($b['type_action']==='stock_initial') return 1;
        return strtotime($a['date']) <=> strtotime($b['date']);
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <title>Fiche Inventaire</title>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 p-4">
            <h3 class="mb-4"><i class="bi bi-box-seam me-2"></i>Fiche Inventaire</h3>

            <!-- Formulaire recherche produit -->
            <form method="get" class="row g-3 mb-3">
                <div class="col-md-6 position-relative">
                    <label class="form-label">Produit</label>
                    <input type="text" id="search_produit" class="form-control" placeholder="Rechercher un produit...">
                    <input type="hidden" name="id_produit" id="id_produit">
                    <div id="resultats_produits" class="list-group position-absolute w-100" style="z-index:1000;"></div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Afficher
                    </button>
                </div>
            </form>

            <?php if ($id_produit>0): ?>
                <h5>Produit : <?= htmlspecialchars($nom_produit) ?></h5>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Type action</th>
                                <th>Date</th>
                                <th>Quantité</th>
                                <th>Bureau</th>
                                <th>Balance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i=0;
                        $balance=0;
                        foreach ($mouvements as $m):
                            $i++;
                            if ($m['type_action']==='stock_initial') {
                                $balance=$m['quantite'];
                            } elseif ($m['type_action']==='achat') {
                                $balance += $m['quantite'];
                            } elseif ($m['type_action']==='decharge') {
                                $balance -= $m['quantite'];
                            }
                            // lien Détails
                            $url = '';
                            if ($m['type_action']==='achat') {
                                $url = 'details.php?id=' . urlencode($m['id_action']);
                            } elseif ($m['type_action']==='decharge') {
                                $url = 'details.php?id=' . urlencode($m['id_action']);
                            }
                        ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= ucfirst($m['type_action']) ?></td>
                                <td><?= $m['date']?date('d/m/Y H:i',strtotime($m['date'])):'' ?></td>
                                <td><?= $m['quantite'] ?></td>
                                <td><?= htmlspecialchars($m['bureau']??'') ?></td>
                                <td><?= $balance ?></td>
                                <td>
                                    <?php if ($m['type_action']!=='stock_initial'): ?>
                                        <a href="<?= $url ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Détails</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search_produit');
    const resultsDiv = document.getElementById('resultats_produits');
    const hiddenInput = document.getElementById('id_produit');

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim();
        if (q.length < 2) {
            resultsDiv.innerHTML = '';
            return;
        }
        fetch('ajax_search_produits.php?q=' + encodeURIComponent(q))
            .then(res => res.json())
            .then(data => {
                resultsDiv.innerHTML = '';
                data.forEach(prod => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.classList.add('list-group-item', 'list-group-item-action');
                    item.textContent = prod.nom;
                    item.addEventListener('click', e => {
                        e.preventDefault();
                        searchInput.value = prod.nom;
                        hiddenInput.value = prod.id;
                        resultsDiv.innerHTML = '';
                    });
                    resultsDiv.appendChild(item);
                });
            });
    });

    // cacher la liste si on clique ailleurs
    document.addEventListener('click', e => {
        if (!resultsDiv.contains(e.target) && e.target !== searchInput) {
            resultsDiv.innerHTML = '';
        }
    });
});
</script>

</body>
</html>
