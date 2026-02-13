<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/User.php';

require_roles(['admin_gestionnaire', 'admin_general']);

$title = $title ?? "Gestionnaire – Patients";
include __DIR__ . '/../layout/header.php';
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Patients</h1>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="add-patient-cagnotte.php">Nouveau patient + cagnotte</a>
      <?php if (($_SESSION['type'] ?? '') === 'admin_general'): ?>
        <a class="btn btn-outline-secondary" href="../admin/index.php">Dashboard Admin</a>
      <?php endif; ?>
      <a class="btn btn-outline-secondary" href="../index.php">Retour au site</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="input-group">
        <span class="input-group-text">🔍</span>
        <input type="text" id="patientSearch" class="form-control" placeholder="Rechercher un patient (nom, email, téléphone, ID)..." autocomplete="off">
        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('patientSearch').value=''; filterPatients();">Effacer</button>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0" id="patientsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom complet</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Inscription</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($patients ?? []) as $p): ?>
              <tr class="patient-row" data-search="<?= strtolower(clean((string)$p['nom_complet'] . ' ' . $p['email'] . ' ' . ($p['telephone'] ?? '') . ' ' . $p['id'])) ?>">
              <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= clean((string)$p['nom_complet']) ?></td>
                <td class="text-muted small"><?= clean((string)$p['email']) ?></td>
                <td class="text-muted small"><?= clean((string)($p['telephone'] ?? '')) ?></td>
                <td class="text-muted small"><?= clean((string)$p['date_inscription']) ?></td>
                <td>
                  <form class="row g-2 align-items-center" method="POST" action="../controllers/gestionnaire_controller.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_patient">
                    <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                    <div class="col-md-3">
                      <input type="text" name="nom_complet" class="form-control form-control-sm"
                             value="<?= clean((string)$p['nom_complet']) ?>" placeholder="Nom complet">
                    </div>
                    <div class="col-md-3">
                      <input type="email" name="email" class="form-control form-control-sm"
                             value="<?= clean((string)$p['email']) ?>" placeholder="Email">
                    </div>
                    <div class="col-md-3">
                      <input type="text" name="telephone" class="form-control form-control-sm"
                             value="<?= clean((string)($p['telephone'] ?? '')) ?>" placeholder="Téléphone">
                    </div>
                    <div class="col-md-3 d-grid">
                      <button class="btn btn-sm btn-primary" type="submit">Enregistrer</button>
                    </div>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($patients)): ?>
              <tr><td colspan="6" class="text-muted">Aucun patient pour l’instant.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="small text-muted mt-2">
        Pour ajouter des justificatifs médicaux, ouvrez la cagnotte du patient puis utilisez le formulaire d'ajout de documents.
      </div>
    </div>
  </div>
</div>

<script>
function filterPatients() {
  const search = document.getElementById('patientSearch').value.toLowerCase().trim();
  const rows = document.querySelectorAll('#patientsTable .patient-row');
  let visibleCount = 0;
  
  rows.forEach(row => {
    const searchText = row.getAttribute('data-search') || '';
    if (search === '' || searchText.includes(search)) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  // Afficher un message si aucun résultat
  let noResultsRow = document.getElementById('no-results');
  if (noResultsRow) noResultsRow.remove();
  
  if (visibleCount === 0 && search !== '') {
    const tbody = document.querySelector('#patientsTable tbody');
    const tr = document.createElement('tr');
    tr.id = 'no-results';
    tr.innerHTML = '<td colspan="6" class="text-center text-muted py-4">Aucun patient trouvé pour "' + search + '"</td>';
    tbody.appendChild(tr);
  }
}

document.getElementById('patientSearch').addEventListener('input', filterPatients);
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>


