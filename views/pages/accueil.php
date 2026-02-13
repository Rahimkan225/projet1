<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Cagnotte.php';

$title = "Accueil";
$urgentes = Cagnotte::topUrgentes(6);

// Stats simples - Utiliser la méthode centralisée
$stats = Cagnotte::statsGlobales();
$nbCagnottes = (int)($stats['nb_cagnottes_actives'] ?? 0);
$nbDonateurs = (int)$pdo->query("SELECT COUNT(DISTINCT COALESCE(donateur_id, email_donateur)) FROM dons")->fetchColumn();
$montantTotal = (float)($stats['total_collecte'] ?? 0);

include __DIR__ . '/../layout/header.php';
?>

<section class="hero mb-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8 mx-auto text-center">
        <h1 class="display-4 fw-bold mb-3">Liens d'Espoir</h1>
        <p class="lead mb-4">Soutenez des vies en finançant des soins médicaux urgents. Ensemble pour la santé.</p>
        
        <!-- Barre de recherche principale -->
        <form method="GET" action="cagnottes.php" class="mb-4">
          <div class="input-group input-group-lg shadow-lg" style="max-width: 700px; margin: 0 auto;">
            <span class="input-group-text bg-white border-0">
              <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
              </svg>
            </span>
            <input type="text" name="search" class="form-control border-0" placeholder="Rechercher une cagnotte (nom du patient, diagnostic, hôpital)..." value="<?= clean((string)($_GET['search'] ?? '')) ?>" style="font-size: 1.1rem; padding: 1rem;">
            <button type="submit" class="btn btn-primary px-4" style="border-radius: 0 8px 8px 0;">
              Rechercher
            </button>
          </div>
        </form>

        <div class="d-flex gap-2 flex-wrap justify-content-center">
          <a href="cagnottes.php" class="btn btn-light btn-lg px-4">Découvrir les cagnottes</a>
          <?php if (!empty($_SESSION['user_id']) && (($_SESSION['type'] ?? '') === 'admin_gestionnaire')): ?>
            <a href="gestionnaire/add-patient-cagnotte.php" class="btn btn-outline-light btn-lg px-4">Créer une cagnotte</a>
          <?php elseif (empty($_SESSION['user_id'])): ?>
            <a href="connexion.php" class="btn btn-outline-light btn-lg px-4">Créer une cagnotte</a>
          <?php else: ?>
            <a href="index.php" class="btn btn-outline-light btn-lg px-4" onclick="alert('Création réservée à l\\'admin gestionnaire.');return false;">Créer une cagnotte</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container py-5">
  <div class="text-center mb-4">
    <h2 class="h3 fw-bold mb-2">Comment ça marche ?</h2>
    <p class="text-muted">Un processus simple et sécurisé pour venir en aide aux patients</p>
  </div>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center p-4">
          <div class="mb-3" style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold;">1</div>
          <h3 class="h5 fw-bold mb-2">Contactez un gestionnaire</h3>
          <p class="text-muted mb-0">Prenez contact avec un gestionnaire agréé qui vous accompagnera dans la création de votre cagnotte médicale.</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center p-4">
          <div class="mb-3" style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold;">2</div>
          <h3 class="h5 fw-bold mb-2">Validation et publication</h3>
          <p class="text-muted mb-0">Le gestionnaire crée votre cagnotte avec vos documents médicaux. Après validation par l'admin, elle est publiée.</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center p-4">
          <div class="mb-3" style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold;">3</div>
          <h3 class="h5 fw-bold mb-2">Recevez des dons</h3>
          <p class="text-muted mb-0">La communauté se mobilise pour vous soutenir. Chaque don vous rapproche de votre objectif de soins.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="h3 fw-bold mb-2">Cagnottes urgentes</h2>
      <p class="text-muted mb-0">Des vies qui ont besoin de votre soutien</p>
    </div>
    <a class="btn btn-outline-primary" href="cagnottes.php">Voir toutes les cagnottes →</a>
  </div>
  <div class="row g-4">
    <?php foreach ($urgentes as $c): ?>
      <?php $pct = (int)($c['pourcentage'] ?? 0); ?>
      <div class="col-md-4">
        <div class="card cagnotte-card h-100">
          <div style="height:200px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
            <?php if (!empty($c['photo_patient']) && file_exists($c['photo_patient'])): ?>
              <img src="<?= clean($c['photo_patient']) ?>" class="w-100 h-100" style="object-fit:cover;" alt="Photo patient">
            <?php else: ?>
              <img src="<?= clean(patient_placeholder_img((int)$c['id'])) ?>" class="w-100 h-100" style="object-fit:cover;" alt="Illustration patient">
            <?php endif; ?>
            <div style="position:absolute;top:1rem;right:1rem;">
              <span class="badge <?= urgence_badge_class((string)$c['urgence']) ?> text-white shadow-lg"><?= strtoupper(clean((string)$c['urgence'])) ?></span>
            </div>
          </div>
          <div class="card-body p-4">
            <h3 class="h5 fw-bold mb-2"><?= clean((string)$c['nom_patient']) ?>, <?= (int)$c['age_patient'] ?> ans</h3>
            <div class="text-muted small mb-3 d-flex align-items-center">
              <span class="me-2">🏥</span>
              <span><?= clean((string)$c['hopital']) ?></span>
            </div>
            <p class="mb-3 text-muted" style="font-size:0.9rem;line-height:1.6;"><?= clean(truncate((string)$c['diagnostic'], 100)) ?></p>
            <div class="progress mb-3" style="height:8px;">
              <div class="progress-bar" role="progressbar" data-percentage="<?= $pct ?>" style="width:0%"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <div class="fw-bold text-primary"><?= format_fcfa($c['montant_collecte']) ?></div>
                <div class="text-muted small">sur <?= format_fcfa($c['montant_objectif']) ?></div>
              </div>
              <div class="text-end">
                <div class="fw-semibold"><?= $pct ?>%</div>
                <div class="text-muted small">complété</div>
              </div>
            </div>
            <a href="cagnotte-detail.php?id=<?= (int)$c['id'] ?>" class="btn btn-primary w-100">Voir les détails</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="stats-section">
  <div class="text-center mb-4">
    <h2 class="section-title">Notre Impact</h2>
    <p class="section-subtitle">Des chiffres qui témoignent de notre engagement</p>
  </div>
  <div class="row g-4 text-center">
    <div class="col-md-4">
      <div class="stats-card animate-fade-in-up">
        <div class="mb-3" style="font-size: 3rem; color: var(--primary);">💰</div>
        <div class="h2 fw-bold mb-2" style="color: var(--primary);"><?= format_fcfa($montantTotal) ?></div>
        <div class="text-muted fw-semibold">FCFA collectés</div>
        <div class="small text-muted mt-2">Grâce à votre générosité</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stats-card animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="mb-3" style="font-size: 3rem; color: var(--success);">❤️</div>
        <div class="h2 fw-bold mb-2" style="color: var(--success);"><?= (int)$nbDonateurs ?></div>
        <div class="text-muted fw-semibold">Donateurs généreux</div>
        <div class="small text-muted mt-2">Une communauté solidaire</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stats-card animate-fade-in-up" style="animation-delay: 0.2s;">
        <div class="mb-3" style="font-size: 3rem; color: var(--info);">🎯</div>
        <div class="h2 fw-bold mb-2" style="color: var(--info);"><?= (int)$nbCagnottes ?></div>
        <div class="text-muted fw-semibold">Cagnottes actives</div>
        <div class="small text-muted mt-2">En cours de financement</div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>


