@echo off
echo ========================================
echo Solucionando Problema de Firewall
echo ========================================
echo.
echo Este script debe ejecutarse como ADMINISTRADOR
echo.
pause

echo.
echo Agregando regla de firewall para puerto 8001...
netsh advfirewall firewall delete rule name="Laravel API 8001" >nul 2>&1
netsh advfirewall firewall add rule name="Laravel API 8001" dir=in action=allow protocol=TCP localport=8001

if %ERRORLEVEL% EQU 0 (
    echo ✅ Regla de firewall agregada correctamente
) else (
    echo ❌ Error al agregar regla. Ejecuta como Administrador.
    pause
    exit /b 1
)

echo.
echo Verificando regla...
netsh advfirewall firewall show rule name="Laravel API 8001"

echo.
echo ========================================
echo Verificando que Laravel esté corriendo...
echo ========================================
netstat -ano | findstr :8001

echo.
echo ========================================
echo Prueba desde tu móvil:
echo http://192.168.0.129:8001/api/ping
echo ========================================
echo.
pause

