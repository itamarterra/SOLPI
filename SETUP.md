# 🚀 SOLPI - Setup Rápido no Windows

## Situação Atual
- ✅ Git está instalado
- ❌ PHP 8.3 não está instalado  
- ❌ Composer não está instalado

## ⚡ Setup em 5 Minutos

### Opção 1: Usar Scoop (Mais Fácil)

```powershell
# 1. Instalar Scoop (copie e cole no PowerShell)
iwr -useb get.scoop.sh | iex

# 2. Instalar PHP e Composer
scoop install php composer

# 3. Pronto! Abra novo PowerShell e execute:
php --version
composer --version
```

### Opção 2: Instaladores Oficiais (Manual)

#### Passo 1: Baixe PHP 8.3
1. Acesse: https://windows.php.net/download/
2. Clique em: **PHP 8.3 Thread Safe (x64)**
3. Baixe o `.zip`
4. Extraia em: `C:\php83`

#### Passo 2: Adicione ao PATH

1. Pressione: `Win + X` → selecione **Sistema**
2. Clique em: **Configurações avançadas do sistema**
3. Clique em: **Variáveis de Ambiente** (botão no canto inferior)
4. Clique em: **Path** (em Variáveis de Usuário) → **Editar**
5. Clique em: **Novo** e adicione: `C:\php83`
6. Clique OK 3 vezes
7. **Feche e abra novo PowerShell**

#### Passo 3: Baixe Composer

1. Acesse: https://getcomposer.org/download/
2. Clique em: **Composer-Setup.exe**
3. Execute e deixe ele encontrar PHP automaticamente
4. Clique Finish

#### Passo 4: Verifique
```powershell
php --version
composer --version
```

### Opção 3: Chocolatey (Se tiver instalado)

```powershell
# Abra PowerShell como ADMINISTRATOR e execute:
choco install php composer -y
```

---

## ✅ Depois de Instalar PHP e Composer

### 1. Abra PowerShell NOVO

### 2. Vá para o SOLPI
```powershell
cd C:\SOLPI
```

### 3. Instale dependências do projeto
```powershell
composer install
```

Isso vai instalar:
- PHPUnit 11 (testes)
- PHPStan (análise estática)
- PHPCPD (detecção de duplicação)

### 4. Rode os testes
```powershell
composer run-script test
```

Se passar: ✅ **Pronto!**

---

## 🔧 Verificar Setup

Copie e cole isto no PowerShell:

```powershell
php --version
composer --version
git --version
cd C:\SOLPI
composer install
composer run-script test
```

---

## ❓ Dúvidas?

| Problema | Solução |
|----------|---------|
| "php: termo não reconhecido" | Adicione `C:\php83` ao PATH (veja acima) |
| "composer: termo não reconhecido" | Reinstale Composer ou execute `refreshenv` |
| "composer install" falha | Execute: `composer clearcache` depois tente novamente |
| Memory limit erro | Execute: `php -d memory_limit=512M ./vendor/bin/phpunit` |

---

## 🎯 Comandos Úteis

```powershell
# Limpar PATH e recarregar
refreshenv

# Ver PATH atual
$env:Path

# Testar PHP
php -v
php -m  # lista extensões

# Testar Composer
composer diagnose

# Rodar testes
composer run-script test
composer run-script test:coverage
composer run-script check
```

---

## 📚 Documentação

- `README.md` — Visão geral do plugin
- `TESTING.md` — Guia de testes
- `STATUS.md` — Quick reference
- `INSTALL.md` — Instruções detalhadas

---

**Assim que PHP e Composer estiverem instalados, execute na pasta SOLPI:**

```powershell
composer install
composer run-script test
```

Pronto! 🎉
