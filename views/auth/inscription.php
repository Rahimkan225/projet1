<?php
require_once __DIR__ . '/../../config/init.php';

if (!empty($_SESSION['user_id'])) {
    $redirect = (($_SESSION['type'] ?? '') === 'patient') ? 'dashboard-patient.php' : 'dashboard-donateur.php';
    header("Location: {$redirect}");
    exit;
}

$title = "Inscription";
include __DIR__ . '/../layout/header.php';
?>

<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h1 class="h4 mb-3 text-center">Créer un compte</h1>
          <form action="controllers/auth_controller.php" method="POST" id="register-form" onsubmit="return validateForm('register-form')">
            <input type="hidden" name="action" value="register">
            <?= csrf_field() ?>

            <div class="mb-3">
              <label class="form-label">Nom complet *</label>
              <input type="text" name="nom_complet" class="form-control" minlength="3" maxlength="100" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email *</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Téléphone (format CI) *</label>
              <input type="text" name="telephone" class="form-control" placeholder="+2250700000000" required>
              <div class="form-text">Ex: +22507xxxxxxx</div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Mot de passe *</label>
                <input type="password" name="mot_de_passe" class="form-control" minlength="8" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Confirmation *</label>
                <input type="password" name="mot_de_passe_confirm" class="form-control" minlength="8" required>
              </div>
            </div>

            <input type="hidden" name="type" value="donateur">

            <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
          </form>

          <div class="text-center mt-3">
            <span class="text-muted">Déjà un compte ?</span> <a href="connexion.php">Se connecter</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


