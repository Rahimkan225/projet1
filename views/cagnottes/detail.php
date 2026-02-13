<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Cagnotte.php';
require_once __DIR__ . '/../../models/Don.php';

$id = (int)($_GET['id'] ?? 0);
$c = Cagnotte::findByIdWithStats($id);
if (!$c) {
    flash_set('error', "Cagnotte introuvable.");
    header('Location: cagnottes.php');
    exit;
}
$docs = Cagnotte::findDocuments($id);
$dons = Don::listByCagnotte($id);
$pct = (int)($c['pourcentage'] ?? 0);

$title = "Détail cagnotte";
include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <div class="row g-3 align-items-start">
    <div class="col-lg-6">
      <div class="card">
        <div style="height:340px;background:#e9ecef;display:flex;align-items:center;justify-content:center;">
          <?php if (!empty($c['photo_patient']) && file_exists((string)$c['photo_patient'])): ?>
            <img src="<?= clean((string)$c['photo_patient']) ?>" class="w-100 h-100" style="object-fit:cover;" alt="Photo patient">
          <?php else: ?>
            <img src="<?= clean(patient_placeholder_img((int)$c['id'])) ?>" class="w-100 h-100" style="object-fit:cover;" alt="Illustration patient">
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h1 class="h4 mb-1"><?= clean((string)$c['nom_patient']) ?>, <?= (int)$c['age_patient'] ?> ans</h1>
              <div class="text-muted"><?= clean((string)$c['hopital']) ?></div>
            </div>
            <span class="badge <?= urgence_badge_class((string)$c['urgence']) ?> text-white"><?= strtoupper(clean((string)$c['urgence'])) ?></span>
          </div>

          <div class="mt-3">
            <div class="progress mb-2">
              <div class="progress-bar" data-percentage="<?= $pct ?>" style="width:0%"></div>
            </div>
            <div class="small">
              <strong><?= format_fcfa($c['montant_collecte']) ?></strong> collectés sur <?= format_fcfa($c['montant_objectif']) ?>
              <br><span class="text-muted"><?= (int)($c['nb_donateurs'] ?? 0) ?> donateurs</span>
            </div>
          </div>

          <div class="d-grid mt-3">
            <a href="faire-don.php?cagnotte_id=<?= (int)$c['id'] ?>" class="btn btn-primary btn-lg">FAIRE UN DON</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <ul class="nav nav-tabs mt-4" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-histoire" type="button" role="tab">Histoire</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-docs" type="button" role="tab">Documents</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-donateurs" type="button" role="tab">Donateurs (<?= count($dons) ?>)</button>
    </li>
  </ul>
  <div class="tab-content border border-top-0 bg-white p-3">
    <div class="tab-pane fade show active" id="tab-histoire" role="tabpanel">
      <div class="row">
        <div class="col-md-8">
          <h2 class="h6">Diagnostic complet</h2>
          <p><?= nl2br(clean((string)$c['diagnostic'])) ?></p>
        </div>
        <div class="col-md-4">
          <div class="card"><div class="card-body">
            <div class="text-muted small">Créée le</div>
            <div class="fw-semibold"><?= clean((string)$c['date_creation']) ?></div>
            <hr>
            <div class="text-muted small">Créée par</div>
            <div class="fw-semibold"><?= clean((string)($c['createur'] ?? '')) ?></div>
            <hr>
            <div class="text-muted small">Pathologie</div>
            <div class="fw-semibold"><?= clean((string)$c['pathologie']) ?></div>
          </div></div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="tab-docs" role="tabpanel">
      <?php if (!$docs): ?>
        <div class="text-muted">Aucun document.</div>
      <?php else: ?>
        <ul class="list-group">
          <?php foreach ($docs as $d): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold"><?= clean((string)$d['type_document']) ?></div>
                <div class="text-muted small"><?= clean((string)$d['nom_fichier']) ?></div>
              </div>
              <a class="btn btn-sm btn-outline-primary" href="download.php?doc_id=<?= (int)$d['id'] ?>">Télécharger</a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php if (checkRole(['admin_gestionnaire','admin_general'])): ?>
        <hr>
        <form method="POST" action="controllers/gestionnaire_controller.php" enctype="multipart/form-data" class="mt-2">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add_docs">
          <input type="hidden" name="cagnotte_id" value="<?= (int)$c['id'] ?>">
          <div class="mb-2">
            <label class="form-label">Ajouter des justificatifs (PDF/JPG, max 5Mo chacun)</label>
            <input type="file" name="documents[]" class="form-control" accept="application/pdf,image/jpeg,image/png" multiple>
          </div>
          <button class="btn btn-sm btn-primary">Ajouter les documents</button>
        </form>
      <?php endif; ?>
    </div>
    <div class="tab-pane fade" id="tab-donateurs" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead><tr><th>Date</th><th>Nom</th><th>Montant</th><th>Message</th></tr></thead>
          <tbody>
            <?php foreach ($dons as $d): ?>
              <tr>
                <td><?= clean((string)$d['date_don']) ?></td>
                <td><?= $d['est_anonyme'] ? '<em>Anonyme</em>' : clean((string)$d['nom_donateur']) ?></td>
                <td><strong><?= format_fcfa($d['montant']) ?></strong></td>
                <td><?= clean((string)($d['message'] ?? '')) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$dons): ?>
              <tr><td colspan="4" class="text-muted">Aucun don pour l’instant.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


