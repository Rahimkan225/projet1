<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Cagnotte.php';
require_once __DIR__ . '/../includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    verify_csrf();
    // V2: un patient ne peut pas créer sa cagnotte. Seul l'admin_gestionnaire le fait.
    require_roles(['admin_gestionnaire']);

    $nomPatient = clean((string)($_POST['nom_patient'] ?? ''));
    $age = (int)($_POST['age_patient'] ?? 0);
    $relation = clean((string)($_POST['relation_patient'] ?? ''));
    $pathologie = (string)($_POST['pathologie'] ?? '');
    $diagnostic = clean((string)($_POST['diagnostic'] ?? ''));
    $hopital = clean((string)($_POST['hopital'] ?? ''));
    $urgence = (string)($_POST['urgence'] ?? 'moderee');
    $objectif = (float)($_POST['montant_objectif'] ?? 0);

    $errors = [];
    if (mb_strlen($nomPatient) < 3) $errors[] = "Nom du patient requis.";
    if ($age < 0 || $age > 120) $errors[] = "Âge invalide.";
    if (!in_array($pathologie, ['cancer','chirurgie','accident','maternite','autre'], true)) $errors[] = "Pathologie invalide.";
    if (mb_strlen($diagnostic) < 100) $errors[] = "Diagnostic: minimum 100 caractères.";
    if ($hopital === '') $errors[] = "Hôpital requis.";
    if (!in_array($urgence, ['critique','elevee','moderee'], true)) $errors[] = "Urgence invalide.";
    if ($objectif < 50000 || $objectif > 50000000) $errors[] = "Montant objectif invalide (50 000 - 50 000 000).";

    // Photo patient
    $photoRes = upload_patient_photo($_FILES['photo_patient'] ?? []);
    if (!empty($photoRes['error'])) $errors[] = "Photo: " . $photoRes['error'];

    // Documents (min 2 obligatoires dans la spec)
    $docs = $_FILES['documents'] ?? null;
    if (!$docs || empty($docs['name']) || !is_array($docs['name'])) {
        $errors[] = "Documents requis.";
    } else {
        $nonEmpty = 0;
        foreach ($docs['name'] as $n) {
            if (!empty($n)) $nonEmpty++;
        }
        if ($nonEmpty < 2) $errors[] = "Au moins 2 documents requis (ordonnance + devis).";
        if ($nonEmpty > 5) $errors[] = "Maximum 5 documents.";
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        header('Location: ../creer-cagnotte.php');
        exit;
    }

    // Créer cagnotte
    // Par compat: si ce endpoint est encore utilisé, on crée la cagnotte "au nom" du gestionnaire (sans patient lié).
    // Préférez: controllers/gestionnaire_controller.php (wizard) qui lie à un patient.
    $cagnotteId = Cagnotte::create([
        'user_id' => (int)$_SESSION['user_id'],
        'created_by' => (int)$_SESSION['user_id'],
        'nom_patient' => $nomPatient,
        'age_patient' => $age,
        'photo_patient' => (string)$photoRes['path'],
        'diagnostic' => $diagnostic,
        'pathologie' => $pathologie,
        'hopital' => $hopital,
        'montant_objectif' => $objectif,
        'urgence' => $urgence,
        'relation_patient' => $relation,
    ]);

    // Déplacer la photo vers le dossier cagnotte
    $photoFinalDir = "public/uploads/cagnottes/{$cagnotteId}/";
    ensure_dir($photoFinalDir);
    $photoFinalPath = $photoFinalDir . basename((string)$photoRes['path']);
    @rename((string)$photoRes['path'], $photoFinalPath);

    // Mettre à jour le chemin en BDD
    global $pdo;
    $stmt = $pdo->prepare("UPDATE cagnottes SET photo_patient = ? WHERE id = ?");
    $stmt->execute([$photoFinalPath, $cagnotteId]);

    // Upload documents + insertion table documents
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
            error_log("Upload doc erreur: " . $up['error']);
            continue;
        }

        // Déduire type_document
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

    flash_set('success', "Cagnotte créée ! Elle est en attente de validation.");
    header('Location: ../dashboard-patient.php');
    exit;
}

header('Location: ../index.php');
exit;


