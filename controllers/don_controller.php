<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../models/Cagnotte.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    verify_csrf();

    $cagnotteId = (int)($_POST['cagnotte_id'] ?? 0);
    $montant = (float)($_POST['montant'] ?? 0);
    $estAnonyme = !empty($_POST['est_anonyme']);
    $message = clean((string)($_POST['message'] ?? ''));
    if (mb_strlen($message) > 500) $message = mb_substr($message, 0, 500);

    $cagnotte = Cagnotte::findByIdWithStats($cagnotteId);
    if (!$cagnotte || ($cagnotte['statut'] ?? '') !== 'active') {
        flash_set('error', "Cagnotte introuvable ou inactive.");
        header('Location: ../cagnottes.php');
        exit;
    }

    if ($montant <= 0) {
        flash_set('error', "Montant invalide.");
        header("Location: ../faire-don.php?cagnotte_id={$cagnotteId}");
        exit;
    }

    $donateurId = null;
    $nomDonateur = null;
    $emailDonateur = null;

    if (!empty($_SESSION['user_id'])) {
        $donateurId = (int)$_SESSION['user_id'];
        $nomDonateur = (string)($_SESSION['nom_complet'] ?? '');
        $emailDonateur = (string)($_SESSION['email'] ?? '');
    } else {
        $nomDonateur = clean((string)($_POST['nom_donateur'] ?? ''));
        $emailDonateur = clean((string)($_POST['email_donateur'] ?? ''));
        if ($nomDonateur === '' || !filter_var($emailDonateur, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', "Nom et email requis pour recevoir le reçu.");
            header("Location: ../faire-don.php?cagnotte_id={$cagnotteId}");
            exit;
        }
    }

    if ($estAnonyme) {
        $nomDonateur = 'Anonyme';
    }

    global $pdo;
    try {
        $pdo->beginTransaction();

        // Créer don
        $ref = 'LE-' . time() . '-TMP';
        $donId = Don::create([
            'cagnotte_id' => $cagnotteId,
            'donateur_id' => $donateurId,
            'montant' => $montant,
            'est_anonyme' => $estAnonyme,
            'nom_donateur' => $nomDonateur,
            'email_donateur' => $emailDonateur,
            'message' => $message ?: null,
            'reference_don' => $ref,
        ]);

        // Mettre une référence unique finale
        $refFinal = 'LE-' . time() . '-' . $donId;
        $stmt = $pdo->prepare("UPDATE dons SET reference_don = ? WHERE id = ?");
        $stmt->execute([$refFinal, $donId]);

        // Update cagnotte
        Cagnotte::updateMontantCollecte($cagnotteId, $montant);
        Cagnotte::maybeComplete($cagnotteId);

        $pdo->commit();

        flash_set('success', "Votre don de " . format_fcfa($montant) . " a été enregistré avec succès. Merci pour votre générosité !");
        header("Location: ../dons/confirmation.php?don_id={$donId}");
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Erreur don: " . $e->getMessage());
        flash_set('error', "Erreur lors de l'enregistrement du don.");
        header("Location: ../faire-don.php?cagnotte_id={$cagnotteId}");
        exit;
    }
}

header('Location: ../index.php');
exit;


