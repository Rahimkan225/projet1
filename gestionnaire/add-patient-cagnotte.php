<?php
require_once __DIR__ . '/../config/init.php';

require_roles(['admin_gestionnaire', 'admin_general']);

$title = "Gestionnaire — Patient & Cagnotte";
require_once __DIR__ . '/../views/gestionnaire/add_patient_cagnotte.php';


