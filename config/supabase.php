<?php
/**
 * Supabase configuration
 */

declare(strict_types=1);

// Supabase project URL
$supabaseUrl = (string)env_value('SUPABASE_URL', 'https://your-project.supabase.co');

// Supabase API key (anon/public)
$supabaseKey = (string)env_value('SUPABASE_KEY', 'your-anon-key');

// PostgreSQL configuration via Supabase
$supabaseDbHost = (string)env_value('SUPABASE_DB_HOST', 'db.your-project.supabase.co');
$supabaseDbName = (string)env_value('SUPABASE_DB_NAME', 'postgres');
$supabaseDbUser = (string)env_value('SUPABASE_DB_USER', 'postgres');
$supabaseDbPass = (string)env_value('SUPABASE_DB_PASSWORD', '');
$supabaseDbPort = (int)env_value('SUPABASE_DB_PORT', 5432);

// PostgreSQL DSN for Supabase
$supabaseDsn = "pgsql:host={$supabaseDbHost};port={$supabaseDbPort};dbname={$supabaseDbName};options=--client_encoding=UTF8";

/**
 * Get Supabase PDO connection
 */
function getSupabaseConnection(): PDO
{
    global $supabaseDsn, $supabaseDbUser, $supabaseDbPass;

    if (!extension_loaded('pdo_pgsql')) {
        die("L'extension PDO PostgreSQL (pdo_pgsql) n'est pas activee.");
    }

    try {
        return new PDO($supabaseDsn, $supabaseDbUser, $supabaseDbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        $errorMsg = "Erreur connexion Supabase : " . $e->getMessage() . "\n\n";
        $errorMsg .= "Verifiez vos variables d'environnement :\n";
        $errorMsg .= "- SUPABASE_DB_HOST\n";
        $errorMsg .= "- SUPABASE_DB_NAME\n";
        $errorMsg .= "- SUPABASE_DB_USER\n";
        $errorMsg .= "- SUPABASE_DB_PASSWORD\n";
        die($errorMsg);
    }
}

/**
 * Use Supabase REST API (alternative to PDO)
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
            'Content-Type: application/json',
            'Prefer: return=representation',
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
        return json_decode((string)$response, true) ?? [];
    }

    throw new RuntimeException("Supabase API error: HTTP {$httpCode}");
}
