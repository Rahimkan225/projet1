<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Cagnotte.php';

$title = "À propos";
include __DIR__ . '/../layout/header.php';

// Stats dynamiques - Utiliser la méthode centralisée
$statsGlobales = Cagnotte::statsGlobales();
global $pdo;
$stats = [
    'total_collecte' => (float)($statsGlobales['total_collecte'] ?? 0),
    'nb_cagnottes' => (int)($statsGlobales['nb_cagnottes_actives'] ?? 0) + (int)($statsGlobales['nb_cagnottes_completees'] ?? 0),
    'nb_donateurs' => (int)$pdo->query("SELECT COUNT(DISTINCT COALESCE(donateur_id, email_donateur)) FROM dons")->fetchColumn(),
    'nb_patients' => (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM cagnottes")->fetchColumn(),
];
?>

<section class="hero mb-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4rem 0;">
  <div class="container text-center">
    <h1 class="display-5 fw-bold mb-3">À propos de Liens d'Espoir</h1>
    <p class="lead mb-0">Une plateforme de solidarité pour financer des soins médicaux urgents</p>
  </div>
</section>

<div class="container">
  <div class="row g-4 mb-5">
    <div class="col-lg-8 mx-auto">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <h2 class="h4 fw-bold mb-3">Notre Mission</h2>
          <p class="text-muted mb-0">
            Liens d'Espoir est une plateforme innovante qui facilite le financement de soins médicaux urgents 
            grâce à un système de cagnottes transparentes et sécurisées. Nous croyons que chaque personne 
            mérite l'accès aux soins de santé, indépendamment de sa situation financière.
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body p-4 text-center">
          <div class="mb-3" style="font-size: 3rem;">🔍</div>
          <h3 class="h5 fw-bold mb-3">Transparence Totale</h3>
          <p class="text-muted mb-0">
            Tous les documents médicaux sont vérifiés et validés. Chaque don est tracé et visible, 
            garantissant une confiance absolue dans le processus.
          </p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body p-4 text-center">
          <div class="mb-3" style="font-size: 3rem;">❤️</div>
          <h3 class="h5 fw-bold mb-3">Solidarité Communautaire</h3>
          <p class="text-muted mb-0">
            Chaque don, même le plus modeste, fait la différence. Notre communauté se mobilise 
            pour soutenir ceux qui en ont besoin.
          </p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body p-4 text-center">
          <div class="mb-3" style="font-size: 3rem;">⚡</div>
          <h3 class="h5 fw-bold mb-3">Efficacité Maximale</h3>
          <p class="text-muted mb-0">
            Processus simplifié et sécurisé : gestionnaires agréés, validation rapide, 
            et publication immédiate pour une mobilisation rapide.
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-5">
          <h2 class="h4 fw-bold mb-4 text-center">Nos Chiffres</h2>
          <div class="row g-4 text-center">
            <div class="col-md-3">
              <div class="h2 fw-bold text-primary mb-2"><?= format_fcfa($stats['total_collecte']) ?></div>
              <div class="text-muted">FCFA collectés</div>
            </div>
            <div class="col-md-3">
              <div class="h2 fw-bold text-success mb-2"><?= (int)$stats['nb_cagnottes'] ?></div>
              <div class="text-muted">Cagnottes actives</div>
            </div>
            <div class="col-md-3">
              <div class="h2 fw-bold text-info mb-2"><?= (int)$stats['nb_donateurs'] ?></div>
              <div class="text-muted">Donateurs généreux</div>
            </div>
            <div class="col-md-3">
              <div class="h2 fw-bold text-warning mb-2"><?= (int)$stats['nb_patients'] ?></div>
              <div class="text-muted">Patients aidés</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-lg-10 mx-auto">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <h2 class="h4 fw-bold mb-3">Comment Nous Fonctionnons</h2>
          <div class="row g-3">
            <div class="col-md-6">
              <h4 class="h6 fw-semibold mb-2">Pour les Patients</h4>
              <p class="text-muted small mb-0">
                Contactez un gestionnaire agréé qui vous accompagnera dans la création de votre cagnotte. 
                Vous fournissez les documents médicaux nécessaires, et le gestionnaire s'occupe du reste.
              </p>
            </div>
            <div class="col-md-6">
              <h4 class="h6 fw-semibold mb-2">Pour les Donateurs</h4>
              <p class="text-muted small mb-0">
                Parcourez les cagnottes actives, choisissez celle qui vous touche et faites un don en toute sécurité. 
                Vous pouvez suivre l'évolution de chaque cagnotte en temps réel.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


