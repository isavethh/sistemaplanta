@echo off
echo ========================================
echo ABRIENDO FIREWALL PARA PUERTO 8001
echo ========================================
echo.
echo IMPORTANTE: Ejecuta este archivo como ADMINISTRADOR
echo (Click derecho - Ejecutar como administrador)
echo.
pause

echo.
echo Eliminando regla anterior si existe...
netsh advfirewall firewall delete rule name="Laravel API 8001" >nul 2>&1

echo.
echo Agregando nueva regla de firewall...
netsh advfirewall firewall add rule name="Laravel API 8001" dir=in action=allow protocol=TCP localport=8001

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ ✅ ✅ REGLA DE FIREWALL AGREGADA CORRECTAMENTE ✅ ✅ ✅
    echo.
    echo Verificando regla...
    netsh advfirewall firewall show rule name="Laravel API 8001"
    echo.
    echo ========================================
    echo AHORA PRUEBA LA APP MÓVIL
    echo ========================================
) else (
    echo.
    echo ❌ ERROR: No se pudo agregar la regla
    echo.
    echo Asegúrate de ejecutar este archivo como ADMINISTRADOR
    echo (Click derecho - Ejecutar como administrador)
)

echo.
pause

