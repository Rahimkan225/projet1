<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Don.php';

$donId = (int)($_GET['don_id'] ?? 0);
$don = Don::findById($donId);
if (!$don) {
    flash_set('error', "Reçu introuvable.");
    header('Location: cagnottes.php');
    exit;
}

$title = "Confirmation don";
include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <div class="card">
    <div class="card-body text-center py-5">
      <h1 class="h4 mb-2">Don enregistré avec succès !</h1>
      <p class="text-muted">Merci pour votre générosité.</p>

      <div class="mx-auto mt-4" style="max-width:520px;">
        <div class="card bg-light">
          <div class="card-body text-start">
            <h2 class="h6">Reçu de don #<?= (int)$don['id'] ?></h2>
            <table class="table table-sm mb-0">
              <tr><td>Montant</td><td><strong><?= format_fcfa($don['montant']) ?></strong></td></tr>
              <tr><td>Cagnotte</td><td><?= clean((string)$don['nom_patient']) ?></td></tr>
              <tr><td>Date</td><td><?= clean((string)$don['date_don']) ?></td></tr>
              <tr><td>Référence</td><td><?= clean((string)$don['reference_don']) ?></td></tr>
            </table>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
        <a href="../generer-recu.php?don_id=<?= (int)$don['id'] ?>" class="btn btn-primary" target="_blank">Télécharger / imprimer</a>
        <a href="../cagnottes.php" class="btn btn-outline-primary">Voir toutes les cagnottes</a>
        <a href="../index.php" class="btn btn-outline-secondary">Retour à l'accueil</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


