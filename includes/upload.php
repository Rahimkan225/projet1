<?php
declare(strict_types=1);

/**
 * Uploads sécurisés (validation extension + MIME + taille)
 * Chemins relatifs au projet.
 */

function ensure_dir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function upload_profile_photo(array $file): array
{
    $allowedExt = ['jpg', 'jpeg', 'png'];
    $allowedMime = ['image/jpeg', 'image/png'];
    $maxSize = 5 * 1024 * 1024;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload invalide'];
    }
    if (($file['size'] ?? 0) > $maxSize) {
        return ['error' => 'Fichier trop volumineux (max 5MB)'];
    }

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['error' => 'Extension non autorisée (JPG/PNG)'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : ($file['type'] ?? '');
    if ($finfo) finfo_close($finfo);
    if (!in_array($mime, $allowedMime, true)) {
        return ['error' => 'Type MIME non autorisé'];
    }

    $dir = 'public/uploads/profils/';
    ensure_dir($dir);

    $safeName = uniqid('pp_', true) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename((string)$file['name']));
    $path = $dir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return ['error' => 'Erreur lors du déplacement du fichier'];
    }

    return ['success' => true, 'path' => $path, 'filename' => $safeName];
}

function upload_patient_photo(array $file): array
{
    // Même contraintes que photo profil
    $res = upload_profile_photo($file);
    if (isset($res['error'])) return $res;

    // Déplacer dans dossier cagnottes générique (pas par cagnotte car id pas encore connu)
    $src = $res['path'];
    $dir = 'public/uploads/cagnottes/_tmp/';
    ensure_dir($dir);
    $dst = $dir . $res['filename'];
    @rename($src, $dst);

    return ['success' => true, 'path' => $dst, 'filename' => $res['filename']];
}

function upload_cagnotte_document(array $file, int $cagnotteId): array
{
    $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowedMime = ['application/pdf', 'image/jpeg', 'image/png'];
    $maxSize = 5 * 1024 * 1024;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload invalide'];
    }
    if (($file['size'] ?? 0) > $maxSize) {
        return ['error' => 'Fichier trop volumineux (max 10MB)'];
    }

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['error' => 'Extension non autorisée'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : ($file['type'] ?? '');
    if ($finfo) finfo_close($finfo);
    if (!in_array($mime, $allowedMime, true)) {
        return ['error' => 'Type MIME non autorisé'];
    }

    // V2: stocker les justificatifs hors de la racine publique
    $dir = "storage/uploads/cagnottes/{$cagnotteId}/";
    ensure_dir($dir);

    $safeBase = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename((string)$file['name']));
    $safeName = uniqid('doc_', true) . '_' . $safeBase;
    $path = $dir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return ['error' => 'Erreur upload'];
    }

    return ['success' => true, 'path' => $path, 'filename' => $safeName, 'size' => (int)$file['size']];
}


