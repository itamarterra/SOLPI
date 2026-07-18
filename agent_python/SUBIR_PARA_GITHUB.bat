@echo off
echo ===========================================
echo   SOLPI OS - HYBRID SYNC (SOLPI + HERMES)
echo ===========================================
echo.

:: Detecta caminho do Git
set GIT_CMD="C:\Program Files\Git\cmd\git.exe"
if not exist %GIT_CMD% (
    set GIT_CMD=git
)

echo [1/3] Indexando nova arquitetura hibrida...
%GIT_CMD% add .

echo [2/3] Consolidando nucleo de alta performance...
%GIT_CMD% commit -m "feat: SOLPI OS v6.0 HYBRID - Integrated Hermes-Agent Engine into AI Core"

echo [3/3] Sincronizando com a Nuvem...
%GIT_CMD% push origin main

if %errorlevel% neq 0 (
    echo.
    echo [AVISO] Houve um problema no sincronismo. Verifique login/internet.
) else (
    echo.
    echo [SUCESSO] SOLPI OS v6 Hibrido esta salvo no GitHub!
)

echo.
pause
