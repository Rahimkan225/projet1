<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create_gestionnaire') {
    verify_csrf();
    require_roles(['admin_general']);

    $nom = clean((string)($_POST['nom_complet'] ?? ''));
    $email = clean((string)($_POST['email'] ?? ''));
    $telephone = clean((string)($_POST['telephone'] ?? ''));
    $password = (string)($_POST['mot_de_passe'] ?? '');
    $password2 = (string)($_POST['mot_de_passe_confirm'] ?? '');

    $errors = [];
    if (mb_strlen($nom) < 3 || mb_strlen($nom) > 100) $errors[] = "Nom complet invalide (3-100 caractères).";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if ($telephone !== '' && !preg_match('/^(?:\+225)?\s?(?:0)?\d{8,10}$/', $telephone)) $errors[] = "Téléphone invalide.";
    if (strlen($password) < 8 || !preg_match('/\d/', $password)) $errors[] = "Mot de passe: min 8 caractères et au moins 1 chiffre.";
    if ($password !== $password2) $errors[] = "Confirmation du mot de passe incorrecte.";
    if (User::existsEmail($email)) $errors[] = "Cet email est déjà utilisé.";
    if ($telephone !== '' && User::existsTelephone($telephone)) $errors[] = "Ce téléphone est déjà utilisé.";

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        header('Location: ../admin/add-gestionnaire.php');
        exit;
    }

    User::createByHierarchy([
        'nom_complet' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'mot_de_passe' => $password,
        'type' => 'admin_gestionnaire',
        'created_by' => (int)$_SESSION['user_id'],
    ]);

    flash_set('success', "Gestionnaire créé avec succès.");
    header('Location: ../admin/index.php');
    exit;
}

if ($action === 'set_role') {
    verify_csrf();
    require_roles(['admin_general']);

    $userId = (int)($_POST['user_id'] ?? 0);
    $role = (string)($_POST['role'] ?? '');

    try {
        User::adminGeneralSetRole($userId, $role, (int)$_SESSION['user_id']);
        flash_set('success', "Rôle mis à jour.");
    } catch (Throwable $e) {
        flash_set('error', $e->getMessage());
    }
    header('Location: ../admin/users.php');
    exit;
}

if ($action === 'set_active') {
    verify_csrf();
    require_roles(['admin_general']);

    $userId = (int)($_POST['user_id'] ?? 0);
    $isActive = (int)($_POST['is_active'] ?? 1);

    try {
        User::adminGeneralSetActive($userId, $isActive, (int)$_SESSION['user_id']);
        flash_set('success', "Statut mis à jour.");
    } catch (Throwable $e) {
        flash_set('error', $e->getMessage());
    }
    header('Location: ../admin/users.php');
    exit;
}

header('Location: ../index.php');
exit;


