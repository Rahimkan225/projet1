<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Cagnotte.php';

require_roles(['admin_general']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verify_csrf();

$cagnotteId = (int)($_POST['cagnotte_id'] ?? 0);
$decision = (string)($_POST['decision'] ?? '');

if ($cagnotteId <= 0) {
    flash_set('error', "Cagnotte invalide.");
    header('Location: index.php');
    exit;
}

try {
    if ($decision === 'approve') {
        Cagnotte::setStatut($cagnotteId, 'active');
        flash_set('success', "Cagnotte #{$cagnotteId} approuvée.");
    } elseif ($decision === 'reject') {
        Cagnotte::setStatut($cagnotteId, 'rejetee');
        flash_set('success', "Cagnotte #{$cagnotteId} rejetée.");
    } else {
        flash_set('error', "Décision invalide.");
    }
} catch (Throwable $e) {
    error_log("Admin validation erreur: " . $e->getMessage());
    flash_set('error', "Erreur lors de la mise à jour du statut.");
}

header('Location: index.php');
exit;


