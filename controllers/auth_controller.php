<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    verify_csrf();

    $nom = clean((string)($_POST['nom_complet'] ?? ''));
    $email = clean((string)($_POST['email'] ?? ''));
    $telephone = clean((string)($_POST['telephone'] ?? ''));
    $password = (string)($_POST['mot_de_passe'] ?? '');
    $password2 = (string)($_POST['mot_de_passe_confirm'] ?? '');
    // V2: création patient/gestionnaires interdite via inscription publique
    $type = 'donateur';

    $errors = [];
    if (mb_strlen($nom) < 3 || mb_strlen($nom) > 100) $errors[] = "Nom complet invalide (3-100 caractères).";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if ($telephone !== '' && !preg_match('/^(?:\+225)?\s?(?:0)?\d{8,10}$/', $telephone)) $errors[] = "Téléphone invalide.";
    if (strlen($password) < 8 || !preg_match('/\d/', $password)) $errors[] = "Mot de passe: min 8 caractères et au moins 1 chiffre.";
    if ($password !== $password2) $errors[] = "Confirmation du mot de passe incorrecte.";
    if (!in_array($type, ['donateur'], true)) $errors[] = "Type de compte invalide.";

    if (User::existsEmail($email)) $errors[] = "Cet email est déjà utilisé.";
    if ($telephone !== '' && User::existsTelephone($telephone)) $errors[] = "Ce téléphone est déjà utilisé.";

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        header('Location: ../inscription.php');
        exit;
    }

    $userId = User::create([
        'nom_complet' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'mot_de_passe' => $password,
        'type' => $type,
    ]);

    $_SESSION['user_id'] = $userId;
    $_SESSION['nom_complet'] = $nom;
    $_SESSION['email'] = $email;
    $_SESSION['type'] = $type;

    $redirect = 'dashboard-donateur.php';
    header("Location: ../{$redirect}");
    exit;
}

if ($action === 'login') {
    verify_csrf();
    login_throttle_check();

    $identifiant = clean((string)($_POST['identifiant'] ?? ''));
    $password = (string)($_POST['mot_de_passe'] ?? '');

    $user = User::findByEmailOrPhone($identifiant);
    if ($user && password_verify($password, (string)$user['mot_de_passe'])) {
        login_throttle_reset();

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['nom_complet'] = (string)$user['nom_complet'];
        $_SESSION['email'] = (string)$user['email'];
        $_SESSION['type'] = (string)$user['type'];

        if (!empty($_POST['remember_me'])) {
            $token = bin2hex(random_bytes(32));
            // Mode étudiant: cookie seulement (en prod: stocker token hashé en BDD)
            setcookie('remember_token', $token, [
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'secure' => false, // true si HTTPS
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        if ($user['type'] === 'admin_general') {
            header('Location: ../admin/index.php');
            exit;
        }
        if ($user['type'] === 'admin_gestionnaire') {
            header('Location: ../gestionnaire/add-patient-cagnotte.php');
            exit;
        }

        $redirect = ($user['type'] === 'patient') ? 'dashboard-patient.php' : 'dashboard-donateur.php';
        header("Location: ../{$redirect}");
        exit;
    }

    login_throttle_fail();
    flash_set('error', "Identifiants incorrects.");
    header('Location: ../connexion.php');
    exit;
}

if ($action === 'update_profile') {
    verify_csrf();
    require_login();

    $nom = clean((string)($_POST['nom_complet'] ?? ''));
    $email = clean((string)($_POST['email'] ?? ''));
    $telephone = clean((string)($_POST['telephone'] ?? ''));
    $adresse = clean((string)($_POST['adresse'] ?? ''));
    $ville = clean((string)($_POST['ville'] ?? ''));
    $pays = clean((string)($_POST['pays'] ?? ''));
    $code_postal = clean((string)($_POST['code_postal'] ?? ''));
    $date_naissance = (string)($_POST['date_naissance'] ?? '');
    $bio = clean((string)($_POST['bio'] ?? ''));
    $site_web = clean((string)($_POST['site_web'] ?? ''));

    $errors = [];
    if (mb_strlen($nom) < 3 || mb_strlen($nom) > 100) $errors[] = "Nom complet invalide (3-100 caractères).";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if ($telephone !== '' && !preg_match('/^(?:\+225)?\s?(?:0)?\d{8,10}$/', $telephone)) $errors[] = "Téléphone invalide.";
    if ($date_naissance !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_naissance)) $errors[] = "Date de naissance invalide (format: AAAA-MM-JJ).";
    if ($site_web !== '' && !filter_var($site_web, FILTER_VALIDATE_URL)) $errors[] = "URL du site web invalide.";
    if (mb_strlen($bio) > 500) $errors[] = "Biographie trop longue (max 500 caractères).";

    $me = User::findById((int)$_SESSION['user_id']);
    if (!$me) {
        session_destroy();
        header('Location: ../connexion.php');
        exit;
    }

    if ($email !== $me['email'] && User::existsEmail($email)) $errors[] = "Cet email est déjà utilisé.";
    if ($telephone !== '' && $telephone !== ($me['telephone'] ?? '') && User::existsTelephone($telephone)) $errors[] = "Ce téléphone est déjà utilisé.";

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        header('Location: ../profil.php');
        exit;
    }

    User::updateProfile((int)$me['id'], [
        'nom_complet' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'adresse' => $adresse ?: null,
        'ville' => $ville ?: null,
        'pays' => $pays ?: null,
        'code_postal' => $code_postal ?: null,
        'date_naissance' => $date_naissance ?: null,
        'bio' => $bio ?: null,
        'site_web' => $site_web ?: null,
    ]);

    $_SESSION['nom_complet'] = $nom;
    $_SESSION['email'] = $email;
    flash_set('success', "Profil mis à jour.");
    header('Location: ../profil.php');
    exit;
}

if ($action === 'update_photo') {
    verify_csrf();
    require_login();

    $res = upload_profile_photo($_FILES['photo_profil'] ?? []);
    if (!empty($res['error'])) {
        flash_set('error', (string)$res['error']);
        header('Location: ../profil.php');
        exit;
    }

    User::updatePhoto((int)$_SESSION['user_id'], (string)$res['path']);
    flash_set('success', "Photo de profil mise à jour.");
    header('Location: ../profil.php');
    exit;
}

if ($action === 'change_password') {
    verify_csrf();
    require_login();

    $old = (string)($_POST['ancien_mot_de_passe'] ?? '');
    $new = (string)($_POST['nouveau_mot_de_passe'] ?? '');
    $new2 = (string)($_POST['nouveau_mot_de_passe_confirm'] ?? '');

    $me = User::findById((int)$_SESSION['user_id']);
    if (!$me || !password_verify($old, (string)$me['mot_de_passe'])) {
        flash_set('error', "Ancien mot de passe incorrect.");
        header('Location: ../profil.php');
        exit;
    }
    if (strlen($new) < 8 || !preg_match('/\d/', $new)) {
        flash_set('error', "Nouveau mot de passe: min 8 caractères et au moins 1 chiffre.");
        header('Location: ../profil.php');
        exit;
    }
    if ($new !== $new2) {
        flash_set('error', "Confirmation du nouveau mot de passe incorrecte.");
        header('Location: ../profil.php');
        exit;
    }

    User::updatePassword((int)$me['id'], $new);
    flash_set('success', "Mot de passe mis à jour.");
    header('Location: ../profil.php');
    exit;
}

header('Location: ../index.php');
exit;


