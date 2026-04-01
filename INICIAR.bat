@echo off
echo ========================================
echo   GAC - Gestor Automatizado de Codigos
echo ========================================
echo.

REM Verificar si existe .env
if not exist .env (
    echo [ERROR] Archivo .env no encontrado!
    echo.
    echo Por favor crea el archivo .env con las siguientes variables:
    echo.
    echo DB_HOST=localhost
    echo DB_PORT=3306
    echo DB_NAME=gac_operational
    echo DB_USER=root
    echo DB_PASSWORD=tu_password
    echo.
    echo WAREHOUSE_DB_HOST=localhost
    echo WAREHOUSE_DB_PORT=3306
    echo WAREHOUSE_DB_NAME=gac_warehouse
    echo WAREHOUSE_DB_USER=root
    echo WAREHOUSE_DB_PASSWORD=tu_password
    echo.
    echo APP_ENV=development
    echo APP_NAME=GAC
    echo APP_URL=http://localhost:8000
    echo.
    pause
    exit /b 1
)

REM Verificar si existe vendor/autoload.php
if not exist vendor\autoload.php (
    echo [INFO] Instalando dependencias de Composer...
    composer install
    if errorlevel 1 (
        echo [ERROR] Error al instalar dependencias
        pause
        exit /b 1
    )
)

echo [INFO] Iniciando servidor en http://localhost:8000
echo [INFO] Presiona Ctrl+C para detener el servidor
echo.

php -S localhost:8000 -t public public/router.php