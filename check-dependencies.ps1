# Verificar dependências do SOLPI
Write-Host ""
Write-Host "Verificando dependencias..." -ForegroundColor Cyan
Write-Host ""

$missing = @()

if (Get-Command php -ErrorAction SilentlyContinue) {
    $phpVersion = php --version | Select-Object -First 1
    Write-Host "OK: PHP - $phpVersion" -ForegroundColor Green
} else {
    Write-Host "FALTA: PHP 8.3" -ForegroundColor Red
    $missing += "PHP 8.3"
}

if (Get-Command composer -ErrorAction SilentlyContinue) {
    $composerVersion = composer --version
    Write-Host "OK: $composerVersion" -ForegroundColor Green
} else {
    Write-Host "FALTA: Composer" -ForegroundColor Red
    $missing += "Composer"
}

if (Get-Command git -ErrorAction SilentlyContinue) {
    $gitVersion = git --version
    Write-Host "OK: $gitVersion" -ForegroundColor Green
} else {
    Write-Host "FALTA: Git" -ForegroundColor Red
    $missing += "Git"
}

Write-Host ""

if ($missing.Count -gt 0) {
    Write-Host "Faltam: $($missing -join ', ')" -ForegroundColor Yellow
    Write-Host "Veja INSTALL.md para instruções" -ForegroundColor Yellow
} else {
    Write-Host "Tudo OK! Execute: composer install" -ForegroundColor Green
}
