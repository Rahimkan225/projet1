<?php
declare(strict_types=1);

/**
 * Utilitaires pour la compatibilité MySQL/PostgreSQL
 */

/**
 * Détecte si on utilise PostgreSQL (Supabase)
 */
function isPostgres(): bool
{
    global $pdo;
    if (!isset($pdo)) {
        return false;
    }
    try {
        return ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql');
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Obtient le dernier ID inséré (compatible MySQL et PostgreSQL)
 */
function getLastInsertId(PDO $pdo, ?string $sequence = null): int
{
    if (isPostgres()) {
        // PostgreSQL nécessite le nom de la séquence
        if ($sequence === null) {
            // Essayer de deviner depuis la dernière requête
            // Pour les tables avec SERIAL, la séquence est généralement: table_column_seq
            // On peut aussi utiliser lastval() si on est dans la même transaction
            return (int)$pdo->lastInsertId();
        }
        return (int)$pdo->lastInsertId($sequence);
    }
    return (int)$pdo->lastInsertId();
}

/**
 * Vérifie si une colonne existe dans une table (compatible MySQL et PostgreSQL)
 */
function columnExists(string $table, string $column): bool
{
    global $pdo;
    
    if (isPostgres()) {
        // PostgreSQL: utiliser information_schema avec current_schema()
        try {
            $stmt = $pdo->prepare("
                SELECT 1
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = ?
                  AND column_name = ?
                LIMIT 1
            ");
            $stmt->execute([$table, $column]);
            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    } else {
        // MySQL: utiliser INFORMATION_SCHEMA avec DATABASE()
        try {
            $stmt = $pdo->prepare("
                SELECT 1
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
                LIMIT 1
            ");
            $stmt->execute([$table, $column]);
            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }
}

/**
 * Convertit ORDER BY FIELD() MySQL en CASE WHEN pour PostgreSQL
 */
function orderByField(string $column, array $values, string $defaultOrder = 'DESC'): string
{
    if (isPostgres()) {
        // PostgreSQL: utiliser CASE WHEN
        $cases = [];
        foreach ($values as $index => $value) {
            $escapedValue = addslashes($value);
            $cases[] = "WHEN '{$escapedValue}' THEN " . ($index + 1);
        }
        $caseExpr = "CASE {$column} " . implode(' ', $cases) . " ELSE " . (count($values) + 1) . " END";
        return $caseExpr;
    } else {
        // MySQL: utiliser FIELD()
        $valuesStr = implode("','", array_map('addslashes', $values));
        return "FIELD({$column}, '{$valuesStr}')";
    }
}

