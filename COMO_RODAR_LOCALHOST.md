# 🚀 Como Rodar SOLPI Localhost

## ⚠️ Status Atual

**Nenhum container está rodando!** Você precisa iniciar o docker-compose.

---

## 🎯 Opção 1: Usar a Configuração Padrão (Recomendado)

### Portas Disponíveis:
- **http://localhost** — Nginx Reverse Proxy (porta 80)
- **http://localhost:8001** — Hermes Agent UI
- **http://localhost:8002** — Hermes Gateway API
- **http://localhost:18789** — OpenClaw Gateway
- **localhost:6379** — Redis

### Iniciar:

**Windows PowerShell:**
```powershell
cd C:\SOLPI
.\docker-compose-setup.ps1
docker-compose up -d
docker-compose ps
```

**Linux / macOS:**
```bash
cd /path/to/SOLPI
bash docker-compose-setup.sh
docker-compose up -d
docker-compose ps
```

### Esperar 60-90 segundos para os serviços iniciarem

```bash
docker-compose logs -f
```

Quando ver `healthy` nos logs, tente acessar:
```
http://localhost:8001
```

---

## 🎯 Opção 2: Usar Porta 8090 (Custom)

Se você quer especificamente a porta 8090, temos 2 opções:

### A) Mapear Nginx para 8090

Edite `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8090:80"    # Muda de 80:80 para 8090:80
    - "443:443"
```

Depois acesse:
```
http://localhost:8090
```

### B) Criar um docker-compose customizado

Vou criar um arquivo `docker-compose.local.yml` com a porta 8090:

```bash
docker-compose -f docker-compose.local.yml up -d
```

---

## 📋 Próximas Ações

### 1. Verificar se Docker está rodando:
```bash
docker --version
docker ps
```

### 2. Fazer o setup (primeira vez):
**Windows:**
```powershell
.\docker-compose-setup.ps1
```

**Linux/macOS:**
```bash
bash docker-compose-setup.sh
```

### 3. Iniciar containers:
```bash
docker-compose up -d
```

### 4. Monitorar logs:
```bash
docker-compose logs -f
```

### 5. Acessar:
```
http://localhost:8001  (Hermes UI)
```

---

## 🐛 Troubleshooting

### Nenhum container inicia
```bash
# Ver erro detalhado
docker-compose up
# (sem -d para ver logs)
```

### Porta 80 já está em uso
Use uma porta diferente em `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8090:80"  # ao invés de 80:80
```

### Não consigo acessar http://localhost:8001
1. Verifique se container está rodando: `docker ps`
2. Verifique logs: `docker-compose logs hermes-agent`
3. Aguarde 60 segundos (startup delay)

### "docker: command not found"
- Windows: Instale Docker Desktop
- Linux: `sudo apt-get install docker.io`
- macOS: Instale Docker Desktop

---

## ⏹️ Parar os containers

```bash
docker-compose down
```

Remover volumes também (cuidado — apaga dados):
```bash
docker-compose down -v
```

---

## 📞 Precisa de Help?

1. Leia `DOCKER_COMPOSE_README.md` para guia completo
2. Leia `LOCALHOST_SETUP_COMPLETE.md` para arquitetura
3. Verifique logs: `docker-compose logs -f`

---

**Escolha uma das opções acima e comece!** 🚀
