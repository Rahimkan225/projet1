<?php
declare(strict_types=1);

class Cagnotte
{
    private static ?bool $hasCreatedByColumn = null;

    private static function hasCreatedBy(): bool
    {
        if (self::$hasCreatedByColumn !== null) {
            return self::$hasCreatedByColumn;
        }
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT 1
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'cagnottes'
                  AND COLUMN_NAME = 'created_by'
                LIMIT 1
            ");
            $stmt->execute();
            self::$hasCreatedByColumn = (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            self::$hasCreatedByColumn = false;
        }
        return self::$hasCreatedByColumn;
    }

    public static function create(array $data): int
    {
        global $pdo;
        if (self::hasCreatedBy()) {
            $stmt = $pdo->prepare("
                INSERT INTO cagnottes (user_id, created_by, nom_patient, age_patient, photo_patient, diagnostic, pathologie, hopital, montant_objectif, urgence, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')
            ");
            $stmt->execute([
                $data['user_id'],
                $data['created_by'] ?? null,
                $data['nom_patient'],
                $data['age_patient'],
                $data['photo_patient'],
                $data['diagnostic'],
                $data['pathologie'],
                $data['hopital'],
                $data['montant_objectif'],
                $data['urgence'],
            ]);
        } else {
            // Compat schéma V1 (sans created_by)
            $stmt = $pdo->prepare("
                INSERT INTO cagnottes (user_id, nom_patient, age_patient, photo_patient, diagnostic, pathologie, hopital, montant_objectif, urgence, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')
            ");
            $stmt->execute([
                $data['user_id'],
                $data['nom_patient'],
                $data['age_patient'],
                $data['photo_patient'],
                $data['diagnostic'],
                $data['pathologie'],
                $data['hopital'],
                $data['montant_objectif'],
                $data['urgence'],
            ]);
        }
        return getLastInsertId($pdo);
    }

    public static function findActiveWithStats(array $filters, int $limit, int $offset): array
    {
        global $pdo;

        $where = ["c.statut = 'active'"];
        $params = [];

        // Recherche textuelle (nom patient, diagnostic, hôpital)
        if (!empty($filters['search']) && trim($filters['search']) !== '') {
            $search = '%' . trim($filters['search']) . '%';
            // PostgreSQL utilise ILIKE (insensible à la casse), MySQL utilise LIKE
            $likeOperator = $isPostgres ? 'ILIKE' : 'LIKE';
            $where[] = "(c.nom_patient {$likeOperator} ? OR c.diagnostic {$likeOperator} ? OR c.hopital {$likeOperator} ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['pathologie']) && $filters['pathologie'] !== 'all') {
            $where[] = "c.pathologie = ?";
            $params[] = $filters['pathologie'];
        }
        if (!empty($filters['urgence']) && $filters['urgence'] !== 'all') {
            if (in_array($filters['urgence'], ['critique', 'elevee', 'moderee'], true)) {
                $where[] = "c.urgence = ?";
                $params[] = $filters['urgence'];
            }
        }

        // Détecter si on utilise PostgreSQL (Supabase)
        $isPostgres = isPostgres();

        $whereClause = implode(' AND ', $where);
        
        $order = "c.date_creation DESC";
        if (!empty($filters['tri'])) {
            switch ($filters['tri']) {
                case 'urgentes':
                    if ($isPostgres) {
                        // PostgreSQL: utiliser CASE au lieu de FIELD()
                        $order = "CASE c.urgence 
                            WHEN 'critique' THEN 1 
                            WHEN 'elevee' THEN 2 
                            WHEN 'moderee' THEN 3 
                            ELSE 4 
                        END, c.date_creation DESC";
                    } else {
                        // MySQL: utiliser FIELD()
                        $order = "FIELD(c.urgence, 'critique', 'elevee', 'moderee'), c.date_creation DESC";
                    }
                    break;
                case 'presque_completes':
                    $order = "(c.montant_collecte / NULLIF(c.montant_objectif,0)) DESC";
                    break;
                case 'recentes':
                default:
                    $order = "c.date_creation DESC";
            }
        }

        if ($isPostgres) {
            // PostgreSQL: doit inclure toutes les colonnes non-agrégées dans GROUP BY
            $sql = "
                SELECT c.*,
                       u.nom_complet as createur,
                       COUNT(DISTINCT d.id) as nb_donateurs,
                       ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
                FROM cagnottes c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN dons d ON c.id = d.cagnotte_id
                WHERE {$whereClause}
                GROUP BY c.id, u.nom_complet
                ORDER BY {$order}
                LIMIT ? OFFSET ?
            ";
        } else {
            // MySQL: GROUP BY plus permissif
            $sql = "
                SELECT c.*,
                       u.nom_complet as createur,
                       COUNT(DISTINCT d.id) as nb_donateurs,
                       ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
                FROM cagnottes c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN dons d ON c.id = d.cagnotte_id
                WHERE {$whereClause}
                GROUP BY c.id
                ORDER BY {$order}
                LIMIT ? OFFSET ?
            ";
        }
        $params2 = array_merge($params, [$limit, $offset]);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params2);
        $rows = $stmt->fetchAll();

        $totalSql = "SELECT COUNT(*) FROM cagnottes c WHERE {$whereClause}";
        $stmtT = $pdo->prepare($totalSql);
        $stmtT->execute($params);
        $total = (int)$stmtT->fetchColumn();

        return ['rows' => $rows, 'total' => $total];
    }

    public static function findByIdWithStats(int $id): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT c.*,
                   u.nom_complet as createur,
                   COUNT(DISTINCT d.id) as nb_donateurs,
                   ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
            FROM cagnottes c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN dons d ON c.id = d.cagnotte_id
            WHERE c.id = ?
            GROUP BY c.id
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findDocuments(int $cagnotteId): array
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE cagnotte_id = ? ORDER BY date_upload DESC");
        $stmt->execute([$cagnotteId]);
        return $stmt->fetchAll();
    }

    public static function findByUser(int $userId): array
    {
        global $pdo;
        $isPostgres = isPostgres();
        if ($isPostgres) {
            // PostgreSQL: GROUP BY plus strict
            $stmt = $pdo->prepare("
                SELECT c.*,
                       COUNT(DISTINCT d.id) as nb_donateurs,
                       ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
                FROM cagnottes c
                LEFT JOIN dons d ON c.id = d.cagnotte_id
                WHERE c.user_id = ?
                GROUP BY c.id
                ORDER BY c.date_creation DESC
            ");
        } else {
            // MySQL: GROUP BY plus permissif
            $stmt = $pdo->prepare("
                SELECT c.*,
                       COUNT(DISTINCT d.id) as nb_donateurs,
                       ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
                FROM cagnottes c
                LEFT JOIN dons d ON c.id = d.cagnotte_id
                WHERE c.user_id = ?
                GROUP BY c.id
                ORDER BY c.date_creation DESC
            ");
        }
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findPendingWithStats(int $limit = 50): array
    {
        global $pdo;
        $isPostgres = isPostgres();
        if ($isPostgres) {
            // PostgreSQL: GROUP BY plus strict
            $stmt = $pdo->prepare("
                SELECT c.*,
                       u.nom_complet as createur,
                       COUNT(DISTINCT d.id) as nb_donateurs,
                       COUNT(DISTINCT doc.id) as nb_documents,
                       ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
                FROM cagnottes c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN dons d ON c.id = d.cagnotte_id
                LEFT JOIN documents doc ON c.id = doc.cagnotte_id
                WHERE c.statut = 'en_attente'
                GROUP BY c.id, u.nom_complet
                ORDER BY c.date_creation DESC
                LIMIT ?
            ");
        } else {
            // MySQL: GROUP BY plus permissif
            $stmt = $pdo->prepare("
                SELECT c.*,
                       u.nom_complet as createur,
                       COUNT(DISTINCT d.id) as nb_donateurs,
                       COUNT(DISTINCT doc.id) as nb_documents,
                       ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
                FROM cagnottes c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN dons d ON c.id = d.cagnotte_id
                LEFT JOIN documents doc ON c.id = doc.cagnotte_id
                WHERE c.statut = 'en_attente'
                GROUP BY c.id
                ORDER BY c.date_creation DESC
                LIMIT ?
            ");
        }
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function setStatut(int $cagnotteId, string $statut): void
    {
        if (!in_array($statut, ['en_attente', 'active', 'completee', 'rejetee'], true)) {
            throw new InvalidArgumentException("Statut invalide");
        }
        global $pdo;
        $stmt = $pdo->prepare("UPDATE cagnottes SET statut = ? WHERE id = ?");
        $stmt->execute([$statut, $cagnotteId]);
    }

    public static function statsGlobales(): array
    {
        global $pdo;
        $rows = [];
        $rows['total_collecte'] = (float)$pdo->query("SELECT COALESCE(SUM(montant),0) FROM dons")->fetchColumn();
        $rows['nb_users'] = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $rows['nb_cagnottes_actives'] = (int)$pdo->query("SELECT COUNT(*) FROM cagnottes WHERE statut = 'active'")->fetchColumn();
        $rows['nb_cagnottes_completees'] = (int)$pdo->query("SELECT COUNT(*) FROM cagnottes WHERE statut = 'completee'")->fetchColumn();
        $rows['nb_cagnottes_attente'] = (int)$pdo->query("SELECT COUNT(*) FROM cagnottes WHERE statut = 'en_attente'")->fetchColumn();
        return $rows;
    }

    public static function topUrgentes(int $limit = 6): array
    {
        global $pdo;
        $orderClause = orderByField('c.urgence', ['critique', 'elevee', 'moderee'], 'DESC');
        $sql = "
            SELECT c.*,
                   ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
            FROM cagnottes c
            WHERE c.statut = 'active'
            ORDER BY {$orderClause}, c.date_creation DESC
            LIMIT ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function updateMontantCollecte(int $cagnotteId, float $delta): void
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE cagnottes SET montant_collecte = montant_collecte + ? WHERE id = ?");
        $stmt->execute([$delta, $cagnotteId]);
    }

    public static function maybeComplete(int $cagnotteId): void
    {
        global $pdo;
        $stmt = $pdo->prepare("
            UPDATE cagnottes
            SET statut = 'completee'
            WHERE id = ? AND montant_collecte >= montant_objectif AND statut = 'active'
        ");
        $stmt->execute([$cagnotteId]);
    }
}


