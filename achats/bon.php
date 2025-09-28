<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// --- Générer num_bon auto ---
$stmt = $pdo->query("SELECT MAX(id) as max_id FROM achats");
$row = $stmt->fetch();
$num_bon = 'BA-' . str_pad(($row['max_id'] + 1), 5, '0', STR_PAD_LEFT);

// --- Sauvegarde ---
if (isset($_POST['valider'])) {
    $num_achat = $_POST['num_achat'];
    $date = $_POST['date'];
    $id_fournisseur = $_POST['id_fournisseur'];

    // 1. Insertion dans achats
    $stmt = $pdo->prepare("INSERT INTO achats (num_achat, date, id_fournisseur)
                           VALUES (?, ?, ?)");
    $stmt->execute([$num_achat, $date, $id_fournisseur]);
    $id_achat = $pdo->lastInsertId();

    // 2. Insertion dans achats_details
    if (!empty($_POST['produit_id'])) {
        foreach ($_POST['produit_id'] as $k => $id_produit) {
            $prix = $_POST['prix_achat'][$k];
            $quantite = $_POST['quantite'][$k];
            $stmt = $pdo->prepare("INSERT INTO achats_details (id_achat, id_produit, prix_achat, quantite)
                                   VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_achat, $id_produit, $prix, $quantite]);
        }
    }

    header("Location: bon_achat.php?success=1");
    exit;
}
?>
<?php require_once("../includes/header.php"); ?>
<?php require_once("../includes/sidebar.php"); ?>

<div class="col-md-9 col-lg-10 p-4">
<h2>Nouveau Bon d'Achat</h2>

<form method="post" id="bonAchatForm">
    <div class="row mb-3">
        <div class="col-md-3">
            <label>Numéro Bon</label>
            <input type="text" name="num_achat" class="form-control" value="<?= $num_bon ?>" readonly>
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
    <button type="button" class="btn btn-secondary">Imprimer</button>
</form>
</div>

<!-- jQuery + jQuery UI -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
// === Autocomplétion ===
$(function(){

  // Fournisseurs
  $("#searchFournisseur").autocomplete({
    source: function(request, response){
      $.getJSON("ajax_fournisseurs.php", {q: request.term}, function(data){
        response($.map(data, function(item){
          return {
            label: item.nom,
            value: item.nom,
            id: item.id
          };
        }));
      });
    },
    select: function(event, ui){
      $("#id_fournisseur").val(ui.item.id);
    },
    minLength: 1
  });

  // Produits
  $("#searchProduit").autocomplete({
    source: function(request, response){
      $.getJSON("ajax_produits.php", {q: request.term}, function(data){
        response($.map(data, function(item){
          return {
            label: item.nom,
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
</script>

<?php require_once("../includes/footer.php"); ?>
