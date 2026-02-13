<?php
/**
 * Script de test de connexion Supabase
 * Exécutez ce script pour vérifier vos credentials
 * 
 * Usage: C:\xampp\php\php.exe test_supabase_connection.php
 */

declare(strict_types=1);

// Charger les variables d'environnement
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

echo "========================================\n";
echo "Test de connexion Supabase\n";
echo "========================================\n\n";

// Vérifier l'extension
if (!extension_loaded('pdo_pgsql')) {
    echo "❌ ERREUR: Extension pdo_pgsql non activée\n";
    echo "   Voir ACTIVER_PGSQL.md pour l'activer\n";
    exit(1);
}
echo "✅ Extension pdo_pgsql activée\n\n";

// Vérifier USE_SUPABASE
$useSupabase = filter_var($_ENV['USE_SUPABASE'] ?? false, FILTER_VALIDATE_BOOLEAN);
if (!$useSupabase) {
    echo "⚠️  ATTENTION: USE_SUPABASE=false dans .env\n";
    echo "   La connexion utilisera MySQL au lieu de Supabase\n";
    echo "   Changez USE_SUPABASE=true pour utiliser Supabase\n\n";
}

// Récupérer les credentials
$host = $_ENV['SUPABASE_DB_HOST'] ?? 'db.your-project.supabase.co';
$port = (int)($_ENV['SUPABASE_DB_PORT'] ?? 5432);
$dbname = $_ENV['SUPABASE_DB_NAME'] ?? 'postgres';
$user = $_ENV['SUPABASE_DB_USER'] ?? 'postgres';
$password = $_ENV['SUPABASE_DB_PASSWORD'] ?? '';

echo "Configuration détectée:\n";
echo "  Host: " . ($host === 'db.your-project.supabase.co' ? '❌ NON CONFIGURÉ' : $host) . "\n";
echo "  Port: $port\n";
echo "  Database: $dbname\n";
echo "  User: $user\n";
echo "  Password: " . (empty($password) ? '❌ NON DÉFINI' : '***' . substr($password, -3)) . "\n\n";

// Vérifier que les credentials sont configurés
if ($host === 'db.your-project.supabase.co' || empty($password)) {
    echo "❌ ERREUR: Credentials non configurés dans .env\n";
    echo "   Voir CONFIGURER_SUPABASE_CREDENTIALS.md pour les instructions\n";
    exit(1);
}

// Tester la connexion
echo "Tentative de connexion...\n";
try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};options=--client_encoding=UTF8";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    
    echo "✅ Connexion réussie !\n\n";
    
    // Tester une requête simple
    echo "Test de requête...\n";
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "✅ Version PostgreSQL: " . substr($version, 0, 50) . "...\n\n";
    
    // Vérifier les tables
    echo "Vérification des tables...\n";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️  Aucune table trouvée. Exécutez database/supabase_schema.sql\n";
    } else {
        echo "✅ Tables trouvées (" . count($tables) . "):\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
    }
    
    echo "\n✅ Tous les tests sont passés !\n";
    echo "   Votre configuration Supabase est correcte.\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERREUR de connexion:\n";
    echo "   " . $e->getMessage() . "\n\n";
    
    // Messages d'aide selon le type d'erreur
    $errorCode = $e->getCode();
    $errorMsg = $e->getMessage();
    
    if (strpos($errorMsg, 'password authentication failed') !== false) {
        echo "💡 SOLUTION: Vérifiez le mot de passe dans .env\n";
        echo "   1. Allez dans Supabase > Settings > Database\n";
        echo "   2. Vérifiez ou réinitialisez le mot de passe\n";
        echo "   3. Copiez-collez le mot de passe dans SUPABASE_DB_PASSWORD\n";
    } elseif (strpos($errorMsg, 'could not connect') !== false || strpos($errorMsg, 'timeout') !== false) {
        echo "💡 SOLUTION: Vérifiez les paramètres de connexion\n";
        echo "   1. Vérifiez SUPABASE_DB_HOST (doit commencer par 'db.')\n";
        echo "   2. Vérifiez votre connexion Internet\n";
        echo "   3. Vérifiez que le projet Supabase est actif\n";
    } elseif (strpos($errorMsg, 'database') !== false && strpos($errorMsg, 'does not exist') !== false) {
        echo "💡 SOLUTION: Vérifiez SUPABASE_DB_NAME\n";
        echo "   Le nom de la base est généralement 'postgres'\n";
    }
    
    echo "\n   Voir CONFIGURER_SUPABASE_CREDENTIALS.md pour plus d'aide\n";
    exit(1);
}





