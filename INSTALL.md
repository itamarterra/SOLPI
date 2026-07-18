# 📥 SOLPI - Guia de Instalação Manual no Windows

## ⚠️ Importante

Se você não tem **PHP**, **Composer** e **Git** instalados, siga estes passos.

---

## 🔧 Instalação Rápida (com Chocolatey)

Se já tem Chocolatey instalado, abra **PowerShell como Administrator** e execute:

```powershell
choco install php composer git -y
```

---

## 📥 Instalação Manual

### 1️⃣ Instalar PHP 8.3

**Opção A: Via Windows Installer (Recomendado)**
1. Acesse: https://www.php.net/downloads
2. Baixe: **Windows Installer (x64)**
3. Execute e siga os passos
4. Instale em: `C:\php83`

**Opção B: Portable (sem instalação)**
1. Acesse: https://windows.php.net/download/
2. Baixe: **Thread Safe (x64)** para sua versão
3. Extraia em: `C:\php83`
4. Adicione ao PATH (veja abaixo)

**Verificar instalação:**
```bash
php --version
```

---

### 2️⃣ Adicionar PHP ao PATH (se instalação manual)

**Windows 10/11:**
1. Pressione `Win + X` → `Sistema`
2. Clique em: `Configurações avançadas do sistema`
3. Clique em: `Variáveis de Ambiente`
4. Edite variável `Path`:
   - Adicione: `C:\php83`
5. Clique OK
6. **Reinicie o PowerShell**

---

### 3️⃣ Instalar Composer

**Opção A: Installer (Recomendado)**
1. Acesse: https://getcomposer.org/download/
2. Baixe e execute: **Composer-Setup.exe**
3. Deixe usar PHP automático
4. Instale

**Opção B: Manual**
1. Acesse: https://getcomposer.org/download/
2. Baixe: **composer.phar**
3. Salve em: `C:\composer\` (ou onde preferir)
4. Crie script batch em `C:\composer\composer.bat`:
   ```batch
   @echo off
   php "%~dp0composer.phar" %*
   ```
5. Adicione `C:\composer\` ao PATH

**Verificar instalação:**
```bash
composer --version
```

---

### 4️⃣ Instalar Git

**Windows:**
1. Acesse: https://git-scm.com/download/win
2. Baixe e execute o instalador
3. Use valores padrão
4. Clique Finish

**Verificar instalação:**
```bash
git --version
```

---

## 🚀 Depois de Instalar Tudo

### 1. Abra um NOVO PowerShell
(importante para recarregar as variáveis PATH)

### 2. Navegue até o SOLPI
```bash
cd C:\SOLPI
```

### 3. Instale dependências do projeto
```bash
composer install
```

Isso vai instalar:
- ✅ PHPUnit 11
- ✅ PHPStan 2
- ✅ PHPCPD 6
- ✅ Todas as dependências do plugin

### 4. Verifique instalação
```bash
composer run-script test
```

Se passar, tudo funciona! ✅

---

## 🐛 Troubleshooting

### "php: O termo não é reconhecido"
- Verifique se PHP está no PATH
- Reinicie o PowerShell
- Execute: `echo $env:Path` para ver variáveis

### "composer: O termo não é reconhecido"
- Reinstale Composer
- Ou adicione manualmente ao PATH

### "composer install" não funciona
```bash
# Limpar cache
composer clearcache

# Tentar novamente
composer install --verbose
```

### Memory limit
```bash
php -d memory_limit=512M ./vendor/bin/phpunit
```

---

## ✅ Verificação Final

Execute isto para confirmar que tudo funciona:

```bash
cd C:\SOLPI
php --version
composer --version
git --version
composer run-script test
```

Se todos os comandos funcionam e os testes passam, ✅ você está pronto!

---

## 📚 Links Úteis

- PHP: https://www.php.net/
- Composer: https://getcomposer.org/
- Git: https://git-scm.com/
- SOLPI README: Veja README.md
- Testes: Veja TESTING.md

---

**Precisa de help? Veja STATUS.md ou TESTING.md**
