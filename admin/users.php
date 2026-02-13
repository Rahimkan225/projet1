<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/User.php';

require_roles(['admin_general']);

$title = "Admin — Utilisateurs";
$users = User::listForAdminGeneral(500);

include __DIR__ . '/../views/admin/users.php';


