<?php
declare(strict_types=1);

require_once __DIR__ . '/config/init.php';

require_login();

$docId = (int)($_GET['doc_id'] ?? 0);
if ($docId <= 0) {
    http_response_code(400);
    die('Document invalide');
}

global $pdo;
$stmt = $pdo->prepare("
    SELECT d.*, c.user_id AS patient_id
    FROM documents d
    INNER JOIN cagnottes c ON c.id = d.cagnotte_id
    WHERE d.id = ?
    LIMIT 1
");
$stmt->execute([$docId]);
$doc = $stmt->fetch();
if (!$doc) {
    http_response_code(404);
    die('Document introuvable');
}

$meId = (int)($_SESSION['user_id'] ?? 0);
$meType = (string)($_SESSION['type'] ?? '');
$isOwnerPatient = $meId > 0 && $meId === (int)$doc['patient_id'];
$isAdmin = in_array($meType, ['admin_general', 'admin_gestionnaire'], true);
if (!$isAdmin && !$isOwnerPatient) {
    http_response_code(403);
    die('Accès refusé');
}

$path = (string)$doc['chemin_fichier'];
if ($path === '' || !is_file($path)) {
    http_response_code(404);
    die('Fichier manquant');
}

// Détermination du type MIME
$mime = 'application/octet-stream';
$finfo = finfo_open(FILEINFO_MIME_TYPE);
if ($finfo) {
    $m = finfo_file($finfo, $path);
    if (is_string($m) && $m !== '') $mime = $m;
    finfo_close($finfo);
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($path));
header('Content-Disposition: attachment; filename="' . basename((string)$doc['nom_fichier']) . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;


