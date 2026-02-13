<?php
require_once __DIR__ . '/../../config/init.php';
// V2: interdit au patient (création gérée par admin_gestionnaire)
require_roles(['admin_gestionnaire']);

$title = "Créer une cagnotte";
include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <h1 class="h4 mb-3">Créer une nouvelle cagnotte</h1>

  <form action="controllers/cagnotte_controller.php" method="POST" enctype="multipart/form-data" id="cagnotte-form" onsubmit="return validateForm('cagnotte-form')">
    <input type="hidden" name="action" value="create">
    <?= csrf_field() ?>

    <div class="card mb-3">
      <div class="card-header"><strong>Étape 1 — Informations patient</strong></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Nom du patient *</label>
            <input type="text" name="nom_patient" class="form-control" required>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Âge *</label>
            <input type="number" name="age_patient" class="form-control" min="0" max="120" required>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Relation</label>
            <select name="relation_patient" class="form-select">
              <option value="Moi-même">Moi-même</option>
              <option value="Mon enfant">Mon enfant</option>
              <option value="Mon conjoint">Mon conjoint</option>
              <option value="Autre">Autre</option>
            </select>
          </div>
          <div class="col-md-12 mb-3">
            <label class="form-label">Photo du patient (JPG/PNG, max 5MB) *</label>
            <input type="file" name="photo_patient" class="form-control" accept="image/png,image/jpeg" required>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>Étape 2 — Informations médicales</strong></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
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
          <div class="col-md-6 mb-3">
            <label class="form-label">Niveau d'urgence *</label>
            <select name="urgence" class="form-select" required>
              <option value="critique">Critique</option>
              <option value="elevee">Élevée</option>
              <option value="moderee">Modérée</option>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Nom de l'hôpital/clinique *</label>
          <input type="text" name="hopital" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Diagnostic complet (min 100 caractères) *</label>
          <textarea name="diagnostic" class="form-control" rows="5" minlength="100" required></textarea>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>Étape 3 — Objectif & Documents</strong></div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Montant objectif (FCFA) *</label>
          <input type="number" name="montant_objectif" class="form-control" min="50000" max="50000000" required>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Ordonnance médicale *</label>
            <input type="file" name="documents[]" class="form-control" accept="application/pdf,image/png,image/jpeg" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Devis de l'hôpital *</label>
            <input type="file" name="documents[]" class="form-control" accept="application/pdf,image/png,image/jpeg" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Certificat médical (optionnel)</label>
            <input type="file" name="documents[]" class="form-control" accept="application/pdf,image/png,image/jpeg">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Autre (optionnel)</label>
            <input type="file" name="documents[]" class="form-control" accept="application/pdf,image/png,image/jpeg">
          </div>
        </div>

        <button class="btn btn-primary btn-lg">Créer la cagnotte</button>
      </div>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


