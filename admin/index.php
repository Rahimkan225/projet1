<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Cagnotte.php';

require_roles(['admin_general']);

$title = "Admin — Dashboard";
$stats = Cagnotte::statsGlobales();
$pending = Cagnotte::findPendingWithStats(100);

include __DIR__ . '/../views/layout/header.php';
?>

<div class="container">
  <div class="admin-hero mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
      <div>
        <div class="small opacity-75">Espace Admin Général</div>
        <h1 class="h3 mb-0">Dashboard</h1>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-light" href="users.php">Gérer les accès</a>
        <a class="btn btn-outline-light" href="../gestionnaire/patients.php">Gérer les patients</a>
        <a class="btn btn-outline-light" href="add-gestionnaire.php">Ajouter un gestionnaire</a>
        <a class="btn btn-outline-light" href="../index.php">Retour au site</a>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card kpi-card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Total collecté</div>
            <div class="kpi-value h5 mb-0"><?= format_fcfa($stats['total_collecte'] ?? 0) ?></div>
          </div>
          <div class="kpi-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1a11 11 0 1 0 11 11A11 11 0 0 0 12 1Zm1 17.93V20h-2v-1.07A7.002 7.002 0 0 1 5.07 13H4v-2h1.07A7.002 7.002 0 0 1 11 5.07V4h2v1.07A7.002 7.002 0 0 1 18.93 11H20v2h-1.07A7.002 7.002 0 0 1 13 18.93Z"/></svg>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card kpi-card kpi-info">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Utilisateurs</div>
            <div class="kpi-value h5 mb-0" data-countup="<?= (int)($stats['nb_users'] ?? 0) ?>">0</div>
          </div>
          <div class="kpi-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4 0-8 2-8 5v1h16v-1c0-3-4-5-8-5Z"/></svg>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card kpi-card kpi-ok">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Cagnottes actives</div>
            <div class="kpi-value h5 mb-0" data-countup="<?= (int)($stats['nb_cagnottes_actives'] ?? 0) ?>">0</div>
          </div>
          <div class="kpi-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6H4V4h16ZM4 8h16v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Zm7 3v2H7v2h4v2l4-3Z"/></svg>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card kpi-card kpi-warn">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">En attente</div>
            <div class="kpi-value h5 mb-0" data-countup="<?= (int)($stats['nb_cagnottes_attente'] ?? 0) ?>">0</div>
          </div>
          <div class="kpi-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21Zm12-3h-2v-2h2Zm0-4h-2v-4h2Z"/></svg>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h2 class="h6 mb-0">Répartition des cagnottes</h2>
            <span class="text-muted small">Actives / Attente / Complétées</span>
          </div>
          <div class="mt-3">
            <canvas id="statusChart"
              data-active="<?= (int)($stats['nb_cagnottes_actives'] ?? 0) ?>"
              data-pending="<?= (int)($stats['nb_cagnottes_attente'] ?? 0) ?>"
              data-done="<?= (int)($stats['nb_cagnottes_completees'] ?? 0) ?>"
              height="220"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 mb-0">Cagnottes en attente</h2>
            <div class="input-group" style="max-width: 320px;">
              <span class="input-group-text">🔎</span>
              <input id="pendingFilter" class="form-control" placeholder="Filtrer (patient, id, urgence…)">
            </div>
          </div>
          <div class="table-responsive table-sticky" style="max-height: 420px;">
            <table class="table table-striped align-middle mb-0" id="pendingTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Patient</th>
                  <th>Créateur</th>
                  <th>Urgence</th>
                  <th>Docs</th>
                  <th>Objectif</th>
                  <th>Créée le</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pending as $c): ?>
                  <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><?= clean((string)$c['nom_patient']) ?></td>
                    <td class="text-muted small"><?= clean((string)($c['createur'] ?? '')) ?></td>
                    <td><span class="badge <?= urgence_badge_class((string)$c['urgence']) ?> text-white"><?= strtoupper(clean((string)$c['urgence'])) ?></span></td>
                    <td><?= (int)($c['nb_documents'] ?? 0) ?></td>
                    <td><?= format_fcfa($c['montant_objectif']) ?></td>
                    <td class="text-muted small"><?= clean((string)$c['date_creation']) ?></td>
                    <td>
                      <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-sm btn-outline-primary" href="../cagnotte-detail.php?id=<?= (int)$c['id'] ?>" target="_blank">Voir</a>
                        <form method="POST" action="valider-cagnotte.php" class="d-inline">
                          <?= csrf_field() ?>
                          <input type="hidden" name="cagnotte_id" value="<?= (int)$c['id'] ?>">
                          <input type="hidden" name="decision" value="approve">
                          <button class="btn btn-sm btn-success" type="submit">Approuver</button>
                        </form>
                        <form method="POST" action="valider-cagnotte.php" class="d-inline" onsubmit="return confirm('Rejeter cette cagnotte ?');">
                          <?= csrf_field() ?>
                          <input type="hidden" name="cagnotte_id" value="<?= (int)$c['id'] ?>">
                          <input type="hidden" name="decision" value="reject">
                          <button class="btn btn-sm btn-danger" type="submit">Rejeter</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (!$pending): ?>
                  <tr><td colspan="8" class="text-muted">Aucune cagnotte en attente.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="../public/js/admin-dashboard.js"></script>

<?php include __DIR__ . '/../views/layout/footer.php'; ?>


