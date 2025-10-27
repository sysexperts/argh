@echo off
echo ========================================
echo Business Manager - Deployment Package
echo ========================================
echo.

REM Zielordner
set DEPLOY_DIR=..\business-manager-deploy

REM Lösche alten Deploy-Ordner falls vorhanden
if exist "%DEPLOY_DIR%" (
    echo Loesche alten Deployment-Ordner...
    rmdir /s /q "%DEPLOY_DIR%"
)

REM Erstelle neuen Deploy-Ordner
echo Erstelle Deployment-Ordner...
mkdir "%DEPLOY_DIR%"

REM Kopiere alle wichtigen Dateien
echo Kopiere Dateien...
xcopy /E /I /Y bootstrap "%DEPLOY_DIR%\bootstrap"
xcopy /E /I /Y core "%DEPLOY_DIR%\core"
xcopy /E /I /Y database "%DEPLOY_DIR%\database"
xcopy /E /I /Y public "%DEPLOY_DIR%\public"
xcopy /E /I /Y resources "%DEPLOY_DIR%\resources"
xcopy /E /I /Y routes "%DEPLOY_DIR%\routes"
xcopy /E /I /Y vendor "%DEPLOY_DIR%\vendor"

REM Kopiere einzelne Dateien
copy /Y .env.example "%DEPLOY_DIR%\.env.example"
copy /Y composer.json "%DEPLOY_DIR%\composer.json"
copy /Y composer.lock "%DEPLOY_DIR%\composer.lock"
copy /Y DEPLOYMENT.md "%DEPLOY_DIR%\DEPLOYMENT.md"
copy /Y README_KUNDE.md "%DEPLOY_DIR%\README_KUNDE.md"

REM Lösche .git aus vendor (falls vorhanden)
if exist "%DEPLOY_DIR%\vendor\.git" (
    rmdir /s /q "%DEPLOY_DIR%\vendor\.git"
)

REM Erstelle leere .env Datei
echo. > "%DEPLOY_DIR%\.env"

REM Erstelle STRATO_INSTALLATION.txt
(
echo ========================================
echo STRATO INSTALLATION - ANLEITUNG
echo ========================================
echo.
echo 1. Alle Dateien via FTP hochladen nach:
echo    /(deine-domain^)/
echo.
echo 2. Browser oeffnen:
echo    https://deine-domain.de/public/install.php
echo.
echo 3. Installations-Wizard durchlaufen
echo.
echo 4. Nach Installation loeschen:
echo    /public/install.php
echo.
echo WICHTIG:
echo - Document Root muss auf /public/ zeigen
echo - Falls nicht moeglich, siehe DEPLOYMENT.md
echo.
echo Support: support@sys-experts.de
echo ========================================
) > "%DEPLOY_DIR%\STRATO_INSTALLATION.txt"

echo.
echo ========================================
echo Fertig!
echo ========================================
echo.
echo Deployment-Paket erstellt in:
echo %DEPLOY_DIR%
echo.
echo Naechste Schritte:
echo 1. Ordner zippen: business-manager-deploy.zip
echo 2. Via FTP zu Strato hochladen
echo 3. Entpacken auf dem Server
echo 4. Browser: https://deine-domain.de/public/install.php
echo.
pause
