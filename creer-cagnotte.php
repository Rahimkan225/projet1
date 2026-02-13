<?php
require_once __DIR__ . '/config/init.php';

// V2: la création est gérée par l'admin gestionnaire (wizard)
if (empty($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

if (($_SESSION['type'] ?? '') === 'admin_gestionnaire') {
    header('Location: gestionnaire/add-patient-cagnotte.php');
    exit;
}

// Les patients ne peuvent pas créer eux-mêmes
flash_set('error', "Création de cagnotte: réservée à l'administrateur gestionnaire.");
header('Location: index.php');
exit;


