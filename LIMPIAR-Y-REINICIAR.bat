@echo off
title Limpiar Cache y Reiniciar Laravel
color 0C

echo ========================================
echo   LIMPIANDO CACHE Y REINICIANDO
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Limpiando cache de Laravel...
call php artisan route:clear
call php artisan config:clear
call php artisan cache:clear
echo ✅ Cache limpiado
echo.

echo [2/3] Verificando rutas...
call php artisan route:list --path=api/transportista
echo.

echo [3/3] Iniciando Laravel...
echo.
echo ⚠️  DEJA ESTA VENTANA ABIERTA
echo.

call php artisan serve --host=0.0.0.0 --port=8000

pause







