@echo off
echo ===========================================
echo   SOLPI AGENT - SINCRONIZADOR AUTOMATICO
echo ===========================================
echo.

:: Verifica se o Git esta instalado
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERRO] O Git nao esta instalado ou nao foi encontrado no PATH.
    echo Por favor, instale o Git em: https://git-scm.com/download/win
    pause
    exit
)

echo [1/3] Preparando arquivos...
git add .

echo [2/3] Criando ponto de salvamento...
git commit -m "Evolucao do Agente SOLPI - Sincronismo Automatico"

echo [3/3] Enviando para o GitHub...
git push origin main

if %errorlevel% neq 0 (
    echo.
    echo [AVISO] Houve um problema no envio.
    echo Verifique se voce esta logado no GitHub e se a internet esta ativa.
) else (
    echo.
    echo [SUCESSO] Seu Agente esta na nuvem!
)

echo.
pause
