<?php
// bureaux/table.php
require_once "../includes/db.php";
require_once "../includes/header.php";
require_once "../includes/sidebar.php";

// Récupérer les bureaux avec le service associé
$sql = "SELECT b.id, b.bureau, s.nom AS service_nom
        FROM bureaux b
        JOIN services s ON b.id_service = s.id
        ORDER BY b.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$bureaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des services pour le formulaire
$services = $pdo->query("SELECT id, nom FROM services ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un bureau (via POST du modal)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['bureau'])) {
    $bureau = trim($_POST['bureau']);
    $id_service = intval($_POST['id_service']);

    if ($bureau && $id_service) {
        $stmt = $pdo->prepare("INSERT INTO bureaux (bureau, id_service) VALUES (?, ?)");
        $stmt->execute([$bureau, $id_service]);
        header("Location: table.php");
        exit();
    }
}
?>

<div class="col-md-9 col-lg-10 p-4">
    <h2 class="mb-4">Gestion des bureaux</h2>

    <!-- Bouton qui ouvre le modal -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBureauModal">
        <i class="bi bi-plus-circle"></i> Ajouter un bureau
    </button>

    <!-- Tableau -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nom du bureau</th>
                    <th>Service</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($bureaux): ?>
                <?php foreach ($bureaux as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['id']) ?></td>
                    <td><?= htmlspecialchars($b['bureau']) ?></td>
                    <td><?= htmlspecialchars($b['service_nom']) ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="supprimer.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce bureau ?');">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">Aucun bureau trouvé</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajouter Bureau -->
<div class="modal fade" id="addBureauModal" tabindex="-1" aria-labelledby="addBureauModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addBureauModalLabel">Ajouter un bureau</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nom du bureau</label>
            <input type="text" name="bureau" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Service</label>
            <select name="id_service" class="form-control" required>
              <option value="">-- Choisir un service --</option>
              <?php foreach ($services as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Enregistrer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once "../includes/footer.php"; ?>
