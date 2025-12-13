@echo off
echo ========================================
echo Abriendo puerto 8001 en el Firewall
echo ========================================
echo.

netsh advfirewall firewall add rule name="Laravel API 8001" dir=in action=allow protocol=TCP localport=8001

if %ERRORLEVEL% EQU 0 (
    echo ✅ Regla de firewall agregada correctamente
) else (
    echo ⚠️  La regla puede que ya exista o hubo un error
)

echo.
echo Presiona cualquier tecla para continuar...
pause >nul

