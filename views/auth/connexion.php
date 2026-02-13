<?php
require_once __DIR__ . '/../../config/init.php';

if (!empty($_SESSION['user_id'])) {
    $redirect = (($_SESSION['type'] ?? '') === 'patient') ? 'dashboard-patient.php' : 'dashboard-donateur.php';
    header("Location: {$redirect}");
    exit;
}

$title = "Connexion";
include __DIR__ . '/../layout/header.php';
?>

<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h1 class="h4 mb-3 text-center">Connexion</h1>
          <form action="controllers/auth_controller.php" method="POST" id="login-form" onsubmit="return validateForm('login-form')">
            <input type="hidden" name="action" value="login">
            <?= csrf_field() ?>

            <div class="mb-3">
              <label class="form-label">Email ou Téléphone *</label>
              <input type="text" name="identifiant" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Mot de passe *</label>
              <input type="password" name="mot_de_passe" class="form-control" required>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" name="remember_me" class="form-check-input" id="remember">
              <label class="form-check-label" for="remember">Se souvenir de moi</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
          </form>

          <div class="text-center mt-3">
            <span class="text-muted">Pas encore de compte ?</span> <a href="inscription.php">S'inscrire</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


