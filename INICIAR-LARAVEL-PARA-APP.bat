@echo off
echo ========================================
echo Iniciando Laravel para App Movil
echo ========================================
echo.
echo IMPORTANTE: Este servidor escucha en TODAS las interfaces (0.0.0.0)
echo para que la app movil pueda conectarse desde cualquier dispositivo
echo en la misma red.
echo.
echo Para encontrar tu IP local, ejecuta: ipconfig
echo Busca "Direccion IPv4" (generalmente 192.168.x.x)
echo.
echo La app movil debe usar: http://TU_IP:8001/api
echo.
echo Presiona Ctrl+C para detener el servidor
echo.
echo ========================================
echo.

cd /d "%~dp0"
php artisan serve --host=0.0.0.0 --port=8001

pause
