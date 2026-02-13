<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Cagnotte.php';
require_once __DIR__ . '/../includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create_patient_and_cagnotte') {
    verify_csrf();
    require_roles(['admin_gestionnaire', 'admin_general']);

    // 1) Patient: sélectionner existant ou créer
    $patientMode = (string)($_POST['patient_mode'] ?? 'existing'); // existing|new
    $patientId = (int)($_POST['patient_id'] ?? 0);

    $patientNom = clean((string)($_POST['patient_nom_complet'] ?? ''));
    $patientEmail = clean((string)($_POST['patient_email'] ?? ''));
    $patientTel = clean((string)($_POST['patient_telephone'] ?? ''));
    $patientPassword = (string)($_POST['patient_mot_de_passe'] ?? '');
    $patientPassword2 = (string)($_POST['patient_mot_de_passe_confirm'] ?? '');

    // 2) Cagnotte
    $nomPatient = clean((string)($_POST['nom_patient'] ?? ''));
    $age = (int)($_POST['age_patient'] ?? 0);
    $pathologie = (string)($_POST['pathologie'] ?? '');
    $diagnostic = clean((string)($_POST['diagnostic'] ?? ''));
    $hopital = clean((string)($_POST['hopital'] ?? ''));
    $urgence = (string)($_POST['urgence'] ?? 'moderee');
    $objectif = (float)($_POST['montant_objectif'] ?? 0);

    $errors = [];

    if (!in_array($patientMode, ['existing', 'new'], true)) $errors[] = "Mode patient invalide.";

    if ($patientMode === 'existing') {
        if ($patientId <= 0) $errors[] = "Patient requis.";
        $p = $patientId > 0 ? User::findById($patientId) : null;
        if (!$p || ($p['type'] ?? '') !== 'patient') $errors[] = "Patient invalide.";
    } else {
        if (mb_strlen($patientNom) < 3 || mb_strlen($patientNom) > 100) $errors[] = "Nom patient (compte) invalide (3-100 caractères).";
        if (!filter_var($patientEmail, FILTER_VALIDATE_EMAIL)) $errors[] = "Email patient invalide.";
        if ($patientTel !== '' && !preg_match('/^(?:\+225)?\s?(?:0)?\d{8,10}$/', $patientTel)) $errors[] = "Téléphone patient invalide.";
        if (strlen($patientPassword) < 8 || !preg_match('/\d/', $patientPassword)) $errors[] = "Mot de passe patient: min 8 caractères et au moins 1 chiffre.";
        if ($patientPassword !== $patientPassword2) $errors[] = "Confirmation mot de passe patient incorrecte.";
        if (User::existsEmail($patientEmail)) $errors[] = "Email patient déjà utilisé.";
        if ($patientTel !== '' && User::existsTelephone($patientTel)) $errors[] = "Téléphone patient déjà utilisé.";
    }

    if (mb_strlen($nomPatient) < 3) $errors[] = "Nom du patient (cagnotte) requis.";
    if ($age < 0 || $age > 120) $errors[] = "Âge invalide.";
    if (!in_array($pathologie, ['cancer','chirurgie','accident','maternite','autre'], true)) $errors[] = "Pathologie invalide.";
    if (mb_strlen($diagnostic) < 100) $errors[] = "Diagnostic: minimum 100 caractères.";
    if ($hopital === '') $errors[] = "Hôpital requis.";
    if (!in_array($urgence, ['critique','elevee','moderee'], true)) $errors[] = "Urgence invalide.";
    if ($objectif < 50000 || $objectif > 50000000) $errors[] = "Montant objectif invalide (50 000 - 50 000 000).";

    // 3) Upload justificatifs (PDF/JPG, max 5MB)
    $docs = $_FILES['documents'] ?? null;
    if (!$docs || empty($docs['name']) || !is_array($docs['name'])) {
        $errors[] = "Justificatifs requis.";
    } else {
        $nonEmpty = 0;
        foreach ($docs['name'] as $n) if (!empty($n)) $nonEmpty++;
        if ($nonEmpty < 2) $errors[] = "Au moins 2 justificatifs requis (ordonnance + devis).";
        if ($nonEmpty > 5) $errors[] = "Maximum 5 justificatifs.";
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        header('Location: ../gestionnaire/add-patient-cagnotte.php');
        exit;
    }

    // Transaction: patient (si nouveau) + cagnotte + documents
    global $pdo;
    $pdo->beginTransaction();
    try {
        if ($patientMode === 'new') {
            $patientId = User::createByHierarchy([
                'nom_complet' => $patientNom,
                'email' => $patientEmail,
                'telephone' => $patientTel,
                'mot_de_passe' => $patientPassword,
                'type' => 'patient',
                'created_by' => (int)$_SESSION['user_id'],
            ]);
        }

        $cagnotteId = Cagnotte::create([
            'user_id' => $patientId,
            'created_by' => (int)$_SESSION['user_id'],
            'nom_patient' => $nomPatient,
            'age_patient' => $age,
            'photo_patient' => null,
            'diagnostic' => $diagnostic,
            'pathologie' => $pathologie,
            'hopital' => $hopital,
            'montant_objectif' => $objectif,
            'urgence' => $urgence,
        ]);

        // Upload docs + insertion
        $allowedTypes = ['ordonnance', 'devis', 'certificat', 'autre'];
        foreach ($docs['name'] as $k => $name) {
            if (empty($name)) continue;
            $file = [
                'name' => $docs['name'][$k],
                'type' => $docs['type'][$k] ?? '',
                'tmp_name' => $docs['tmp_name'][$k],
                'size' => $docs['size'][$k],
                'error' => $docs['error'][$k] ?? UPLOAD_ERR_OK,
            ];
            $up = upload_cagnotte_document($file, $cagnotteId);
            if (!empty($up['error'])) {
                throw new RuntimeException("Upload doc erreur: " . $up['error']);
            }

            $typeDoc = 'autre';
            $lower = mb_strtolower((string)$file['name']);
            if (str_contains($lower, 'ordon')) $typeDoc = 'ordonnance';
            if (str_contains($lower, 'devis')) $typeDoc = 'devis';
            if (str_contains($lower, 'certif')) $typeDoc = 'certificat';
            if (!in_array($typeDoc, $allowedTypes, true)) $typeDoc = 'autre';

            $stmtD = $pdo->prepare("
                INSERT INTO documents (cagnotte_id, type_document, nom_fichier, chemin_fichier, taille_fichier)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtD->execute([
                $cagnotteId,
                $typeDoc,
                $up['filename'],
                $up['path'],
                $up['size'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash_set('error', "Erreur: " . $e->getMessage());
        header('Location: ../gestionnaire/add-patient-cagnotte.php');
        exit;
    }

    flash_set('success', "Patient/cagnotte créés. La cagnotte est en attente de validation.");
    header('Location: ../gestionnaire/add-patient-cagnotte.php');
    exit;
}

if ($action === 'update_patient') {
    verify_csrf();
    require_roles(['admin_gestionnaire', 'admin_general']);

    $userId = (int)($_POST['user_id'] ?? 0);
    $nom = clean((string)($_POST['nom_complet'] ?? ''));
    $email = clean((string)($_POST['email'] ?? ''));
    $telephone = clean((string)($_POST['telephone'] ?? ''));

    $errors = [];
    if ($userId <= 0) $errors[] = "Patient invalide.";
    if (mb_strlen($nom) < 3 || mb_strlen($nom) > 100) $errors[] = "Nom complet invalide (3-100 caractères).";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if ($telephone !== '' && !preg_match('/^(?:\+225)?\s?(?:0)?\d{8,10}$/', $telephone)) $errors[] = "Téléphone invalide.";

    $patient = User::findById($userId);
    if (!$patient || ($patient['type'] ?? '') !== 'patient') {
        $errors[] = "Patient introuvable.";
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        header('Location: ../gestionnaire/patients.php');
        exit;
    }

    // Vérifier unicité email / téléphone si changé
    if ($email !== $patient['email'] && User::existsEmail($email)) {
        flash_set('error', "Cet email est déjà utilisé.");
        header('Location: ../gestionnaire/patients.php');
        exit;
    }
    if ($telephone !== '' && $telephone !== ($patient['telephone'] ?? '') && User::existsTelephone($telephone)) {
        flash_set('error', "Ce téléphone est déjà utilisé.");
        header('Location: ../gestionnaire/patients.php');
        exit;
    }

    User::updateProfile($userId, [
        'nom_complet' => $nom,
        'email' => $email,
        'telephone' => $telephone,
    ]);

    flash_set('success', "Informations patient mises à jour.");
    header('Location: ../gestionnaire/patients.php');
    exit;
}

if ($action === 'add_docs') {
    verify_csrf();
    require_roles(['admin_gestionnaire', 'admin_general']);

    $cagnotteId = (int)($_POST['cagnotte_id'] ?? 0);
    if ($cagnotteId <= 0) {
        flash_set('error', "Cagnotte invalide.");
        header('Location: ../cagnottes.php');
        exit;
    }

    $c = Cagnotte::findByIdWithStats($cagnotteId);
    if (!$c) {
        flash_set('error', "Cagnotte introuvable.");
        header('Location: ../cagnottes.php');
        exit;
    }

    $docs = $_FILES['documents'] ?? null;
    if (!$docs || empty($docs['name']) || !is_array($docs['name'])) {
        flash_set('error', "Aucun fichier sélectionné.");
        header('Location: ../cagnotte-detail.php?id=' . $cagnotteId);
        exit;
    }

    global $pdo;
    $allowedTypes = ['ordonnance', 'devis', 'certificat', 'autre'];

    foreach ($docs['name'] as $k => $name) {
        if (empty($name)) continue;
        $file = [
            'name' => $docs['name'][$k],
            'type' => $docs['type'][$k] ?? '',
            'tmp_name' => $docs['tmp_name'][$k],
            'size' => $docs['size'][$k],
            'error' => $docs['error'][$k] ?? UPLOAD_ERR_OK,
        ];
        $up = upload_cagnotte_document($file, $cagnotteId);
        if (!empty($up['error'])) {
            flash_set('error', "Erreur upload: " . $up['error']);
            header('Location: ../cagnotte-detail.php?id=' . $cagnotteId);
            exit;
        }

        $typeDoc = 'autre';
        $lower = mb_strtolower((string)$file['name']);
        if (str_contains($lower, 'ordon')) $typeDoc = 'ordonnance';
        if (str_contains($lower, 'devis')) $typeDoc = 'devis';
        if (str_contains($lower, 'certif')) $typeDoc = 'certificat';
        if (!in_array($typeDoc, $allowedTypes, true)) $typeDoc = 'autre';

        $stmtD = $pdo->prepare("
            INSERT INTO documents (cagnotte_id, type_document, nom_fichier, chemin_fichier, taille_fichier)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtD->execute([
            $cagnotteId,
            $typeDoc,
            $up['filename'],
            $up['path'],
            $up['size'],
        ]);
    }

    flash_set('success', "Documents ajoutés à la cagnotte.");
    header('Location: ../cagnotte-detail.php?id=' . $cagnotteId);
    exit;
}

header('Location: ../index.php');
exit;


