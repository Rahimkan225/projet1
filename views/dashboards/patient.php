<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Cagnotte.php';

require_type('patient');

$title = "Dashboard patient";
$cagnottes = Cagnotte::findByUser((int)$_SESSION['user_id']);

// Stats patient
$nb = count($cagnottes);
$totalCollecte = 0.0;
$totalDonateurs = 0;
$best = null;
foreach ($cagnottes as $c) {
    $totalCollecte += (float)$c['montant_collecte'];
    $totalDonateurs += (int)($c['nb_donateurs'] ?? 0);
    if ($best === null || (float)$c['montant_collecte'] > (float)$best['montant_collecte']) $best = $c;
}

include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <div class="row g-3">
    <div class="col-lg-3">
      <?php include __DIR__ . '/../layout/sidebar.php'; ?>
    </div>
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Dashboard Patient</h1>
        <a class="btn btn-primary" href="creer-cagnotte.php">Créer une cagnotte</a>
      </div>

      <div class="row g-3">
        <div class="col-md-3">
          <div class="card"><div class="card-body">
            <div class="text-muted small">Cagnottes créées</div>
            <div class="h4 mb-0"><?= (int)$nb ?></div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card"><div class="card-body">
            <div class="text-muted small">Montant total collecté</div>
            <div class="h4 mb-0"><?= format_fcfa($totalCollecte) ?></div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card"><div class="card-body">
            <div class="text-muted small">Donateurs (total)</div>
            <div class="h4 mb-0"><?= (int)$totalDonateurs ?></div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card"><div class="card-body">
            <div class="text-muted small">Meilleure cagnotte</div>
            <div class="h6 mb-0"><?= $best ? clean((string)$best['nom_patient']) : '—' ?></div>
          </div></div>
        </div>
      </div>

      <h2 class="h5 mt-4">Suivi des cagnottes</h2>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead><tr>
            <th>Photo</th><th>Patient</th><th>Statut</th><th>Progression</th><th>Montants</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <?php foreach ($cagnottes as $c): ?>
              <?php $pct = (int)($c['pourcentage'] ?? 0); ?>
              <tr>
                <td style="width:80px;">
                  <div style="width:64px;height:44px;background:#e9ecef;overflow:hidden;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <?php if (!empty($c['photo_patient']) && file_exists((string)$c['photo_patient'])): ?>
                      <img src="<?= clean((string)$c['photo_patient']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                    <?php else: ?>
                      <span class="text-muted small">—</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td><?= clean((string)$c['nom_patient']) ?></td>
                <td><span class="badge bg-secondary"><?= clean((string)$c['statut']) ?></span></td>
                <td style="min-width:180px;">
                  <div class="progress">
                    <div class="progress-bar" data-percentage="<?= $pct ?>" style="width:0%"></div>
                  </div>
                  <div class="small text-muted"><?= $pct ?>%</div>
                </td>
                <td><?= format_fcfa($c['montant_collecte']) ?> / <?= format_fcfa($c['montant_objectif']) ?></td>
                <td>
                  <a class="btn btn-sm btn-outline-primary" href="cagnotte-detail.php?id=<?= (int)$c['id'] ?>">Voir</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$cagnottes): ?>
              <tr><td colspan="6" class="text-muted">Aucune cagnotte pour l’instant.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


