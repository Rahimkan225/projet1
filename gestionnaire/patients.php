<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/User.php';

require_roles(['admin_gestionnaire', 'admin_general']);

$title = "Gestionnaire – Patients";
$patients = User::listPatientsDetailed(2000);

include __DIR__ . '/../views/gestionnaire/patients.php';


