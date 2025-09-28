<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

$errors = [];
$success = false;

// --- Générer num_bon auto ---
$stmt = $pdo->query("SELECT MAX(id) as max_id FROM achats");
$row = $stmt->fetch();
$num_bon = 'BA-' . str_pad((($row['max_id'] ?? 0) + 1), 5, '0', STR_PAD_LEFT);

// --- Sauvegarde ---
if (isset($_POST['valider'])) {
    try {
        // Récupération et nettoyage
        $num_achat = $_POST['num_achat'] ?? $num_bon;
        $date_input = $_POST['date'] ?? '';
        $id_fournisseur = isset($_POST['id_fournisseur']) && $_POST['id_fournisseur'] !== '' ? intval($_POST['id_fournisseur']) : null;

        // Convertir date 'YYYY-MM-DDTHH:MM' => 'YYYY-MM-DD HH:MM:SS'
        if ($date_input === '') {
            $date = date('Y-m-d H:i:s');
        } else {
            $date = str_replace('T', ' ', $date_input);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date)) {
                $date .= ':00';
            }
        }

        // Validation des lignes produits
        $produits = $_POST['produit_id'] ?? [];
        $prix_arr = $_POST['prix_achat'] ?? [];
        $qte_arr = $_POST['quantite'] ?? [];

        if (empty($produits) || !is_array($produits) || count($produits) === 0) {
            $errors[] = "Ajoutez au moins un produit au bon d'achat.";
        } else {
            foreach ($produits as $k => $id_produit) {
                $ligneIndex = $k + 1;
                if ($id_produit === '') {
                    $errors[] = "Produit invalide à la ligne $ligneIndex.";
                    break;
                }
                $prix = isset($prix_arr[$k]) ? str_replace(',', '.', $prix_arr[$k]) : null;
                $qte  = isset($qte_arr[$k]) ? str_replace(',', '.', $qte_arr[$k]) : null;
                if (!is_numeric($prix) || !is_numeric($qte)) {
                    $errors[] = "Prix ou quantité invalide à la ligne $ligneIndex.";
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

            $ins = $pdo->prepare("INSERT INTO achats (num_achat, date, id_fournisseur) VALUES (:num_achat, :date, :id_fournisseur)");
            $ins->bindValue(':num_achat', $num_achat);
            $ins->bindValue(':date', $date);
            if ($id_fournisseur === null) {
                $ins->bindValue(':id_fournisseur', null, PDO::PARAM_NULL);
            } else {
                $ins->bindValue(':id_fournisseur', $id_fournisseur, PDO::PARAM_INT);
            }
            $ins->execute();
            $id_achat = $pdo->lastInsertId();

            $insDet = $pdo->prepare("INSERT INTO achats_details (id_achat, id_produit, prix_achat, quantite) VALUES (:id_achat, :id_produit, :prix_achat, :quantite)");
            foreach ($produits as $k => $id_produit) {
                $prix = floatval(str_replace(',', '.', $prix_arr[$k]));
                $qte  = floatval(str_replace(',', '.', $qte_arr[$k]));
                $insDet->execute([
                    ':id_achat' => $id_achat,
                    ':id_produit' => intval($id_produit),
                    ':prix_achat' => $prix,
                    ':quantite' => $qte
                ]);
            }

            $pdo->commit();
            $success = true; // on affiche le message et on redirige après 3s
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
    <h2>Nouveau Bon d'Achat</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">
            Bon d'achat enregistré avec succès. Vous allez être redirigé dans 3 secondes…
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
    <form method="post" id="bonAchatForm">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Numéro Bon</label>
                <input type="text" name="num_achat" class="form-control" value="<?= htmlspecialchars($num_bon) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Date</label>
                <input type="datetime-local" name="date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
            </div>
            <div class="col-md-6">
                <label>Fournisseur</label>
                <input type="text" id="searchFournisseur" class="form-control" placeholder="Rechercher fournisseur...">
                <input type="hidden" name="id_fournisseur" id="id_fournisseur">
            </div>
        </div>

        <hr>

        <h5>Produits</h5>
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="searchProduit" class="form-control" placeholder="Rechercher produit...">
                <input type="hidden" id="id_produit">
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" id="prix_achat" class="form-control" placeholder="Prix achat">
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" id="quantite" class="form-control" value="1">
            </div>
            <div class="col-md-2">
                <button type="button" id="ajouterLigne" class="btn btn-success">+ Ajouter</button>
            </div>
        </div>

        <table class="table table-bordered" id="tableProduits">
            <thead class="table-dark">
                <tr>
                    <th>Produit</th>
                    <th>Prix achat</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th id="totalCell">0.00</th>
                    <th></th>
                </tr>
            </tfoot>
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
// === Autocomplétion ===
$(function(){
  $("#searchFournisseur").autocomplete({
    source: function(request, response){
      $.getJSON("ajax_fournisseurs.php", {q: request.term}, function(data){
        response($.map(data, function(item){
          return {label: item.nom, value: item.nom, id: item.id};
        }));
      });
    },
    select: function(event, ui){
      $("#id_fournisseur").val(ui.item.id);
    },
    minLength: 1
  });

  $("#searchProduit").autocomplete({
    source: function(request, response){
      $.getJSON("ajax_produits.php", {q: request.term}, function(data){
        response($.map(data, function(item){
          return {label: item.nom, value: item.nom, id: item.id};
        }));
      });
    },
    select: function(event, ui){
      $("#id_produit").val(ui.item.id);
    },
    minLength: 1
  });
});

// === Gestion dynamique du tableau ===
function recalculerTotal(){
    let total = 0;
    document.querySelectorAll('.sousTotal').forEach(cell=>{
        total += parseFloat(cell.textContent) || 0;
    });
    document.getElementById('totalCell').textContent = total.toFixed(2);
}

document.getElementById('ajouterLigne').addEventListener('click', function(){
    let id_produit = document.getElementById('id_produit').value;
    let nom_produit = document.getElementById('searchProduit').value;
    let prix = parseFloat(document.getElementById('prix_achat').value) || 0;
    let quantite = parseFloat(document.getElementById('quantite').value) || 1;

    if(!id_produit || !nom_produit) {
        alert('Veuillez sélectionner un produit.');
        return;
    }

    let sousTotal = prix * quantite;

    let tbody = document.querySelector('#tableProduits tbody');
    let tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            ${nom_produit}
            <input type="hidden" name="produit_id[]" value="${id_produit}">
        </td>
        <td>
            <input type="number" step="0.01" name="prix_achat[]" class="form-control prix" value="${prix}">
        </td>
        <td>
            <input type="number" step="0.01" name="quantite[]" class="form-control quantite" value="${quantite}">
        </td>
        <td class="sousTotal">${sousTotal.toFixed(2)}</td>
        <td><button type="button" class="btn btn-danger btn-sm supprimer">X</button></td>
    `;
    tbody.appendChild(tr);
    recalculerTotal();

    document.getElementById('searchProduit').value = '';
    document.getElementById('id_produit').value = '';
    document.getElementById('prix_achat').value = '';
    document.getElementById('quantite').value = 1;
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('supprimer')){
        e.target.closest('tr').remove();
        recalculerTotal();
    }
});

document.addEventListener('input', function(e){
    if(e.target.classList.contains('prix') || e.target.classList.contains('quantite')){
        let tr = e.target.closest('tr');
        let prix = parseFloat(tr.querySelector('.prix').value) || 0;
        let qte = parseFloat(tr.querySelector('.quantite').value) || 1;
        tr.querySelector('.sousTotal').textContent = (prix*qte).toFixed(2);
        recalculerTotal();
    }
});

document.getElementById('bonAchatForm').addEventListener('submit', function(e){
    const rows = document.querySelectorAll('#tableProduits tbody tr').length;
    if (rows === 0) {
        e.preventDefault();
        alert('Ajoutez au moins un produit avant de valider le bon.');
    }
});
</script>

<?php require_once("../includes/footer.php"); ?>
