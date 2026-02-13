<?php
require_once __DIR__ . '/../../config/init.php';
require_roles(['admin_general']);

$title = "Ajouter un gestionnaire";
include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Ajouter un admin gestionnaire</h1>
    <a class="btn btn-outline-secondary" href="index.php">Retour</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="../controllers/admin_controller.php" id="add-gestionnaire-form" onsubmit="return validateForm('add-gestionnaire-form')">
        <input type="hidden" name="action" value="create_gestionnaire">
        <?= csrf_field() ?>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nom complet *</label>
            <input class="form-control" name="nom_complet" minlength="3" maxlength="100" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Téléphone (format CI)</label>
            <input class="form-control" name="telephone" placeholder="+2250700000000">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email *</label>
            <input class="form-control" type="email" name="email" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Mot de passe *</label>
            <input class="form-control" type="password" name="mot_de_passe" minlength="8" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Confirmation *</label>
            <input class="form-control" type="password" name="mot_de_passe_confirm" minlength="8" required>
          </div>
        </div>

        <div class="mt-3">
          <button class="btn btn-primary">Créer le gestionnaire</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


