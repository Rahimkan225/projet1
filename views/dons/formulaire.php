<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Cagnotte.php';

$cagnotteId = (int)($_GET['cagnotte_id'] ?? 0);
$c = Cagnotte::findByIdWithStats($cagnotteId);
if (!$c || ($c['statut'] ?? '') !== 'active') {
    flash_set('error', "Cagnotte introuvable ou inactive.");
    header('Location: cagnottes.php');
    exit;
}

$title = "Faire un don";
include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <h1 class="h4 mb-3">Faire un don</h1>

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start gap-2">
        <div>
          <div class="text-muted small">Bénéficiaire</div>
          <div class="fw-semibold"><?= clean((string)$c['nom_patient']) ?></div>
          <div class="text-muted small"><?= clean((string)$c['hopital']) ?></div>
        </div>
        <span class="badge <?= urgence_badge_class((string)$c['urgence']) ?> text-white"><?= strtoupper(clean((string)$c['urgence'])) ?></span>
      </div>
    </div>
  </div>

  <form action="controllers/don_controller.php" method="POST" id="don-form">
    <input type="hidden" name="action" value="create">
    <input type="hidden" name="cagnotte_id" value="<?= (int)$cagnotteId ?>">
    <?= csrf_field() ?>

    <div id="step-1" class="wizard-step">
      <div class="card">
        <div class="card-header"><strong>Étape 1 — Choix du montant</strong></div>
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-outline-primary btn-montant" data-montant="1000">1 000</button>
            <button type="button" class="btn btn-outline-primary btn-montant" data-montant="5000">5 000</button>
            <button type="button" class="btn btn-outline-primary btn-montant" data-montant="10000">10 000</button>
            <button type="button" class="btn btn-outline-primary btn-montant" data-montant="50000">50 000</button>
          </div>
          <div class="mb-3">
            <label class="form-label">Montant (FCFA) *</label>
            <input type="number" class="form-control" id="montant" name="montant" min="500" required>
          </div>
          <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" onclick="nextStep()">Continuer</button>
          </div>
        </div>
      </div>
    </div>

    <div id="step-2" class="wizard-step">
      <div class="card">
        <div class="card-header"><strong>Étape 2 — Informations donateur</strong></div>
        <div class="card-body">
          <?php if (!empty($_SESSION['user_id'])): ?>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Nom</label>
                <input class="form-control" value="<?= clean((string)($_SESSION['nom_complet'] ?? '')) ?>" readonly>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" value="<?= clean((string)($_SESSION['email'] ?? '')) ?>" readonly>
              </div>
            </div>
          <?php else: ?>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Nom *</label>
                <input class="form-control" name="nom_donateur" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Email *</label>
                <input class="form-control" type="email" name="email_donateur" required>
              </div>
            </div>
          <?php endif; ?>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="est_anonyme" id="anonyme">
            <label class="form-check-label" for="anonyme">Faire un don anonyme</label>
          </div>

          <div class="mb-3">
            <label class="form-label">Message de soutien (optionnel)</label>
            <textarea class="form-control" name="message" maxlength="500" rows="3"></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">Retour</button>
            <button type="button" class="btn btn-primary" onclick="nextStep()">Continuer</button>
          </div>
        </div>
      </div>
    </div>

    <div id="step-3" class="wizard-step">
      <div class="card">
        <div class="card-header"><strong>Étape 3 — Paiement (simulation)</strong></div>
        <div class="card-body">
          <div class="alert alert-warning">
            <strong>Mode simulation - Projet étudiant</strong>
            <div class="small">Le paiement est automatiquement validé. En production, une interface mobile money serait intégrée.</div>
          </div>

          <div class="card bg-light">
            <div class="card-body">
              <h2 class="h6">Résumé</h2>
              <div>Montant : <strong><span id="resume-montant">0</span> FCFA</strong></div>
              <div>Bénéficiaire : <strong><?= clean((string)$c['nom_patient']) ?></strong></div>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">Retour</button>
            <button type="submit" class="btn btn-success btn-lg">Confirmer le don</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script src="public/js/wizard-don.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const montant = document.getElementById('montant');
  const resume = document.getElementById('resume-montant');
  if (montant && resume) {
    montant.addEventListener('input', () => resume.textContent = Number(montant.value || 0).toLocaleString('fr-FR'));
  }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>


