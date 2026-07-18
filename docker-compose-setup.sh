#!/bin/bash
# SOLPI Docker Compose Setup Script

set -e

echo "=== SOLPI Docker Compose Setup ==="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check Docker
echo -e "${YELLOW}Checking Docker installation...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker is not installed. Please install Docker Desktop.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker found${NC}"

# Check Docker Compose
echo -e "${YELLOW}Checking Docker Compose...${NC}"
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}Docker Compose is not installed.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker Compose found${NC}"

# Create .env if not exists
echo -e "${YELLOW}Setting up environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env from .env.example${NC}"
else
    echo -e "${GREEN}✓ .env already exists${NC}"
fi

# Create certs directory
echo -e "${YELLOW}Setting up SSL certificates...${NC}"
mkdir -p certs

# Generate self-signed certificate if not exists
if [ ! -f certs/localhost.crt ] || [ ! -f certs/localhost.key ]; then
    echo "Generating self-signed certificate..."
    openssl req -x509 -newkey rsa:4096 -keyout certs/localhost.key \
        -out certs/localhost.crt -days 365 -nodes \
        -subj "/C=BR/ST=State/L=City/O=SOLPI/CN=localhost"
    echo -e "${GREEN}✓ Certificate generated${NC}"
else
    echo -e "${GREEN}✓ Certificate already exists${NC}"
fi

# Create required directories
echo -e "${YELLOW}Creating data directories...${NC}"
mkdir -p data/hermes data/redis data/openclaw

echo ""
echo -e "${GREEN}=== Setup Complete ===${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Review .env file and update API keys if needed"
echo "2. Run: docker-compose up -d"
echo "3. Wait 30-60 seconds for services to start"
echo "4. Check status: docker-compose ps"
echo ""
echo -e "${YELLOW}Access points:${NC}"
echo "  • Hermes UI:      http://localhost:8001"
echo "  • Hermes Gateway: http://localhost:8002"
echo "  • OpenClaw:       http://localhost:18789"
echo "  • Nginx (proxy):  http://localhost"
echo "  • Redis:          localhost:6379"
echo ""
echo -e "${YELLOW}Useful commands:${NC}"
echo "  • docker-compose logs -f hermes-agent"
echo "  • docker-compose logs -f openclaw"
echo "  • docker-compose ps"
echo "  • docker-compose down"
echo ""
