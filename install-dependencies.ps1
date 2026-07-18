# Script para instalar PHP 8.3, Composer e tudo necessário para SOLPI
# Execute como Administrator

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "SOLPI Plugin Setup - Instalando Dependências" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se é Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "❌ ERRO: Execute este script como Administrator" -ForegroundColor Red
    Write-Host "Clique com botão direito no PowerShell e selecione 'Run as administrator'" -ForegroundColor Yellow
    exit 1
}

# 1. Instalar Chocolatey (gerenciador de pacotes Windows)
Write-Host "1️⃣ Verificando Chocolatey..." -ForegroundColor Yellow
if (-not (Get-Command choco -ErrorAction SilentlyContinue)) {
    Write-Host "   Instalando Chocolatey..." -ForegroundColor Cyan
    Set-ExecutionPolicy Bypass -Scope Process -Force
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    Write-Host "   ✅ Chocolatey instalado" -ForegroundColor Green
} else {
    Write-Host "   ✅ Chocolatey já existe" -ForegroundColor Green
}

# 2. Instalar PHP 8.3
Write-Host ""
Write-Host "2️⃣ Verificando PHP 8.3..." -ForegroundColor Yellow
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "   Instalando PHP 8.3..." -ForegroundColor Cyan
    choco install php -y --version 8.3.0
    Write-Host "   ✅ PHP 8.3 instalado" -ForegroundColor Green
} else {
    $phpVersion = php --version | Select-Object -First 1
    Write-Host "   ✅ PHP já existe: $phpVersion" -ForegroundColor Green
}

# 3. Instalar Git
Write-Host ""
Write-Host "3️⃣ Verificando Git..." -ForegroundColor Yellow
if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    Write-Host "   Instalando Git..." -ForegroundColor Cyan
    choco install git -y
    Write-Host "   ✅ Git instalado" -ForegroundColor Green
} else {
    $gitVersion = git --version
    Write-Host "   ✅ Git já existe: $gitVersion" -ForegroundColor Green
}

# 4. Instalar Composer
Write-Host ""
Write-Host "4️⃣ Verificando Composer..." -ForegroundColor Yellow
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "   Instalando Composer..." -ForegroundColor Cyan
    choco install composer -y
    Write-Host "   ✅ Composer instalado" -ForegroundColor Green
} else {
    $composerVersion = composer --version
    Write-Host "   ✅ Composer já existe: $composerVersion" -ForegroundColor Green
}

# 5. Atualizar PATH (se necessário)
Write-Host ""
Write-Host "5️⃣ Atualizando variáveis de ambiente..." -ForegroundColor Yellow
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
Write-Host "   ✅ PATH atualizado" -ForegroundColor Green

# 6. Verificar instalações
Write-Host ""
Write-Host "6️⃣ Verificando instalações..." -ForegroundColor Yellow
Write-Host ""

$php = php --version | Select-Object -First 1
Write-Host "   PHP: $php" -ForegroundColor Cyan

$composer = composer --version
Write-Host "   Composer: $composer" -ForegroundColor Cyan

$git = git --version
Write-Host "   Git: $git" -ForegroundColor Cyan

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "✅ INSTALAÇÃO COMPLETA!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Próximas ações:" -ForegroundColor Yellow
Write-Host "1. Abra um NOVO PowerShell (para recarregar PATH)"
Write-Host "2. Navegue para C:\SOLPI:"
Write-Host "   cd C:\SOLPI"
Write-Host "3. Instale dependências do projeto:"
Write-Host "   composer install"
Write-Host "4. Rode os testes:"
Write-Host "   composer run-script test"
Write-Host ""
Write-Host "Documentação: Veja TESTING.md ou README.md" -ForegroundColor Cyan
