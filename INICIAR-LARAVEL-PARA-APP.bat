@echo off
echo ========================================
echo  INICIANDO LARAVEL PARA APP MOVIL
echo ========================================
echo.
echo Este script inicia Laravel en el puerto 8000
echo y lo hace accesible desde la red local
echo para que la app movil pueda conectarse.
echo.
echo IMPORTANTE: Tu celular debe estar en la misma red WiFi
echo.

cd /d "%~dp0"

echo Verificando Laravel...
if not exist "artisan" (
    echo ERROR: No se encuentra Laravel en esta carpeta
    pause
    exit /b 1
)

echo.
echo Iniciando servidor Laravel...
echo URL local: http://localhost:8000
echo URL red local: http://10.90.49.140:8000
echo.
echo Presiona Ctrl+C para detener el servidor
echo.

php artisan serve --host=0.0.0.0 --port=8000

