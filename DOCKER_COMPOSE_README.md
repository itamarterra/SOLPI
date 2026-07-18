# SOLPI Docker Compose — Local Development Environment

Configuração completa de Docker Compose para executar os 3 projetos principais (Hermes Agent, OpenClaude, OpenClaw) localmente com um único comando.

## 📋 Requisitos

- **Docker Desktop** 4.0+ ([download](https://www.docker.com/products/docker-desktop))
- **Docker Compose** 2.0+ (incluído no Docker Desktop)
- **4GB+ RAM** (recomendado 8GB)
- **10GB+ disco** para images e volumes

## 🚀 Quick Start

### Linux / macOS

```bash
# Setup
bash docker-compose-setup.sh

# Iniciar
docker-compose up -d

# Verificar
docker-compose ps
```

### Windows PowerShell

```powershell
# Setup
.\docker-compose-setup.ps1

# Iniciar
docker-compose up -d

# Verificar
docker-compose ps
```

## 📍 Portas & URLs

| Serviço | URL | Porta |
|---------|-----|-------|
| **Hermes UI** | http://localhost:8001 | 8001 |
| **Hermes Gateway** | http://localhost:8002 | 8002 |
| **OpenClaw Gateway** | http://localhost:18789 | 18789 |
| **Nginx Reverse Proxy** | http://localhost | 80 |
| **Redis** | redis://localhost:6379 | 6379 |

## 🔧 Configuração

### Environment Variables

Copie `.env.example` → `.env` e customize:

```bash
# Hermes Agent
HERMES_ENV=development
HERMES_LOG_LEVEL=info

# OpenClaude
CLAUDE_API_KEY=your_claude_api_key_here
CLAUDE_MODEL=claude-3-5-sonnet-20241022

# OpenClaw
OPENCLAW_EXTENSIONS=""  # Leave empty for base install
OPENCLAW_INSTALL_BROWSER=""  # Set to 1 to include Chromium (~300MB)
OPENCLAW_INSTALL_DOCKER_CLI=""  # Set to 1 if using sandbox features

# Redis
REDIS_PASSWORD=  # Leave empty for local development
```

## 🎯 Uso Comum

### Iniciar todos os serviços
```bash
docker-compose up -d
```

### Ver logs em tempo real
```bash
# Todos
docker-compose logs -f

# Serviço específico
docker-compose logs -f hermes-agent
docker-compose logs -f openclaw
docker-compose logs -f openclaude
```

### Parar tudo
```bash
docker-compose down
```

### Parar com limpeza de volumes
```bash
docker-compose down -v
```

### Reconstruir imagens
```bash
docker-compose build --no-cache
```

### Reiniciar um serviço
```bash
docker-compose restart hermes-agent
```

### Executar comando em container
```bash
docker-compose exec hermes-agent sh
docker-compose exec openclaw node --version
```

## 🐛 Troubleshooting

### Porta já em uso
```bash
# Encontrar processo usando porta 8001
lsof -i :8001  # macOS/Linux
netstat -ano | findstr :8001  # Windows

# Mudar porta em docker-compose.yml:
# Altere "8001:8001" → "8011:8001" (porta_host:porta_container)
```

### Container não inicia (Exit 1)
```bash
# Ver logs detalhados
docker-compose logs hermes-agent

# Verificar healthcheck
docker-compose ps

# Tentar reconstruir
docker-compose build --no-cache hermes-agent
docker-compose up -d hermes-agent
```

### Espaço em disco insuficiente
```bash
# Limpar images não usadas
docker image prune -a

# Limpar volumes não usados
docker volume prune

# Ver uso de espaço
docker system df
```

### OpenClaude não conecta à Claude API
- Verificar `CLAUDE_API_KEY` em `.env`
- Testar conexão: `docker-compose exec openclaude node -e "console.log(process.env.CLAUDE_API_KEY)"`

### OpenClaw build falha com `QEMU`
- Acontece em arquiteturas ARM (M1/M2 Mac) durante cross-compilation
- Dockerfile já trata isso com fallback (cria stub A2UI bundle)
- Normal e esperado — build continua

## 📊 Arquitetura

```
┌─────────────────────────────────────────┐
│         Docker Network: solpi-network   │
├─────────────────────────────────────────┤
│                                         │
│  ┌──────────────────────────────────┐  │
│  │      Nginx (Reverse Proxy)       │  │
│  │  :80 → :8001/:8002/:18789        │  │
│  └──────────────────────────────────┘  │
│              ↓                          │
│  ┌────────────────────────────────────┐ │
│  │      Hermes Agent                  │ │
│  │  :8001 (UI)                        │ │
│  │  :8002 (Gateway)                   │ │
│  │  :8003 (Internal)                  │ │
│  └────────────────────────────────────┘ │
│              ↓                          │
│  ┌────────────────────────────────────┐ │
│  │      OpenClaw                      │ │
│  │  :18789 (Gateway)                  │ │
│  └────────────────────────────────────┘ │
│              ↓                          │
│  ┌────────────────────────────────────┐ │
│  │      Redis (Cache/Sessions)        │ │
│  │  :6379                             │ │
│  └────────────────────────────────────┘ │
│              ↓                          │
│  ┌────────────────────────────────────┐ │
│  │      OpenClaude (CLI)              │ │
│  │  (CLI access)                      │ │
│  └────────────────────────────────────┘ │
│                                         │
└─────────────────────────────────────────┘

Named Volumes:
  • solpi-hermes-data (persistent data)
  • solpi-hermes-cache (model cache)
  • solpi-openclaw-workspace (projects)
  • solpi-openclaw-config (settings)
  • solpi-redis-data (session/cache)
```

## 💾 Volumes & Persistence

Todos os serviços usam **named volumes** para persisten dados entre reinicializações:

```bash
# Ver todos os volumes
docker volume ls | grep solpi

# Inspecionar um volume
docker volume inspect solpi-redis-data

# Backup de um volume
docker run -v solpi-hermes-data:/data -v $(pwd):/backup \
  alpine tar czf /backup/hermes-backup.tar.gz -C / data
```

## 🔐 SSL/TLS

### Auto-gerado (self-signed)

O setup script gera automaticamente um certificado self-signed em `certs/`:

```bash
certs/
  ├── localhost.crt  (public certificate)
  └── localhost.key  (private key)
```

### Usar certificado próprio

Substitua `certs/localhost.*` com seu próprio certificado:

```bash
# Exemplo com Let's Encrypt
cp /etc/letsencrypt/live/example.com/fullchain.pem certs/localhost.crt
cp /etc/letsencrypt/live/example.com/privkey.pem certs/localhost.key
```

## 📈 Performance Tuning

### Aumentar memória alocada para Docker
- **Docker Desktop → Preferences → Resources → Memory**: Set to 8GB+

### BuildKit (faster builds)
```bash
export DOCKER_BUILDKIT=1
docker-compose build
```

### Parallel builds
```bash
docker-compose build --parallel
```

## 🔄 CI/CD Integration

### GitHub Actions Example

```yaml
name: Docker Compose Test
on: [push, pull_request]
jobs:
  docker:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: docker/setup-buildx-action@v2
      - name: Build and test
        run: |
          docker-compose build
          docker-compose up -d
          docker-compose exec -T hermes-agent curl http://localhost:8001/health
          docker-compose down
```

## 📝 Logs

### Arquivo único com todos os logs
```bash
docker-compose logs > solpi-logs.txt
```

### Logs estruturados (JSON)
```bash
docker-compose logs --format json
```

### Últimas 100 linhas
```bash
docker-compose logs --tail=100 -f
```

## 🛑 Cleanup

```bash
# Stop e remove containers
docker-compose down

# Remove volumes (cuidado — apaga dados!)
docker-compose down -v

# Remove images também
docker-compose down --rmi all

# Limpeza completa do Docker
docker system prune -a --volumes
```

## 📚 Referências

- [Docker Compose Docs](https://docs.docker.com/compose/)
- [Docker CLI Reference](https://docs.docker.com/engine/reference/commandline/docker/)
- [Nginx Proxy Docs](https://nginx.org/en/docs/)
- [Redis Docker Image](https://hub.docker.com/_/redis)

## 💬 Suporte

Para problemas ou sugestões:
- 🐛 Abra uma issue no GitHub
- 📧 Consulte a documentação dos projetos individuais
- 🔗 Veja `.env.example` para todas as variáveis disponíveis

---

**Versão:** 1.0  
**Última atualização:** 2024  
**Projeto:** SOLPI
