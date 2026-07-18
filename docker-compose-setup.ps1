# SOLPI Docker Compose Setup Script for Windows PowerShell

Write-Host "=== SOLPI Docker Compose Setup ===" -ForegroundColor Cyan
Write-Host ""

# Check Docker
Write-Host "Checking Docker installation..." -ForegroundColor Yellow
if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Host "Docker is not installed. Please install Docker Desktop." -ForegroundColor Red
    exit 1
}
Write-Host "✓ Docker found" -ForegroundColor Green

# Check Docker Compose
Write-Host "Checking Docker Compose..." -ForegroundColor Yellow
if (-not (Get-Command docker-compose -ErrorAction SilentlyContinue)) {
    Write-Host "Docker Compose is not installed." -ForegroundColor Red
    exit 1
}
Write-Host "✓ Docker Compose found" -ForegroundColor Green

# Create .env if not exists
Write-Host "Setting up environment..." -ForegroundColor Yellow
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    Write-Host "✓ Created .env from .env.example" -ForegroundColor Green
} else {
    Write-Host "✓ .env already exists" -ForegroundColor Green
}

# Create certs directory
Write-Host "Setting up SSL certificates..." -ForegroundColor Yellow
if (-not (Test-Path certs)) {
    New-Item -ItemType Directory -Path certs | Out-Null
}

# Generate self-signed certificate if not exists
if (-not ((Test-Path certs\localhost.crt) -and (Test-Path certs\localhost.key))) {
    Write-Host "Generating self-signed certificate..." -ForegroundColor Yellow
    # Using Windows built-in OpenSSL if available, otherwise skip
    try {
        & openssl req -x509 -newkey rsa:4096 -keyout certs/localhost.key `
            -out certs/localhost.crt -days 365 -nodes `
            -subj "/C=BR/ST=State/L=City/O=SOLPI/CN=localhost"
        Write-Host "✓ Certificate generated" -ForegroundColor Green
    } catch {
        Write-Host "⚠ OpenSSL not found. Using Windows certificate generation..." -ForegroundColor Yellow
        # Alternative: Let nginx generate self-signed cert on startup
        Write-Host "  Nginx will generate self-signed certificate on first run" -ForegroundColor Gray
    }
} else {
    Write-Host "✓ Certificate already exists" -ForegroundColor Green
}

# Create required directories
Write-Host "Creating data directories..." -ForegroundColor Yellow
foreach ($dir in @("data\hermes", "data\redis", "data\openclaw")) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir | Out-Null
    }
}

Write-Host ""
Write-Host "=== Setup Complete ===" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Review .env file and update API keys if needed"
Write-Host "2. Run: docker-compose up -d"
Write-Host "3. Wait 30-60 seconds for services to start"
Write-Host "4. Check status: docker-compose ps"
Write-Host ""
Write-Host "Access points:" -ForegroundColor Yellow
Write-Host "  • Hermes UI:      http://localhost:8001"
Write-Host "  • Hermes Gateway: http://localhost:8002"
Write-Host "  • OpenClaw:       http://localhost:18789"
Write-Host "  • Nginx (proxy):  http://localhost"
Write-Host "  • Redis:          localhost:6379"
Write-Host ""
Write-Host "Useful commands:" -ForegroundColor Yellow
Write-Host "  • docker-compose logs -f hermes-agent"
Write-Host "  • docker-compose logs -f openclaw"
Write-Host "  • docker-compose ps"
Write-Host "  • docker-compose down"
Write-Host ""
