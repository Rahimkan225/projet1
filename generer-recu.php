<?php
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/models/Don.php';

$donId = (int)($_GET['don_id'] ?? 0);
$don = Don::findById($donId);
if (!$don) {
    die('Reçu introuvable');
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reçu don #<?= (int)$don['id'] ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print { .no-print { display:none; } }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Reçu de don — Liens d'Espoir</h1>
      <button class="btn btn-primary no-print" onclick="window.print()">Imprimer</button>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="text-muted small">Reçu #</div>
            <div class="fw-semibold"><?= (int)$don['id'] ?></div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Référence</div>
            <div class="fw-semibold"><?= clean((string)$don['reference_don']) ?></div>
          </div>
        </div>
        <hr>
        <table class="table table-sm">
          <tr><td>Date</td><td><?= clean((string)$don['date_don']) ?></td></tr>
          <tr><td>Cagnotte</td><td><?= clean((string)$don['nom_patient']) ?></td></tr>
          <tr><td>Montant</td><td><strong><?= format_fcfa($don['montant']) ?></strong></td></tr>
        </table>
        <div class="text-muted small">Document généré en mode projet étudiant (paiement simulé).</div>
      </div>
    </div>
  </div>
</body>
</html>


