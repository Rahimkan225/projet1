# Script PowerShell pour activer les extensions PostgreSQL dans XAMPP
# Exécutez ce script en tant qu'administrateur

$phpIniPath = "C:\xampp\php\php.ini"

Write-Host "Activation des extensions PostgreSQL dans XAMPP..." -ForegroundColor Cyan
Write-Host ""

# Vérifier que php.ini existe
if (-not (Test-Path $phpIniPath)) {
    Write-Host "ERREUR: Le fichier php.ini n'existe pas à : $phpIniPath" -ForegroundColor Red
    Write-Host "Vérifiez votre installation XAMPP." -ForegroundColor Yellow
    exit 1
}

# Lire le contenu de php.ini
$content = Get-Content $phpIniPath -Raw
$modified = $false

# Activer pdo_pgsql
if ($content -match ";extension=pdo_pgsql") {
    $content = $content -replace ";extension=pdo_pgsql", "extension=pdo_pgsql"
    Write-Host "[OK] Extension pdo_pgsql activée" -ForegroundColor Green
    $modified = $true
} elseif ($content -match "extension=pdo_pgsql") {
    Write-Host "[INFO] Extension pdo_pgsql déjà activée" -ForegroundColor Yellow
} else {
    Write-Host "[WARNING] Extension pdo_pgsql non trouvée dans php.ini" -ForegroundColor Yellow
}

# Activer pgsql
if ($content -match ";extension=pgsql") {
    $content = $content -replace ";extension=pgsql", "extension=pgsql"
    Write-Host "[OK] Extension pgsql activée" -ForegroundColor Green
    $modified = $true
} elseif ($content -match "extension=pgsql") {
    Write-Host "[INFO] Extension pgsql déjà activée" -ForegroundColor Yellow
} else {
    Write-Host "[WARNING] Extension pgsql non trouvée dans php.ini" -ForegroundColor Yellow
}

# Sauvegarder si modifié
if ($modified) {
    try {
        # Créer une sauvegarde
        $backupPath = "$phpIniPath.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
        Copy-Item $phpIniPath $backupPath
        Write-Host "[OK] Sauvegarde créée : $backupPath" -ForegroundColor Green
        
        # Écrire le nouveau contenu
        Set-Content -Path $phpIniPath -Value $content -NoNewline
        Write-Host "[OK] php.ini mis à jour avec succès" -ForegroundColor Green
        Write-Host ""
        Write-Host "IMPORTANT : Redémarrez Apache dans XAMPP pour appliquer les changements !" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Pour vérifier, exécutez :" -ForegroundColor Cyan
        Write-Host "  C:\xampp\php\php.exe -m | Select-String -Pattern 'pdo_pgsql|pgsql'" -ForegroundColor White
    } catch {
        Write-Host "[ERREUR] Impossible de modifier php.ini : $_" -ForegroundColor Red
        Write-Host "Essayez d'exécuter ce script en tant qu'administrateur." -ForegroundColor Yellow
        exit 1
    }
} else {
    Write-Host "[INFO] Aucune modification nécessaire" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Terminé !" -ForegroundColor Green

