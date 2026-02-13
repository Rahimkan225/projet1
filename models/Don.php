<?php
declare(strict_types=1);

class Don
{
    public static function create(array $data): int
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO dons (cagnotte_id, donateur_id, montant, est_anonyme, nom_donateur, email_donateur, message, reference_don)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['cagnotte_id'],
            $data['donateur_id'],
            $data['montant'],
            $data['est_anonyme'] ? 1 : 0,
            $data['nom_donateur'],
            $data['email_donateur'],
            $data['message'],
            $data['reference_don'],
        ]);
        return getLastInsertId($pdo);
    }

    public static function listByCagnotte(int $cagnotteId): array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT d.*
            FROM dons d
            WHERE d.cagnotte_id = ?
            ORDER BY d.date_don DESC
        ");
        $stmt->execute([$cagnotteId]);
        return $stmt->fetchAll();
    }

    public static function statsByDonateur(int $userId): array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT SUM(montant) as total_donne, COUNT(*) as nb_dons
            FROM dons
            WHERE donateur_id = ?
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch() ?: ['total_donne' => 0, 'nb_dons' => 0];

        $stmt2 = $pdo->prepare("SELECT COUNT(DISTINCT cagnotte_id) FROM dons WHERE donateur_id = ?");
        $stmt2->execute([$userId]);
        $stats['nb_cagnottes'] = (int)$stmt2->fetchColumn();

        return $stats;
    }

    public static function historyByDonateur(int $userId, int $limit = 20): array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT d.*, c.nom_patient, c.photo_patient, c.pathologie
            FROM dons d
            JOIN cagnottes c ON d.cagnotte_id = c.id
            WHERE d.donateur_id = ?
            ORDER BY d.date_don DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT d.*, c.nom_patient
            FROM dons d
            JOIN cagnottes c ON d.cagnotte_id = c.id
            WHERE d.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}


