<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Cagnotte.php';
require_once __DIR__ . '/../../models/Don.php';

require_login();
$me = User::findById((int)$_SESSION['user_id']);
if (!$me) {
    session_destroy();
    header('Location: connexion.php');
    exit;
}

$title = "Mon Profil";
include __DIR__ . '/../layout/header.php';

$type = (string)($me['type'] ?? 'donateur');
$cagnottes = [];
$donStats = null;
$donHistory = [];

if ($type === 'patient') {
    $cagnottes = Cagnotte::findByUser((int)$me['id']);
} elseif ($type === 'donateur') {
    $donStats = Don::statsByDonateur((int)$me['id']);
    $donHistory = Don::historyByDonateur((int)$me['id'], 20);
}

// Calculer l'âge si date de naissance disponible
$age = null;
if (!empty($me['date_naissance'])) {
    $birthDate = new DateTime($me['date_naissance']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
}

// Badge de type
$badgeClass = match($type) {
    'admin_general' => 'bg-dark',
    'admin_gestionnaire' => 'bg-primary',
    'patient' => 'bg-success',
    'donateur' => 'bg-info',
    default => 'bg-secondary'
};
?>

<div class="container py-4">
  <!-- En-tête du profil -->
  <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-4 text-white">
      <div class="row align-items-center">
        <div class="col-auto">
          <div style="width:120px;height:120px;border-radius:50%;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center;border:4px solid rgba(255,255,255,0.3);">
            <?php if (!empty($me['photo_profil']) && file_exists((string)$me['photo_profil'])): ?>
              <img src="<?= clean((string)$me['photo_profil']) ?>" alt="Profil" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <div style="font-size:3rem;color:#667eea;">👤</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col">
          <h1 class="h3 mb-2 fw-bold"><?= clean((string)$me['nom_complet']) ?></h1>
          <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            <span class="badge <?= $badgeClass ?> text-white"><?= ucfirst(str_replace('_', ' ', $type)) ?></span>
            <?php if ($age !== null): ?>
              <span class="text-white-50">• <?= $age ?> ans</span>
            <?php endif; ?>
            <?php if (!empty($me['ville'])): ?>
              <span class="text-white-50">• 📍 <?= clean((string)$me['ville']) ?></span>
            <?php endif; ?>
          </div>
          <?php if (!empty($me['bio'])): ?>
            <p class="mb-0 text-white-50"><?= clean((string)$me['bio']) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Sidebar -->
    <div class="col-lg-4">
      <!-- Informations de contact -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <h3 class="h6 fw-bold mb-3">Informations de contact</h3>
          <div class="mb-2">
            <small class="text-muted d-block">Email</small>
            <div class="fw-semibold"><?= clean((string)$me['email']) ?></div>
          </div>
          <?php if (!empty($me['telephone'])): ?>
            <div class="mb-2">
              <small class="text-muted d-block">Téléphone</small>
              <div class="fw-semibold"><?= clean((string)$me['telephone']) ?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($me['adresse'])): ?>
            <div class="mb-2">
              <small class="text-muted d-block">Adresse</small>
              <div class="fw-semibold"><?= clean((string)$me['adresse']) ?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($me['ville']) || !empty($me['pays'])): ?>
            <div class="mb-2">
              <small class="text-muted d-block">Localisation</small>
              <div class="fw-semibold">
                <?php if (!empty($me['ville'])): ?>
                  <?= clean((string)$me['ville']) ?>
                <?php endif; ?>
                <?php if (!empty($me['ville']) && !empty($me['pays'])): ?>, <?php endif; ?>
                <?php if (!empty($me['pays'])): ?>
                  <?= clean((string)$me['pays']) ?>
                <?php endif; ?>
                <?php if (!empty($me['code_postal'])): ?>
                  (<?= clean((string)$me['code_postal']) ?>)
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($me['site_web'])): ?>
            <div class="mb-2">
              <small class="text-muted d-block">Site web</small>
              <a href="<?= clean((string)$me['site_web']) ?>" target="_blank" class="text-decoration-none"><?= clean((string)$me['site_web']) ?></a>
            </div>
          <?php endif; ?>
          <?php if (!empty($me['date_inscription'])): ?>
            <div class="mb-0">
              <small class="text-muted d-block">Membre depuis</small>
              <div class="fw-semibold"><?= date('d/m/Y', strtotime($me['date_inscription'])) ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Photo de profil -->
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h3 class="h6 fw-bold mb-3">Photo de profil</h3>
          <form action="controllers/auth_controller.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_photo">
            <?= csrf_field() ?>
            <input type="file" class="form-control mb-2" name="photo_profil" accept="image/png,image/jpeg" required>
            <button class="btn btn-primary w-100">Mettre à jour</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="col-lg-8">
      <!-- Navigation par onglets -->
      <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Informations</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">Mot de passe</button>
        </li>
        <?php if ($type === 'patient' || $type === 'donateur'): ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">Activité</button>
          </li>
        <?php endif; ?>
      </ul>

      <div class="tab-content">
        <!-- Onglet Informations -->
        <div class="tab-pane fade show active" id="profile" role="tabpanel">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h2 class="h5 fw-bold mb-4">Modifier mes informations</h2>
              <form action="controllers/auth_controller.php" method="POST" id="profile-form" onsubmit="return validateForm('profile-form')">
                <input type="hidden" name="action" value="update_profile">
                <?= csrf_field() ?>
                
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom complet *</label>
                    <input class="form-control" name="nom_complet" value="<?= clean((string)$me['nom_complet']) ?>" required minlength="3" maxlength="100">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de naissance</label>
                    <input class="form-control" type="date" name="date_naissance" value="<?= clean((string)($me['date_naissance'] ?? '')) ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Email *</label>
                    <input class="form-control" type="email" name="email" value="<?= clean((string)$me['email']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Téléphone</label>
                    <input class="form-control" name="telephone" value="<?= clean((string)($me['telephone'] ?? '')) ?>" placeholder="+225 XX XX XX XX">
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Adresse</label>
                    <input class="form-control" name="adresse" value="<?= clean((string)($me['adresse'] ?? '')) ?>" placeholder="Rue, numéro">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Ville</label>
                    <input class="form-control" name="ville" value="<?= clean((string)($me['ville'] ?? '')) ?>" placeholder="Abidjan">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Code postal</label>
                    <input class="form-control" name="code_postal" value="<?= clean((string)($me['code_postal'] ?? '')) ?>" placeholder="01 BP 1234">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Pays</label>
                    <input class="form-control" name="pays" value="<?= clean((string)($me['pays'] ?? 'Côte d\'Ivoire')) ?>" placeholder="Côte d'Ivoire">
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Site web</label>
                    <input class="form-control" type="url" name="site_web" value="<?= clean((string)($me['site_web'] ?? '')) ?>" placeholder="https://example.com">
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Biographie</label>
                    <textarea class="form-control" name="bio" rows="4" maxlength="500" placeholder="Parlez-nous de vous..."><?= clean((string)($me['bio'] ?? '')) ?></textarea>
                    <small class="text-muted">Maximum 500 caractères</small>
                  </div>
                </div>
                <div class="mt-4">
                  <button class="btn btn-primary px-4">Enregistrer les modifications</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Onglet Mot de passe -->
        <div class="tab-pane fade" id="password" role="tabpanel">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h2 class="h5 fw-bold mb-4">Changer mon mot de passe</h2>
              <form action="controllers/auth_controller.php" method="POST" id="pwd-form" onsubmit="return validateForm('pwd-form')">
                <input type="hidden" name="action" value="change_password">
                <?= csrf_field() ?>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label fw-semibold">Ancien mot de passe *</label>
                    <input class="form-control" type="password" name="ancien_mot_de_passe" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Nouveau mot de passe *</label>
                    <input class="form-control" type="password" name="nouveau_mot_de_passe" required minlength="8">
                    <small class="text-muted">Minimum 8 caractères avec au moins 1 chiffre</small>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirmer le mot de passe *</label>
                    <input class="form-control" type="password" name="nouveau_mot_de_passe_confirm" required minlength="8">
                  </div>
                </div>
                <div class="mt-4">
                  <button class="btn btn-outline-primary px-4">Mettre à jour le mot de passe</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Onglet Activité -->
        <?php if ($type === 'patient'): ?>
          <div class="tab-pane fade" id="activity" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h2 class="h5 fw-bold mb-0">Mes cagnottes</h2>
              <a class="btn btn-primary" href="creer-cagnotte.php">Créer une nouvelle cagnotte</a>
            </div>
            <div class="card border-0 shadow-sm">
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Patient</th>
                        <th>Statut</th>
                        <th>Progression</th>
                        <th>Montant</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($cagnottes as $c): ?>
                        <tr>
                          <td class="fw-semibold"><?= clean((string)$c['nom_patient']) ?></td>
                          <td>
                            <?php
                            $statutClass = match($c['statut']) {
                                'active' => 'bg-success',
                                'completee' => 'bg-primary',
                                'en_attente' => 'bg-warning',
                                'rejetee' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $statutClass ?> text-white"><?= ucfirst(str_replace('_', ' ', $c['statut'])) ?></span>
                          </td>
                          <td>
                            <div class="progress" style="height: 20px; width: 100px;">
                              <div class="progress-bar" role="progressbar" style="width: <?= min(100, (int)($c['pourcentage'] ?? 0)) ?>%"><?= (int)($c['pourcentage'] ?? 0) ?>%</div>
                            </div>
                          </td>
                          <td class="fw-semibold"><?= format_fcfa($c['montant_collecte']) ?> / <?= format_fcfa($c['montant_objectif']) ?></td>
                          <td><a class="btn btn-sm btn-outline-primary" href="cagnotte-detail.php?id=<?= (int)$c['id'] ?>">Voir</a></td>
                        </tr>
                      <?php endforeach; ?>
                      <?php if (!$cagnottes): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune cagnotte pour l'instant.</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php elseif ($type === 'donateur'): ?>
          <div class="tab-pane fade" id="activity" role="tabpanel">
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                  <div class="card-body text-center">
                    <div class="h3 fw-bold text-primary mb-1"><?= format_fcfa($donStats['total_donne'] ?? 0) ?></div>
                    <div class="text-muted small">Total donné</div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                  <div class="card-body text-center">
                    <div class="h3 fw-bold text-success mb-1"><?= (int)($donStats['nb_dons'] ?? 0) ?></div>
                    <div class="text-muted small">Nombre de dons</div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                  <div class="card-body text-center">
                    <div class="h3 fw-bold text-info mb-1"><?= (int)($donStats['nb_cagnottes'] ?? 0) ?></div>
                    <div class="text-muted small">Cagnottes aidées</div>
                  </div>
                </div>
              </div>
            </div>

            <h3 class="h5 fw-bold mb-3">Historique des dons</h3>
            <div class="card border-0 shadow-sm">
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Date</th>
                        <th>Cagnotte</th>
                        <th class="text-end">Montant</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($donHistory as $d): ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($d['date_don'])) ?></td>
                          <td class="fw-semibold"><?= clean((string)$d['nom_patient']) ?></td>
                          <td class="text-end fw-bold text-success"><?= format_fcfa($d['montant']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                      <?php if (!$donHistory): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Aucun don pour l'instant.</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
