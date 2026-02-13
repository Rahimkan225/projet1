<?php
/**
 * Configuration Supabase
 * Remplacez ces valeurs par vos propres credentials Supabase
 */

declare(strict_types=1);

// URL de votre projet Supabase
$supabaseUrl = $_ENV['SUPABASE_URL'] ?? 'https://your-project.supabase.co';

// Clé API Supabase (anon/public key)
$supabaseKey = $_ENV['SUPABASE_KEY'] ?? 'your-anon-key';

// Configuration PostgreSQL via Supabase
$supabaseDbHost = $_ENV['SUPABASE_DB_HOST'] ?? 'db.your-project.supabase.co';
$supabaseDbName = $_ENV['SUPABASE_DB_NAME'] ?? 'postgres';
$supabaseDbUser = $_ENV['SUPABASE_DB_USER'] ?? 'postgres';
$supabaseDbPass = $_ENV['SUPABASE_DB_PASSWORD'] ?? '';
$supabaseDbPort = (int)($_ENV['SUPABASE_DB_PORT'] ?? 5432);

// DSN PostgreSQL pour Supabase
$supabaseDsn = "pgsql:host={$supabaseDbHost};port={$supabaseDbPort};dbname={$supabaseDbName};options=--client_encoding=UTF8";

/**
 * Fonction pour obtenir la connexion PDO Supabase
 * Utilisez cette fonction au lieu de la connexion MySQL actuelle
 */
function getSupabaseConnection(): PDO
{
    global $supabaseDsn, $supabaseDbUser, $supabaseDbPass;
    
    // Vérifier si l'extension pdo_pgsql est disponible
    if (!extension_loaded('pdo_pgsql')) {
        $errorMsg = "L'extension PDO PostgreSQL (pdo_pgsql) n'est pas activée dans PHP.\n\n";
        $errorMsg .= "Pour activer PostgreSQL dans XAMPP :\n";
        $errorMsg .= "1. Ouvrez C:\\xampp\\php\\php.ini\n";
        $errorMsg .= "2. Décommentez (enlevez le ;) ces lignes :\n";
        $errorMsg .= "   extension=pdo_pgsql\n";
        $errorMsg .= "   extension=pgsql\n";
        $errorMsg .= "3. Redémarrez Apache dans XAMPP\n\n";
        $errorMsg .= "Voir ACTIVER_PGSQL.md pour plus de détails.\n\n";
        $errorMsg .= "Alternative : Utilisez l'API REST Supabase (fonction supabaseRequest()).";
        die($errorMsg);
    }
    
    try {
        $pdo = new PDO($supabaseDsn, $supabaseDbUser, $supabaseDbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (Throwable $e) {
        $errorMsg = "Erreur connexion Supabase : " . $e->getMessage() . "\n\n";
        $errorMsg .= "Vérifiez vos credentials dans le fichier .env :\n";
        $errorMsg .= "- SUPABASE_DB_HOST\n";
        $errorMsg .= "- SUPABASE_DB_NAME\n";
        $errorMsg .= "- SUPABASE_DB_USER\n";
        $errorMsg .= "- SUPABASE_DB_PASSWORD\n";
        die($errorMsg);
    }
}

/**
 * Fonction pour utiliser Supabase REST API (alternative à PDO)
 */
function supabaseRequest(string $table, string $method = 'GET', array $data = []): array
{
    global $supabaseUrl, $supabaseKey;
    
    $ch = curl_init();
    $url = "{$supabaseUrl}/rest/v1/{$table}";
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "apikey: {$supabaseKey}",
            "Authorization: Bearer {$supabaseKey}",
            "Content-Type: application/json",
            "Prefer: return=representation",
        ],
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true) ?? [];
    }
    
    throw new RuntimeException("Supabase API error: HTTP {$httpCode}");
}

