# Solución Alternativa al Problema de Red

## Cambios Realizados

### 1. Middleware CORS Forzado
- Creado `app/Http/Middleware/ForceCors.php`
- Agregado al stack de middleware en `bootstrap/app.php`
- Fuerza headers CORS en todas las respuestas

### 2. Configuración de CORS Mejorada
- Actualizado `config/cors.php` para incluir todas las rutas

### 3. Cliente API Mejorado
- Cambiado `getByTransportista` para usar `fetch` primero
- Fallback a `axios` si `fetch` falla
- Timeout aumentado a 10 segundos
- Mejor logging de errores

## Prueba Ahora

1. **Reinicia Laravel:**
   ```bash
   # Detén el servidor actual (Ctrl+C)
   # Luego inicia de nuevo:
   php artisan serve --host=0.0.0.0 --port=8001
   ```

2. **Reinicia la App Móvil:**
   - Cierra completamente la app
   - Vuelve a abrirla

3. **Verifica los logs:**
   - Deberías ver más información en la consola
   - Los logs ahora muestran el status HTTP y más detalles

## Si Aún No Funciona

### Opción A: Usar Túnel de Expo
```bash
cd applanta/mobile-app
npm run start:tunnel
```
Esto crea un túnel público que puede acceder a tu servidor local.

### Opción B: Verificar Red
- Asegúrate de que el móvil esté en la misma red WiFi
- Prueba desactivar temporalmente el firewall de Windows
- Verifica que no haya un proxy o VPN activo

### Opción C: Usar ngrok
```bash
# Instala ngrok: https://ngrok.com/
ngrok http 8001
# Usa la URL que te da ngrok en api.js
```

