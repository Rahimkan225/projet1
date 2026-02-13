<?php
// Sidebar simple pour dashboards (patient / donateur / admin)
$type = (string)($_SESSION['type'] ?? '');
?>

<div class="card mb-3">
  <div class="card-body">
    <div class="fw-semibold mb-1"><?= clean((string)($_SESSION['nom_complet'] ?? '')) ?></div>
    <div class="text-muted small"><?= clean($type) ?></div>
  </div>
</div>

<div class="list-group mb-3">
  <?php if ($type === 'patient'): ?>
    <a class="list-group-item list-group-item-action" href="dashboard-patient.php">Dashboard</a>
    <a class="list-group-item list-group-item-action" href="creer-cagnotte.php">Créer une cagnotte</a>
    <a class="list-group-item list-group-item-action" href="profil.php">Profil</a>
  <?php elseif ($type === 'donateur'): ?>
    <a class="list-group-item list-group-item-action" href="dashboard-donateur.php">Dashboard</a>
    <a class="list-group-item list-group-item-action" href="cagnottes.php">Découvrir les cagnottes</a>
    <a class="list-group-item list-group-item-action" href="profil.php">Profil</a>
  <?php elseif ($type === 'admin'): ?>
    <a class="list-group-item list-group-item-action" href="admin/index.php">Dashboard Admin</a>
    <a class="list-group-item list-group-item-action" href="cagnottes.php">Cagnottes (site)</a>
    <a class="list-group-item list-group-item-action" href="profil.php">Profil</a>
  <?php endif; ?>
</div>


