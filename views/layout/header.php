<?php
require_once __DIR__ . '/../../config/init.php';
$title = $title ?? 'Liens d\'Espoir';

// Ajuster les chemins si on est dans /admin, /gestionnaire ou /dons
$isAdminLayout = !empty($isAdminLayout)
    || (isset($_SERVER['SCRIPT_NAME']) && (
        str_contains((string)$_SERVER['SCRIPT_NAME'], '/admin/') 
        || str_contains((string)$_SERVER['SCRIPT_NAME'], '/gestionnaire/')
        || str_contains((string)$_SERVER['SCRIPT_NAME'], '/dons/')
    ));
$base = $isAdminLayout ? '../' : '';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= clean($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= clean($base) ?>public/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-md">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= clean($base) ?>index.php">
      <span style="font-size: 1.75rem; letter-spacing: -0.02em;">Liens d'Espoir</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>cagnottes.php">Cagnottes</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>a-propos.php">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>contact.php">Contact</a></li>
      </ul>
      <ul class="navbar-nav">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>profil.php">Profil</a></li>
          <?php if (($_SESSION['type'] ?? '') === 'patient'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>dashboard-patient.php">Dashboard</a></li>
          <?php elseif (($_SESSION['type'] ?? '') === 'donateur'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>dashboard-donateur.php">Dashboard</a></li>
          <?php elseif (($_SESSION['type'] ?? '') === 'admin_general'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>admin/index.php">Admin</a></li>
          <?php elseif (($_SESSION['type'] ?? '') === 'admin_gestionnaire'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>gestionnaire/add-patient-cagnotte.php">Gestionnaire</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>deconnexion.php">Déconnexion</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>connexion.php">Connexion</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= clean($base) ?>inscription.php">Inscription</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="py-4">
  <div class="container">
    <?php if ($msg = flash_get('success')): ?>
      <div class="alert alert-success"><?= clean($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash_get('error')): ?>
      <div class="alert alert-danger"><?= clean($msg) ?></div>
    <?php endif; ?>
  </div>


