<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/Cagnotte.php';

$title = "Cagnottes";

$filters = [
    'search' => $_GET['search'] ?? '',
    'pathologie' => $_GET['pathologie'] ?? 'all',
    'urgence' => $_GET['urgence'] ?? 'all',
    'tri' => $_GET['tri'] ?? 'recentes',
];

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$res = Cagnotte::findActiveWithStats($filters, $perPage, $offset);
$cagnottes = $res['rows'];
$totalPages = max(1, (int)ceil($res['total'] / $perPage));

include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <div class="row">
    <div class="col-lg-3 mb-3">
      <div class="card">
        <div class="card-body">
          <h2 class="h6">Filtres</h2>
          <form method="GET">
            <div class="mb-3">
              <label class="form-label">Urgence</label>
              <select class="form-select" name="urgence">
                <option value="all" <?= $filters['urgence']==='all'?'selected':'' ?>>Toutes</option>
                <option value="critique" <?= $filters['urgence']==='critique'?'selected':'' ?>>Critique</option>
                <option value="elevee" <?= $filters['urgence']==='elevee'?'selected':'' ?>>Élevée</option>
                <option value="moderee" <?= $filters['urgence']==='moderee'?'selected':'' ?>>Modérée</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Pathologie</label>
              <select class="form-select" name="pathologie">
                <option value="all" <?= $filters['pathologie']==='all'?'selected':'' ?>>Toutes</option>
                <option value="cancer" <?= $filters['pathologie']==='cancer'?'selected':'' ?>>Cancer</option>
                <option value="chirurgie" <?= $filters['pathologie']==='chirurgie'?'selected':'' ?>>Chirurgie</option>
                <option value="accident" <?= $filters['pathologie']==='accident'?'selected':'' ?>>Accident</option>
                <option value="maternite" <?= $filters['pathologie']==='maternite'?'selected':'' ?>>Maternité</option>
                <option value="autre" <?= $filters['pathologie']==='autre'?'selected':'' ?>>Autre</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Tri</label>
              <select class="form-select" name="tri">
                <option value="recentes" <?= $filters['tri']==='recentes'?'selected':'' ?>>Plus récentes</option>
                <option value="presque_completes" <?= $filters['tri']==='presque_completes'?'selected':'' ?>>Bientôt complètes</option>
                <option value="urgentes" <?= $filters['tri']==='urgentes'?'selected':'' ?>>Plus urgentes</option>
              </select>
            </div>
            <button class="btn btn-primary w-100">Appliquer</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h4 mb-1 fw-bold">Cagnottes actives</h1>
          <?php if (!empty($filters['search'])): ?>
            <p class="text-muted small mb-0">Résultats pour "<?= clean($filters['search']) ?>"</p>
          <?php endif; ?>
        </div>
        <?php if (($_SESSION['type'] ?? '') === 'admin_gestionnaire'): ?>
          <a href="gestionnaire/add-patient-cagnotte.php" class="btn btn-primary">Créer une cagnotte</a>
        <?php endif; ?>
      </div>
      <div class="row">
        <?php foreach ($cagnottes as $c): ?>
          <?php $pct = (int)($c['pourcentage'] ?? 0); ?>
          <div class="col-md-4 mb-4">
            <div class="card cagnotte-card">
              <div style="height:170px;background:#e9ecef;display:flex;align-items:center;justify-content:center;">
                <?php if (!empty($c['photo_patient']) && file_exists($c['photo_patient'])): ?>
                  <img src="<?= clean((string)$c['photo_patient']) ?>" class="w-100 h-100" style="object-fit:cover;" alt="Photo patient">
                <?php else: ?>
                  <img src="<?= clean(patient_placeholder_img((int)$c['id'])) ?>" class="w-100 h-100" style="object-fit:cover;" alt="Illustration patient">
                <?php endif; ?>
              </div>
              <div class="card-body">
                <span class="badge <?= urgence_badge_class((string)$c['urgence']) ?> text-white"><?= strtoupper(clean((string)$c['urgence'])) ?></span>
                <h2 class="h6 mt-2 mb-1"><?= clean((string)$c['nom_patient']) ?>, <?= (int)$c['age_patient'] ?> ans</h2>
                <div class="text-muted small mb-2"><?= clean((string)$c['pathologie']) ?></div>
                <p class="small mb-2"><?= clean(truncate((string)$c['diagnostic'], 120)) ?></p>
                <div class="progress mb-2">
                  <div class="progress-bar" data-percentage="<?= $pct ?>" style="width:0%"></div>
                </div>
                <div class="small">
                  <strong><?= format_fcfa($c['montant_collecte']) ?></strong> / <?= format_fcfa($c['montant_objectif']) ?>
                  <br><span class="text-muted"><?= (int)($c['nb_donateurs'] ?? 0) ?> donateurs</span>
                </div>
                <a href="cagnotte-detail.php?id=<?= (int)$c['id'] ?>" class="btn btn-primary w-100 mt-3">Voir les détails</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$cagnottes): ?>
          <div class="col-12"><div class="alert alert-info">Aucune cagnotte ne correspond à vos filtres.</div></div>
        <?php endif; ?>
      </div>

      <nav>
        <ul class="pagination">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php
              $qs = $_GET;
              $qs['page'] = $p;
              $href = 'cagnottes.php?' . http_build_query($qs);
            ?>
            <li class="page-item <?= $p===$page?'active':'' ?>"><a class="page-link" href="<?= clean($href) ?>"><?= $p ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


