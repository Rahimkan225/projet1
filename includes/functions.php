<?php
declare(strict_types=1);

function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!empty($_SESSION['flash'][$key])) {
        $msg = (string)$_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function format_fcfa($amount): string
{
    $n = (float)$amount;
    return number_format($n, 0, ',', ' ') . ' FCFA';
}

function urgence_badge_class(string $urgence): string
{
    return match ($urgence) {
        'critique' => 'badge-critique',
        'elevee' => 'badge-elevee',
        default => 'badge-moderee',
    };
}

function truncate(string $text, int $max = 120): string
{
    $text = trim($text);
    if (mb_strlen($text) <= $max) return $text;
    return mb_substr($text, 0, $max) . '...';
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Image "placeholder" locale (SVG) pour un patient, déterministe selon un identifiant.
 * Permet d'afficher une illustration même quand aucune photo n'est uploadée.
 */
function patient_placeholder_img(int $seed = 0): string
{
    $idx = ($seed % 6);
    if ($idx < 0) $idx = -$idx;
    return "public/img/patients/patient-" . ($idx + 1) . ".svg";
}


