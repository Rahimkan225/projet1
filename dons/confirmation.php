<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Don.php';

$donId = (int)($_GET['don_id'] ?? 0);
$don = Don::findById($donId);
if (!$don) {
    flash_set('error', "Reçu introuvable.");
    header('Location: ../cagnottes.php');
    exit;
}

$title = "Confirmation don";
include __DIR__ . '/../views/layout/header.php';
?>

<div class="container">
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
      <div class="mb-3" style="font-size: 4rem; color: #28a745;">✓</div>
      <h1 class="h3 mb-2 fw-bold">Don enregistré avec succès !</h1>
      <p class="text-muted mb-4">Merci pour votre générosité.</p>

      <div class="mx-auto mt-4" style="max-width:520px;">
        <div class="card bg-light border-0 shadow-sm">
          <div class="card-body text-start p-4">
            <h2 class="h6 fw-bold mb-3">Reçu de don #<?= (int)$don['id'] ?></h2>
            <table class="table table-sm mb-0">
              <tr>
                <td class="text-muted">Montant</td>
                <td class="fw-bold text-success"><?= format_fcfa($don['montant']) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Cagnotte</td>
                <td class="fw-semibold"><?= clean((string)$don['nom_patient']) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Date</td>
                <td><?= date('d/m/Y à H:i', strtotime($don['date_don'])) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Référence</td>
                <td><code><?= clean((string)$don['reference_don']) ?></code></td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
        <a href="../generer-recu.php?don_id=<?= (int)$don['id'] ?>" class="btn btn-primary" target="_blank">
          📄 Télécharger / imprimer
        </a>
        <a href="../cagnottes.php" class="btn btn-outline-primary">
          💝 Voir toutes les cagnottes
        </a>
        <a href="../index.php" class="btn btn-outline-secondary">
          🏠 Retour à l'accueil
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../views/layout/footer.php'; ?>

