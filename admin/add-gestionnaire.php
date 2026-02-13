<?php
require_once __DIR__ . '/../config/init.php';

require_roles(['admin_general']);

$title = "Admin — Ajouter un gestionnaire";
include __DIR__ . '/../views/admin/add_gestionnaire.php';


