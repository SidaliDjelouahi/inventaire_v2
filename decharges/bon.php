<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

$errors = [];
$success = false;

// --- Générer num_decharge auto ---
$stmt = $pdo->query("SELECT MAX(id) as max_id FROM decharges");
$row = $stmt->fetch();
$num_decharge = 'DCH-' . str_pad((($row['max_id'] ?? 0) + 1), 5, '0', STR_PAD_LEFT);

// --- Sauvegarde ---
if (isset($_POST['valider'])) {
    try {
        // Récupération
        $num_decharge_post = $_POST['num_decharge'] ?? $num_decharge;
        $date_input = $_POST['date'] ?? '';
        $id_bureau = isset($_POST['id_bureau']) && $_POST['id_bureau'] !== '' ? intval($_POST['id_bureau']) : null;

        // Date formatée
        if ($date_input === '') {
            $date = date('Y-m-d H:i:s');
        } else {
            $date = str_replace('T', ' ', $date_input);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date)) {
                $date .= ':00';
            }
        }

        // Validation produits
        $produits = $_POST['produit_id'] ?? [];
        $qte_arr = $_POST['quantite'] ?? [];

        if (empty($produits) || !is_array($produits) || count($produits) === 0) {
            $errors[] = "Ajoutez au moins un produit à la décharge.";
        } else {
            foreach ($produits as $k => $id_produit) {
                $ligneIndex = $k + 1;
                if ($id_produit === '') {
                    $errors[] = "Produit invalide à la ligne $ligneIndex.";
                    break;
                }
                $qte  = isset($qte_arr[$k]) ? str_replace(',', '.', $qte_arr[$k]) : null;
                if (!is_numeric($qte)) {
                    $errors[] = "Quantité invalide à la ligne $ligneIndex.";
                    break;
                }
                $chk = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE id = ?");
                $chk->execute([intval($id_produit)]);
                if ($chk->fetchColumn() == 0) {
                    $errors[] = "Le produit (id={$id_produit}) de la ligne $ligneIndex n'existe pas.";
                    break;
                }
            }
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            // Insérer la décharge
            $ins = $pdo->prepare("INSERT INTO decharges (num_decharge, date, id_bureau) 
                                  VALUES (:num_decharge, :date, :id_bureau)");
            $ins->bindValue(':num_decharge', $num_decharge_post);
            $ins->bindValue(':date', $date);
            if ($id_bureau === null) {
                $ins->bindValue(':id_bureau', null, PDO::PARAM_NULL);
            } else {
                $ins->bindValue(':id_bureau', $id_bureau, PDO::PARAM_INT);
            }
            $ins->execute();
            $id_decharge = $pdo->lastInsertId();

            // Insérer les détails
            $insDet = $pdo->prepare("INSERT INTO decharges_details (id_decharge, id_produit, quantite) 
                                     VALUES (:id_decharge, :id_produit, :quantite)");
            foreach ($produits as $k => $id_produit) {
                $qte  = floatval(str_replace(',', '.', $qte_arr[$k]));
                $insDet->execute([
                    ':id_decharge' => $id_decharge,
                    ':id_produit' => intval($id_produit),
                    ':quantite' => $qte
                ]);
            }

            $pdo->commit();
            $success = true;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errors[] = "Erreur lors de la sauvegarde : " . $e->getMessage();
    }
}

require_once("../includes/header.php");
require_once("../includes/sidebar.php");
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2>Nouvelle Décharge</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">
            Décharge enregistrée avec succès. Vous allez être redirigé dans 3 secondes…
        </div>
        <script>
        setTimeout(function(){
            window.location.href = 'bon.php';
        }, 3000);
        </script>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
            <?php foreach ($errors as $er): ?>
                <li><?= htmlspecialchars($er) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" id="dechargeForm">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Numéro Décharge</label>
                <input type="text" name="num_decharge" class="form-control" value="<?= htmlspecialchars($num_decharge) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Date</label>
                <input type="datetime-local" name="date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
            </div>
            <div class="col-md-6">
                <label>Bureau</label>
                <input type="text" id="searchBureau" class="form-control" placeholder="Rechercher bureau...">
                <input type="hidden" name="id_bureau" id="id_bureau">
            </div>
        </div>

        <hr>

        <h5>Produits</h5>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="searchProduit" class="form-control" placeholder="Rechercher produit...">
                <input type="hidden" id="id_produit">
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" id="quantite" class="form-control" value="1">
            </div>
            <div class="col-md-3">
                <button type="button" id="ajouterLigne" class="btn btn-success">+ Ajouter</button>
            </div>
        </div>

        <table class="table table-bordered" id="tableProduits">
            <thead class="table-dark">
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="submit" name="valider" class="btn btn-primary">Valider</button>
    </form>
    <?php endif; ?>
</div>

<!-- jQuery + jQuery UI -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
// Autocomplétion Bureau : bureau – service
$(function(){
  $("#searchBureau").autocomplete({
  source: function(request, response){
    $.getJSON("ajax_bureaux.php", {q: request.term}, function(data){
      response($.map(data, function(item){
        return {
          label: item.bureau_service, // service – bureau
          value: item.bureau_service,
          id: item.id
        };
      }));
    });
  },
  select: function(event, ui){
    $("#id_bureau").val(ui.item.id);
  },
  minLength: 1
});


      $("#searchProduit").autocomplete({
      source: function(request, response){
        $.getJSON("ajax_produits.php", {q: request.term}, function(data){
          response($.map(data, function(item){
            return {
              label:  item.nom +  "  -  quantite : ["+item.stock+"] ", // stock devant le nom
              value: item.nom,
              id: item.id
            };
          }));
        });
      },
      select: function(event, ui){
        $("#id_produit").val(ui.item.id);
      },
      minLength: 1
    });

});

// Gestion tableau
document.getElementById('ajouterLigne').addEventListener('click', function(){
    let id_produit = document.getElementById('id_produit').value;
    let nom_produit = document.getElementById('searchProduit').value;
    let quantite = parseFloat(document.getElementById('quantite').value) || 1;

    if(!id_produit || !nom_produit) {
        alert('Veuillez sélectionner un produit.');
        return;
    }

    let tbody = document.querySelector('#tableProduits tbody');
    let tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            ${nom_produit}
            <input type="hidden" name="produit_id[]" value="${id_produit}">
        </td>
        <td>
            <input type="number" step="0.01" name="quantite[]" class="form-control quantite" value="${quantite}">
        </td>
        <td><button type="button" class="btn btn-danger btn-sm supprimer">X</button></td>
    `;
    tbody.appendChild(tr);

    document.getElementById('searchProduit').value = '';
    document.getElementById('id_produit').value = '';
    document.getElementById('quantite').value = 1;
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('supprimer')){
        e.target.closest('tr').remove();
    }
});

document.getElementById('dechargeForm').addEventListener('submit', function(e){
    const rows = document.querySelectorAll('#tableProduits tbody tr').length;
    if (rows === 0) {
        e.preventDefault();
        alert('Ajoutez au moins un produit avant de valider la décharge.');
    }
});
</script>

<?php require_once("../includes/footer.php"); ?>
