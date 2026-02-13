<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/User.php';

require_roles(['admin_gestionnaire', 'admin_general']);

$title = "Créer patient & cagnotte (wizard)";
// On charge une grande liste pour permettre de retrouver tous les patients existants
$patients = User::listPatientsDetailed(2000);

include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Wizard — Créer un patient + une cagnotte</h1>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="patients.php">Gérer les patients</a>
      <a class="btn btn-outline-secondary" href="../index.php">Retour au site</a>
    </div>
  </div>

  <form action="../controllers/gestionnaire_controller.php" method="POST" enctype="multipart/form-data" id="wizard-form">
    <input type="hidden" name="action" value="create_patient_and_cagnotte">
    <?= csrf_field() ?>

    <div class="card mb-3">
      <div class="card-header"><strong>Étape 1 — Sélection / Création du patient</strong></div>
      <div class="card-body">
        <div class="mb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="patient_mode" id="pm_existing" value="existing" checked>
            <label class="form-check-label" for="pm_existing">Patient existant</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="patient_mode" id="pm_new" value="new">
            <label class="form-check-label" for="pm_new">Nouveau patient</label>
          </div>
        </div>

        <div id="patient-existing">
          <label class="form-label">Choisir un patient *</label>
          <select class="form-select" name="patient_id">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($patients as $p): ?>
              <option value="<?= (int)$p['id'] ?>">
                #<?= (int)$p['id'] ?> — <?= clean((string)$p['nom_complet']) ?> (<?= clean((string)$p['email']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Le patient doit être de type <code>patient</code>.</div>
        </div>

        <div id="patient-new" class="d-none">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nom complet patient *</label>
              <input class="form-control" name="patient_nom_complet">
            </div>
            <div class="col-md-6">
              <label class="form-label">Téléphone patient</label>
              <input class="form-control" name="patient_telephone" placeholder="+2250700000000">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email patient *</label>
              <input class="form-control" type="email" name="patient_email">
            </div>
            <div class="col-md-3">
              <label class="form-label">Mot de passe *</label>
              <input class="form-control" type="password" name="patient_mot_de_passe">
            </div>
            <div class="col-md-3">
              <label class="form-label">Confirmation *</label>
              <input class="form-control" type="password" name="patient_mot_de_passe_confirm">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>Étape 2 — Infos médicales & urgence</strong></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nom du patient (affiché sur la cagnotte) *</label>
            <input class="form-control" name="nom_patient" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Âge *</label>
            <input class="form-control" type="number" name="age_patient" min="0" max="120" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Urgence *</label>
            <select class="form-select" name="urgence" required>
              <option value="critique">Critique</option>
              <option value="elevee">Élevée</option>
              <option value="moderee" selected>Modérée</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Pathologie *</label>
            <select name="pathologie" class="form-select" required>
              <option value="">-- Choisir --</option>
              <option value="cancer">Cancer</option>
              <option value="chirurgie">Chirurgie cardiaque</option>
              <option value="accident">Accident</option>
              <option value="maternite">Maternité à risque</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Hôpital/clinique *</label>
            <input class="form-control" name="hopital" required>
          </div>
          <div class="col-12">
            <label class="form-label">Diagnostic complet (min 100 caractères) *</label>
            <textarea class="form-control" name="diagnostic" rows="5" minlength="100" required></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Montant objectif (FCFA) *</label>
            <input class="form-control" type="number" name="montant_objectif" min="50000" max="50000000" required>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>Étape 3 — Upload des justificatifs (PDF/JPG, max 5Mo)</strong></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Ordonnance *</label>
            <input class="form-control" type="file" name="documents[]" accept="application/pdf,image/jpeg,image/png" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Devis *</label>
            <input class="form-control" type="file" name="documents[]" accept="application/pdf,image/jpeg,image/png" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Certificat (optionnel)</label>
            <input class="form-control" type="file" name="documents[]" accept="application/pdf,image/jpeg,image/png">
          </div>
          <div class="col-md-6">
            <label class="form-label">Autre (optionnel)</label>
            <input class="form-control" type="file" name="documents[]" accept="application/pdf,image/jpeg,image/png">
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-primary btn-lg">Créer patient & cagnotte</button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  (function () {
    function syncPatientMode() {
      const isNew = document.getElementById('pm_new').checked;
      document.getElementById('patient-new').classList.toggle('d-none', !isNew);
      document.getElementById('patient-existing').classList.toggle('d-none', isNew);
    }
    document.getElementById('pm_existing').addEventListener('change', syncPatientMode);
    document.getElementById('pm_new').addEventListener('change', syncPatientMode);
    syncPatientMode();
  })();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>


