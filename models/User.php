<?php
declare(strict_types=1);

class User
{
    private static ?bool $hasCreatedByColumn = null;
    private static ?bool $hasIsActiveColumn = null;

    private static function hasIsActive(): bool
    {
        if (self::$hasIsActiveColumn !== null) {
            return self::$hasIsActiveColumn;
        }
        self::$hasIsActiveColumn = columnExists('users', 'is_active');
        return self::$hasIsActiveColumn;
    }

    private static function hasCreatedBy(): bool
    {
        if (self::$hasCreatedByColumn !== null) {
            return self::$hasCreatedByColumn;
        }
        self::$hasCreatedByColumn = columnExists('users', 'created_by');
        return self::$hasCreatedByColumn;
    }

    public static function findByEmailOrPhone(string $identifiant): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR telephone = ? LIMIT 1");
        $stmt->execute([$identifiant, $identifiant]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function existsEmail(string $email): bool
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return (bool)$stmt->fetchColumn();
    }

    public static function existsTelephone(?string $telephone): bool
    {
        if ($telephone === null || $telephone === '') return false;
        global $pdo;
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE telephone = ? LIMIT 1");
        $stmt->execute([$telephone]);
        return (bool)$stmt->fetchColumn();
    }

    public static function findById(int $id): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        global $pdo;

        if (self::hasCreatedBy()) {
            $stmt = $pdo->prepare("
                INSERT INTO users (nom_complet, email, telephone, mot_de_passe, type, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nom_complet'],
                $data['email'],
                $data['telephone'] ?: null,
                password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
                $data['type'],
                $data['created_by'] ?? null,
            ]);
        } else {
            // Compat schéma V1 (sans created_by)
            $stmt = $pdo->prepare("
                INSERT INTO users (nom_complet, email, telephone, mot_de_passe, type)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nom_complet'],
                $data['email'],
                $data['telephone'] ?: null,
                password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
                $data['type'],
            ]);
        }
        return getLastInsertId($pdo);
    }

    /**
     * Création contrôlée par hiérarchie (V2).
     * - admin_general peut créer: admin_gestionnaire
     * - admin_gestionnaire peut créer: patient
     */
    public static function createByHierarchy(array $data): int
    {
        $type = (string)($data['type'] ?? '');
        $createdBy = (int)($data['created_by'] ?? 0);
        if ($createdBy <= 0) {
            throw new InvalidArgumentException('created_by requis');
        }

        $creator = self::findById($createdBy);
        if (!$creator) {
            throw new RuntimeException('Créateur introuvable');
        }

        $creatorType = (string)$creator['type'];
        $allowed = [
            'admin_general' => ['admin_gestionnaire'],
            'admin_gestionnaire' => ['patient'],
        ];
        if (!isset($allowed[$creatorType]) || !in_array($type, $allowed[$creatorType], true)) {
            throw new RuntimeException('Création interdite par hiérarchie');
        }

        return self::create($data);
    }

    public static function listPatients(int $limit = 200): array
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id, nom_complet, email, telephone FROM users WHERE type = 'patient' ORDER BY nom_complet ASC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function listPatientsDetailed(int $limit = 500): array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT id, nom_complet, email, telephone, date_inscription
            FROM users
            WHERE type = 'patient'
            ORDER BY date_inscription DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function listForAdminGeneral(int $limit = 300): array
    {
        global $pdo;

        // Compat: si is_active n'existe pas encore, on renvoie 1
        $isActiveSql = self::hasIsActive() ? 'u.is_active' : '1';

        $orderClause = orderByField('u.type', ['admin_general', 'admin_gestionnaire', 'patient', 'donateur'], 'DESC');
        $sql = "
            SELECT u.id, u.nom_complet, u.email, u.telephone, u.type, {$isActiveSql} AS is_active,
                   u.created_by, u.date_inscription,
                   (SELECT COUNT(*) FROM cagnottes c WHERE c.user_id = u.id) AS nb_cagnottes,
                   (SELECT COUNT(*) FROM dons d WHERE d.donateur_id = u.id) AS nb_dons
            FROM users u
            ORDER BY {$orderClause}, u.date_inscription DESC
            LIMIT ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function adminGeneralSetRole(int $targetUserId, string $newRole, int $actorId): void
    {
        if (!in_array($newRole, ['donateur', 'patient', 'admin_gestionnaire'], true)) {
            throw new InvalidArgumentException('Rôle invalide');
        }
        if ($targetUserId <= 0 || $actorId <= 0) {
            throw new InvalidArgumentException('Paramètres invalides');
        }
        if ($targetUserId === $actorId) {
            throw new RuntimeException("Impossible de modifier votre propre rôle.");
        }

        $target = self::findById($targetUserId);
        if (!$target) throw new RuntimeException('Utilisateur introuvable');
        if (($target['type'] ?? '') === 'admin_general') {
            throw new RuntimeException('Impossible de modifier un admin général.');
        }

        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET type = ? WHERE id = ?");
        $stmt->execute([$newRole, $targetUserId]);
    }

    public static function adminGeneralSetActive(int $targetUserId, int $isActive, int $actorId): void
    {
        if ($targetUserId <= 0 || $actorId <= 0) {
            throw new InvalidArgumentException('Paramètres invalides');
        }
        if ($targetUserId === $actorId) {
            throw new RuntimeException("Impossible de vous désactiver vous-même.");
        }

        $target = self::findById($targetUserId);
        if (!$target) throw new RuntimeException('Utilisateur introuvable');
        if (($target['type'] ?? '') === 'admin_general') {
            throw new RuntimeException('Impossible de désactiver un admin général.');
        }

        if (!self::hasIsActive()) {
            throw new RuntimeException("La colonne is_active n'existe pas encore en BDD (migration requise).");
        }

        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->execute([(int)!!$isActive, $targetUserId]);
    }

    public static function updateProfile(int $id, array $data): void
    {
        global $pdo;
        
        // Vérifier quelles colonnes existent
        $hasExtendedFields = self::hasExtendedProfileFields();
        
        if ($hasExtendedFields) {
            $stmt = $pdo->prepare("
                UPDATE users
                SET nom_complet = ?, email = ?, telephone = ?,
                    adresse = ?, ville = ?, pays = ?, code_postal = ?,
                    date_naissance = ?, bio = ?, site_web = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['nom_complet'],
                $data['email'],
                $data['telephone'] ?: null,
                $data['adresse'] ?? null,
                $data['ville'] ?? null,
                $data['pays'] ?? null,
                $data['code_postal'] ?? null,
                $data['date_naissance'] ?? null,
                $data['bio'] ?? null,
                $data['site_web'] ?? null,
                $id
            ]);
        } else {
            // Compatibilité avec ancien schéma
            $stmt = $pdo->prepare("
                UPDATE users
                SET nom_complet = ?, email = ?, telephone = ?
                WHERE id = ?
            ");
            $stmt->execute([$data['nom_complet'], $data['email'], $data['telephone'] ?: null, $id]);
        }
    }
    
    private static function hasExtendedProfileFields(): bool
    {
        return columnExists('users', 'adresse');
    }

    public static function updatePhoto(int $id, string $photoPath): void
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET photo_profil = ? WHERE id = ?");
        $stmt->execute([$photoPath, $id]);
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        global $pdo;
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);
    }
}


