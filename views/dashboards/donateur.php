<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Don.php';

require_type('donateur');

$title = "Dashboard donateur";
$stats = Don::statsByDonateur((int)$_SESSION['user_id']);
$historique = Don::historyByDonateur((int)$_SESSION['user_id'], 20);

include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <div class="row g-3">
    <div class="col-lg-3">
      <?php include __DIR__ . '/../layout/sidebar.php'; ?>
    </div>
    <div class="col-lg-9">
      <h1 class="h4 mb-3">Dashboard Donateur</h1>

      <div class="row g-3">
        <div class="col-md-4">
          <div class="card"><div class="card-body">
            <div class="text-muted small">Total donné</div>
            <div class="h4 mb-0"><?= format_fcfa($stats['total_donne'] ?? 0) ?></div>
          </div></div>
        </div>
        <div class="col-md-4">
          <div class="card"><div class="card-body">
            <div class="text-muted small">Nombre de dons</div>
            <div class="h4 mb-0"><?= (int)($stats['nb_dons'] ?? 0) ?></div>
          </div></div>
        </div>
        <div class="col-md-4">
          <div class="card"><div class="card-body">
            <div class="text-muted small">Cagnottes aidées</div>
            <div class="h4 mb-0"><?= (int)($stats['nb_cagnottes'] ?? 0) ?></div>
          </div></div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
        <h2 class="h5 mb-0">Historique des dons</h2>
        <a class="btn btn-primary" href="cagnottes.php">Découvrir des cagnottes</a>
      </div>

      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead><tr><th>Date</th><th>Cagnotte</th><th>Montant</th><th>Référence</th></tr></thead>
          <tbody>
            <?php foreach ($historique as $d): ?>
              <tr>
                <td><?= clean((string)$d['date_don']) ?></td>
                <td><?= clean((string)$d['nom_patient']) ?></td>
                <td><strong><?= format_fcfa($d['montant']) ?></strong></td>
                <td class="text-muted small"><?= clean((string)$d['reference_don']) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$historique): ?>
              <tr><td colspan="4" class="text-muted">Aucun don pour l’instant.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


