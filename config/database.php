<?php
/**
 * Connexion PDO - Support MySQL et Supabase (PostgreSQL)
 * 
 * Pour utiliser Supabase, définissez USE_SUPABASE=true dans .env
 * 
 * ou modifiez la constante ci-dessous
 */

declare(strict_types=1);

// Déterminer quelle base de données utiliser
$useSupabase = filter_var($_ENV['USE_SUPABASE'] ?? true, FILTER_VALIDATE_BOOLEAN);

if ($useSupabase) {
    // Utiliser Supabase (PostgreSQL)
    require_once __DIR__ . '/supabase.php';
    $pdo = getSupabaseConnection();
} else {
    // Utiliser MySQL (par défaut)
    $dbHost = $_ENV['DB_HOST'] ?? 'sql105.ezyro.com';
    $dbName = $_ENV['DB_NAME'] ?? 'ezyro_41149308_liens_espoir2';
    $dbUser = $_ENV['DB_USER'] ?? 'ezyro_41149308';
    $dbPass = $_ENV['DB_PASS'] ?? '12345678bro';
    $dbCharset = 'utf8mb4';
    $dbPort = (int)($_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? 3306);

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        // En prod: logger et afficher un message générique
        die("Erreur connexion BDD : " . $e->getMessage());
    }
}


