<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    die("Produit introuvable");
}

$code = $produit['code'];
$nom  = $produit['nom'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Impression Code-barres</title>
<style>
body { font-family: Arial, sans-serif; }
.page {
    width: 210mm;
    height: 297mm;
    padding: 10mm;
}
.etiquette {
    width: 100mm;
    height: 30mm;
    display: inline-block;
    text-align: center;
    border: 1px dashed #ccc;
    margin: 5mm;
}
</style>
</head>
<body onload="window.print()">
<div class="page">
<?php
// nombre d’étiquettes à répéter sur la page :
$nb = 8;
for ($i=0;$i<$nb;$i++): ?>
  <div class="etiquette">
    <div><strong><?= htmlspecialchars($nom) ?></strong></div>
    <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= urlencode($code) ?>&code=Code128&translate-esc=on" alt="barcode"><br>
    <!-- <small><?= htmlspecialchars($code) ?></small> -->
  </div>
<?php endfor; ?>
</div>
</body>
</html>
