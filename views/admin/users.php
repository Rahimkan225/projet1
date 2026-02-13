<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../models/User.php';

require_roles(['admin_general']);

$title = $title ?? "Admin — Utilisateurs";
include __DIR__ . '/../layout/header.php';

function badgeRole(string $role): string {
    return match ($role) {
        'admin_general' => 'bg-dark',
        'admin_gestionnaire' => 'bg-primary',
        'patient' => 'bg-success',
        'donateur' => 'bg-secondary',
        default => 'bg-light text-dark',
    };
}
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Gestion des utilisateurs</h1>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="add-gestionnaire.php">Ajouter un gestionnaire</a>
      <a class="btn btn-outline-secondary" href="index.php">Dashboard</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Rôle</th>
              <th>Actif</th>
              <th>Créé par</th>
              <th>Cagnottes</th>
              <th>Dons</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($users ?? []) as $u): ?>
              <?php
                $role = (string)($u['type'] ?? '');
                $isActive = (int)($u['is_active'] ?? 1);
              ?>
              <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= clean((string)$u['nom_complet']) ?></td>
                <td class="text-muted small"><?= clean((string)$u['email']) ?></td>
                <td class="text-muted small"><?= clean((string)($u['telephone'] ?? '')) ?></td>
                <td><span class="badge <?= badgeRole($role) ?>"><?= clean($role) ?></span></td>
                <td>
                  <?php if ($isActive): ?>
                    <span class="badge bg-success">oui</span>
                  <?php else: ?>
                    <span class="badge bg-danger">non</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted small"><?= (int)($u['created_by'] ?? 0) ?: '-' ?></td>
                <td><?= (int)($u['nb_cagnottes'] ?? 0) ?></td>
                <td><?= (int)($u['nb_dons'] ?? 0) ?></td>
                <td>
                  <div class="d-flex flex-wrap gap-2">
                    <?php if ($role !== 'admin_general'): ?>
                      <form method="POST" action="../controllers/admin_controller.php" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="set_role">
                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                          <option value="" selected disabled>Changer rôle…</option>
                          <option value="donateur">donateur</option>
                          <option value="patient">patient</option>
                          <option value="admin_gestionnaire">admin_gestionnaire</option>
                        </select>
                      </form>

                      <form method="POST" action="../controllers/admin_controller.php" class="d-inline" onsubmit="return confirm('Confirmer la modification ?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="set_active">
                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                        <input type="hidden" name="is_active" value="<?= $isActive ? 0 : 1 ?>">
                        <button class="btn btn-sm <?= $isActive ? 'btn-outline-danger' : 'btn-outline-success' ?>" type="submit">
                          <?= $isActive ? 'Désactiver' : 'Activer' ?>
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted small">—</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
              <tr><td colspan="10" class="text-muted">Aucun utilisateur.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="small text-muted mt-2">
        Note: si tu n’as pas encore la colonne <code>is_active</code> en BDD, le bouton Activer/Désactiver affichera une erreur de migration.
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>


