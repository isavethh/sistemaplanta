# 🔥 SOLUCIÓN AL PROBLEMA DE FIREWALL

## El Problema
El firewall de Windows está bloqueando las conexiones desde tu dispositivo móvil al puerto 8001.

## Solución Rápida

### Opción 1: Script Automático (RECOMENDADO)

1. **Abre el archivo:** `ABRIR_FIREWALL_AHORA.bat`
2. **Click derecho** → **Ejecutar como administrador**
3. Espera a que termine
4. Prueba la app móvil nuevamente

### Opción 2: Manual (PowerShell como Administrador)

Abre PowerShell como **Administrador** y ejecuta:

```powershell
netsh advfirewall firewall delete rule name="Laravel API 8001"
netsh advfirewall firewall add rule name="Laravel API 8001" dir=in action=allow protocol=TCP localport=8001
```

### Opción 3: Desde el Panel de Control

1. Abre **Windows Defender Firewall**
2. Click en **Configuración avanzada**
3. Click en **Reglas de entrada** → **Nueva regla**
4. Selecciona **Puerto** → **Siguiente**
5. Selecciona **TCP** y escribe **8001** → **Siguiente**
6. Selecciona **Permitir la conexión** → **Siguiente**
7. Marca todas las casillas → **Siguiente**
8. Nombre: **Laravel API 8001** → **Finalizar**

## Verificar que Funcionó

Ejecuta en PowerShell:
```powershell
netsh advfirewall firewall show rule name="Laravel API 8001"
```

Deberías ver la regla listada.

## Prueba Final

Desde tu dispositivo móvil, abre el navegador y ve a:
```
http://192.168.0.129:8001/api/ping
```

Deberías ver un JSON con `"success": true`.

## Si Aún No Funciona

1. Verifica que Laravel esté corriendo:
   ```bash
   netstat -ano | findstr :8001
   ```
   Debe mostrar: `TCP    0.0.0.0:8001`

2. Verifica que estés en la misma red WiFi

3. Prueba desactivar temporalmente el firewall para confirmar que ese es el problema:
   ```powershell
   # Solo para prueba, NO dejes desactivado
   netsh advfirewall set allprofiles state off
   ```

