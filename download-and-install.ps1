# Script de Download - PHP 8.3 e Composer para Windows
# Salve como: C:\SOLPI\download-and-install.ps1
# Execute como: powershell -ExecutionPolicy Bypass -File "C:\SOLPI\download-and-install.ps1"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SOLPI - Download PHP 8.3 e Composer" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$downloadDir = "C:\temp-php-composer"
$phpDir = "C:\php83"
$composerDir = "C:\composer"

# Criar diretórios
if (-not (Test-Path $downloadDir)) { New-Item -ItemType Directory -Path $downloadDir | Out-Null }
if (-not (Test-Path $phpDir)) { New-Item -ItemType Directory -Path $phpDir | Out-Null }
if (-not (Test-Path $composerDir)) { New-Item -ItemType Directory -Path $composerDir | Out-Null }

Write-Host "1. Baixando PHP 8.3..." -ForegroundColor Yellow
$phpUrl = "https://windows.php.net/downloads/releases/php-8.3.14-Win32-vs16-x64.zip"
$phpZip = "$downloadDir\php83.zip"

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    $webClient = New-Object System.Net.WebClient
    $webClient.DownloadFile($phpUrl, $phpZip)
    Write-Host "   ✅ PHP baixado" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Erro ao baixar PHP" -ForegroundColor Red
    Write-Host "   Visite: https://windows.php.net/downloads/releases/" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "2. Extraindo PHP..." -ForegroundColor Yellow
try {
    Expand-Archive -Path $phpZip -DestinationPath $phpDir -Force
    Write-Host "   ✅ PHP extraído em: $phpDir" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Erro ao extrair" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "3. Configurando PHP..." -ForegroundColor Yellow
$phpIniTemplate = @"
; Arquivo gerado automaticamente pelo script de setup SOLPI

[PHP]
engine = On
short_open_tag = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func =
serialize_precision = -1
disable_functions =
disable_classes =
zend.enable_gc = On

[Date]
date.timezone = UTC

[mail function]
SMTP = localhost
smtp_port = 25

[MySQL]
mysql.allow_local_infile = On
mysql.allow_persistent = On
mysql.max_persistent = -1
mysql.max_links = -1

[mysqli]
mysqli.allow_persistent = On
mysqli.max_persistent = -1
mysqli.max_links = -1
mysqli.default_port = 3306
mysqli.default_socket =
mysqli.default_host =
mysqli.default_user =
mysqli.default_pw =

[PDO]
pdo_mysql.default_socket=
"@

$iniPath = "$phpDir\php.ini"
if (-not (Test-Path $iniPath)) {
    $phpIniTemplate | Out-File -FilePath $iniPath -Encoding UTF8
    Write-Host "   ✅ php.ini criado" -ForegroundColor Green
}

Write-Host ""
Write-Host "4. Baixando Composer..." -ForegroundColor Yellow
$composerUrl = "https://getcomposer.org/composer.phar"
$composerPhar = "$composerDir\composer.phar"

try {
    $webClient.DownloadFile($composerUrl, $composerPhar)
    Write-Host "   ✅ Composer baixado" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Erro ao baixar Composer" -ForegroundColor Red
    Write-Host "   Visite: https://getcomposer.org/download/" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "5. Criando scripts..." -ForegroundColor Yellow

# Criar composer.bat
$composerBat = @"
@echo off
cd /d %~dp0
php composer.phar %*
"@
$composerBat | Out-File -FilePath "$composerDir\composer.bat" -Encoding ASCII

# Criar php.bat (opcional)
$phpBat = @"
@echo off
$phpDir\php.exe %*
"@
$phpBat | Out-File -FilePath "$phpDir\php.bat" -Encoding ASCII

Write-Host "   ✅ Scripts criados" -ForegroundColor Green

Write-Host ""
Write-Host "6. Adicionando ao PATH..." -ForegroundColor Yellow

$currentPath = [Environment]::GetEnvironmentVariable("Path", "User")
$pathsToAdd = @($phpDir, $composerDir)

foreach ($path in $pathsToAdd) {
    if ($currentPath -notlike "*$path*") {
        $currentPath += ";$path"
    }
}

[Environment]::SetEnvironmentVariable("Path", $currentPath, "User")
$env:Path = $currentPath

Write-Host "   ✅ PATH atualizado" -ForegroundColor Green
Write-Host "      • PHP: $phpDir" -ForegroundColor Gray
Write-Host "      • Composer: $composerDir" -ForegroundColor Gray

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "✅ INSTALAÇÃO CONCLUÍDA!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Verificar instalação
Write-Host "Verificando..." -ForegroundColor Yellow
php --version
composer --version

Write-Host ""
Write-Host "Próximos passos:" -ForegroundColor Cyan
Write-Host "1. Feche e abra um NOVO PowerShell" -ForegroundColor White
Write-Host "2. cd C:\SOLPI" -ForegroundColor White
Write-Host "3. composer install" -ForegroundColor White
Write-Host "4. composer run-script test" -ForegroundColor White
Write-Host ""

# Cleanup
Write-Host "Limpando downloads..." -ForegroundColor Gray
Remove-Item -Recurse -Force $downloadDir -ErrorAction SilentlyContinue
Write-Host "Pronto!" -ForegroundColor Green
