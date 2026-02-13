<?php
/**
 * Adaptations pour Supabase (PostgreSQL)
 * Remplace certaines fonctions MySQL par leurs équivalents PostgreSQL
 */

declare(strict_types=1);

class CagnotteSupabase
{
    /**
     * Version PostgreSQL de findActiveWithStats
     * Remplace FIELD() MySQL par CASE WHEN
     */
    public static function findActiveWithStats(array $filters, int $limit, int $offset): array
    {
        global $pdo;

        $where = ["c.statut = 'active'"];
        $params = [];

        // Recherche textuelle
        if (!empty($filters['search']) && trim($filters['search']) !== '') {
            $search = '%' . trim($filters['search']) . '%';
            $where[] = "(c.nom_patient ILIKE ? OR c.diagnostic ILIKE ? OR c.hopital ILIKE ?)";
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

        $whereClause = implode(' AND ', $where);

        // PostgreSQL: utiliser CASE au lieu de FIELD()
        $order = "c.date_creation DESC";
        if (!empty($filters['tri'])) {
            switch ($filters['tri']) {
                case 'urgentes':
                    $order = "CASE c.urgence 
                        WHEN 'critique' THEN 1 
                        WHEN 'elevee' THEN 2 
                        WHEN 'moderee' THEN 3 
                        ELSE 4 
                    END, c.date_creation DESC";
                    break;
                case 'presque_completes':
                    $order = "(c.montant_collecte / NULLIF(c.montant_objectif,0)) DESC";
                    break;
                case 'recentes':
                default:
                    $order = "c.date_creation DESC";
            }
        }

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

    /**
     * Version PostgreSQL de topUrgentes
     */
    public static function topUrgentes(int $limit = 6): array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT c.*,
                   ROUND((c.montant_collecte / NULLIF(c.montant_objectif,0)) * 100) as pourcentage
            FROM cagnottes c
            WHERE c.statut = 'active'
            ORDER BY CASE c.urgence
                WHEN 'critique' THEN 1
                WHEN 'elevee' THEN 2
                WHEN 'moderee' THEN 3
                ELSE 4
            END, c.date_creation DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

