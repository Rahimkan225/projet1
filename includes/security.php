<?php
declare(strict_types=1);

function clean(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Vérifie si l'utilisateur courant a l'un des rôles autorisés.
 * Exemple: checkRole(['admin_general','admin_gestionnaire'])
 */
function checkRole(array $roles): bool
{
    $type = (string)($_SESSION['type'] ?? '');
    return $type !== '' && in_array($type, $roles, true);
}

function csrf_field(): string
{
    $token = $_SESSION['csrf_token'] ?? '';
    return '<input type="hidden" name="csrf_token" value="' . clean($token) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
        http_response_code(403);
        die('Token CSRF invalide');
    }
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: connexion.php');
        exit;
    }
}

/**
 * Garde rétro-compat: ancienne fonction require_type('admin'|'patient'|'donateur')
 * En V2, on préfère require_roles([...]).
 */
function require_type(string $type): void
{
    require_login();
    if (($_SESSION['type'] ?? '') !== $type) {
        header('Location: index.php');
        exit;
    }
}

function require_roles(array $roles): void
{
    require_login();
    if (!checkRole($roles)) {
        http_response_code(403);
        header('Location: index.php');
        exit;
    }
}

function login_throttle_check(): void
{
    // Anti brute-force simple en session
    $attempts = $_SESSION['login_attempts'] ?? 0;
    $firstAt = $_SESSION['login_first_attempt_at'] ?? 0;
    $now = time();

    if ($attempts >= 5 && ($now - (int)$firstAt) < (15 * 60)) {
        $_SESSION['error'] = "Trop de tentatives. Réessayez dans 15 minutes.";
        header('Location: connexion.php');
        exit;
    }

    if ($attempts === 0) {
        $_SESSION['login_first_attempt_at'] = $now;
    }
}

function login_throttle_fail(): void
{
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if (empty($_SESSION['login_first_attempt_at'])) {
        $_SESSION['login_first_attempt_at'] = time();
    }
}

function login_throttle_reset(): void
{
    unset($_SESSION['login_attempts'], $_SESSION['login_first_attempt_at']);
}


