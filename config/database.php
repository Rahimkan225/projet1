<?php
/**
 * PDO connection - MySQL and Supabase (PostgreSQL) support
 */

declare(strict_types=1);

// Determine which database to use
$useSupabase = filter_var((string)env_value('USE_SUPABASE', 'true'), FILTER_VALIDATE_BOOLEAN);

if ($useSupabase) {
    // Use Supabase (PostgreSQL)
    require_once __DIR__ . '/supabase.php';
    $pdo = getSupabaseConnection();
} else {
    // Use MySQL
    $dbHost = (string)env_value('DB_HOST', '127.0.0.1');
    $dbName = (string)env_value('DB_NAME', 'liens_espoir');
    $dbUser = (string)env_value('DB_USER', 'root');
    $dbPass = (string)env_value('DB_PASS', '');
    $dbCharset = 'utf8mb4';
    $dbPort = (int)env_value('DB_PORT', 3306);

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        die('Erreur connexion BDD : ' . $e->getMessage());
    }
}
