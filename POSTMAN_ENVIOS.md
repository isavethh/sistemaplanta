# 📮 Consultas Postman para Crear Envíos en plantaCruds

Base URL: `http://bomberos.dasalas.shop/api` (o tu URL local)

---

## 🎯 Opción 1: Crear Envío Estándar (POST /api/envios)

Esta es la ruta REST estándar para crear envíos.

### Request

**Método:** `POST`  
**URL:** `http://bomberos.dasalas.shop/api/envios`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "almacen_destino_id": 1,
  "categoria": "Verduras",
  "fecha_estimada_entrega": "2025-12-20",
  "hora_estimada": "14:00",
  "observaciones": "Envío desde Trazabilidad - Pedido #12345",
  "origen": "trazabilidad",
  "numero_pedido_trazabilidad": "PED-12345",
  "productos": [
    {
      "producto_nombre": "Tomate",
      "cantidad": 50,
      "peso_kg": 0.2,
      "precio": 5.50
    },
    {
      "producto_nombre": "Lechuga",
      "cantidad": 30,
      "peso_kg": 0.3,
      "precio": 4.00
    }
  ]
}
```

### Campos Requeridos:
- `almacen_destino_id` (integer) - ID del almacén destino (debe existir)
- `fecha_estimada_entrega` (date) - Fecha en formato YYYY-MM-DD
- `productos` (array) - Mínimo 1 producto

### Campos Opcionales:
- `categoria` (string) - Categoría del envío
- `hora_estimada` (string) - Hora en formato HH:mm
- `observaciones` (string) - Notas adicionales
- `origen` (string) - "trazabilidad" o "manual"
- `numero_pedido_trazabilidad` (string) - Número de pedido de Trazabilidad
- `productos[].producto_id` (integer) - ID del producto (opcional, se puede usar solo producto_nombre)
- `productos[].peso_kg` (number) - Peso por unidad en kg

### Respuesta Exitosa (201):
```json
{
  "success": true,
  "message": "Envío creado exitosamente",
  "data": {
    "id": 123,
    "codigo": "TRAZ-251216-ABC123",
    "almacen_destino_id": 1,
    "estado": "pendiente_aprobacion_trazabilidad",
    "fecha_creacion": "2025-12-16",
    "fecha_estimada_entrega": "2025-12-20",
    "total_cantidad": 80,
    "total_peso": 19.0,
    "total_precio": 395.00,
    "productos": [...]
  },
  "qr_code": "data:image/png;base64,...",
  "estado": "pendiente_aprobacion_trazabilidad",
  "mensaje": "Envío creado. Debe ser aprobado por Trazabilidad antes de asignar transportista.",
  "propuesta_vehiculos_url": "http://bomberos.dasalas.shop/api/envios/123/propuesta-vehiculos-pdf"
}
```

---

## 🎯 Opción 2: Crear Envío desde Pedido de Almacén (POST /api/pedido-almacen)

Esta ruta está diseñada específicamente para recibir pedidos desde sistemas externos como Trazabilidad.

### Request

**Método:** `POST`  
**URL:** `http://bomberos.dasalas.shop/api/pedido-almacen`  
**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON) - Ejemplo Completo:**
```json
{
  "codigo": "PED-TRAZ-12345",
  "almacen_destino": "Almacén Centro",
  "almacen_destino_lat": -17.7892,
  "almacen_destino_lng": -63.1751,
  "almacen_destino_direccion": "Av. Principal #123, Santa Cruz",
  "origen": "trazabilidad",
  "origen_lat": -17.7833,
  "origen_lng": -63.1821,
  "origen_direccion": "Planta Trazabilidad",
  "fecha_requerida": "2025-12-20",
  "hora_requerida": "14:00",
  "observaciones": "Pedido urgente desde Trazabilidad",
  "total_cantidad": 80,
  "total_peso": 19.5,
  "total_precio": 395.00,
  "productos": [
    {
      "producto_nombre": "Tomate",
      "cantidad": 50,
      "peso_unitario": 0.2,
      "precio_unitario": 5.50,
      "total_peso": 10.0,
      "total_precio": 275.00
    },
    {
      "producto_nombre": "Lechuga",
      "cantidad": 30,
      "peso_unitario": 0.3,
      "precio_unitario": 4.00,
      "total_peso": 9.0,
      "total_precio": 120.00
    }
  ],
  "webhook_url": "https://trazabilidad.com/webhook"
}
```

### Campos Requeridos:
- `almacen_destino` (string) - Nombre del almacén destino (se crea si no existe)
- `fecha_requerida` (date) - Fecha en formato YYYY-MM-DD
- `productos` (array) - Mínimo 1 producto

### Campos Opcionales:
- `codigo` (string) - Código del pedido (si no se envía, se genera automáticamente)
- `codigo_origen` (string) - Código original del pedido
- `almacen_destino_lat` (number) - Latitud del almacén destino
- `almacen_destino_lng` (number) - Longitud del almacén destino
- `almacen_destino_direccion` (string) - Dirección completa del almacén
- `origen` (string) - "trazabilidad" para envíos desde Trazabilidad
- `origen_lat` (number) - Latitud del punto de origen
- `origen_lng` (number) - Longitud del punto de origen
- `origen_direccion` (string) - Dirección del punto de origen
- `hora_requerida` (string) - Hora en formato HH:mm
- `observaciones` (string) - Notas adicionales
- `total_cantidad` (integer) - Total de unidades
- `total_peso` (number) - Peso total en kg
- `total_precio` (number) - Precio total
- `webhook_url` (string) - URL para notificar cuando se cree el envío
- `solicitante_id` (integer) - ID del usuario solicitante
- `solicitante_nombre` (string) - Nombre del solicitante
- `solicitante_email` (string) - Email del solicitante

### Respuesta Exitosa (200):
```json
{
  "success": true,
  "message": "Pedido recibido y envío creado correctamente",
  "envio_id": 123,
  "codigo": "PED-TRAZ-12345",
  "estado": "pendiente_aprobacion_trazabilidad",
  "fecha_creacion": "2025-12-16T10:30:00.000000Z",
  "fecha_estimada_entrega": "2025-12-20",
  "almacen_destino": "Almacén Centro",
  "destino_lat": -17.7892,
  "destino_lng": -63.1751,
  "destino_direccion": "Av. Principal #123, Santa Cruz",
  "origen_lat": -17.7833,
  "origen_lng": -63.1821,
  "origen_direccion": "Planta Trazabilidad"
}
```

---

## 📋 Ejemplo Simplificado para Trazabilidad

### Request Mínimo:
```json
{
  "almacen_destino": "Almacén Centro",
  "fecha_requerida": "2025-12-20",
  "origen": "trazabilidad",
  "productos": [
    {
      "producto_nombre": "Tomate",
      "cantidad": 50,
      "peso_unitario": 0.2,
      "precio_unitario": 5.50
    }
  ]
}
```

---

## 🔄 Flujo Completo de Trazabilidad

### 1. Crear Envío
**POST** `/api/pedido-almacen` o `/api/envios`

### 2. Obtener Propuesta de Vehículos (PDF)
**GET** `/api/envios/{id}/propuesta-vehiculos-pdf`

### 3. Aprobar o Rechazar Propuesta
**POST** `/api/envios/{id}/aprobar-rechazar`
```json
{
  "accion": "aprobar",
  "observaciones": "Propuesta aprobada, proceder con asignación"
}
```

O para rechazar:
```json
{
  "accion": "rechazar",
  "observaciones": "Rechazado por falta de vehículos disponibles"
}
```

### 4. Consultar Estado del Envío
**GET** `/api/envios/{id}`

---

## 📝 Notas Importantes

1. **Estado Inicial:** Si `origen: "trazabilidad"`, el envío se crea con estado `pendiente_aprobacion_trazabilidad`

2. **Código:** Si no envías `codigo`, se genera automáticamente con formato:
   - Trazabilidad: `TRAZ-YYMMDD-XXXXXX`
   - Normal: `ENV-YYMMDD-XXXXXX`

3. **Almacén Destino:** Si el almacén no existe, se crea automáticamente con el nombre proporcionado

4. **Productos:** Si un producto no existe, se crea automáticamente en la categoría "General"

5. **Propuesta de Vehículos:** Se genera automáticamente cuando `origen: "trazabilidad"`

6. **Sincronización:** El envío se sincroniza automáticamente con el backend de Node.js

---

## 🧪 Ejemplos de Prueba en Postman

### Colección de Postman

Puedes importar esta colección en Postman:

```json
{
  "info": {
    "name": "plantaCruds - Envíos",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Crear Envío desde Trazabilidad",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"almacen_destino\": \"Almacén Centro\",\n  \"fecha_requerida\": \"2025-12-20\",\n  \"origen\": \"trazabilidad\",\n  \"productos\": [\n    {\n      \"producto_nombre\": \"Tomate\",\n      \"cantidad\": 50,\n      \"peso_unitario\": 0.2,\n      \"precio_unitario\": 5.50\n    }\n  ]\n}"
        },
        "url": {
          "raw": "{{base_url}}/pedido-almacen",
          "host": ["{{base_url}}"],
          "path": ["pedido-almacen"]
        }
      }
    },
    {
      "name": "Obtener Propuesta de Vehículos",
      "request": {
        "method": "GET",
        "header": [],
        "url": {
          "raw": "{{base_url}}/envios/:id/propuesta-vehiculos-pdf",
          "host": ["{{base_url}}"],
          "path": ["envios", ":id", "propuesta-vehiculos-pdf"],
          "variable": [
            {
              "key": "id",
              "value": "123"
            }
          ]
        }
      }
    },
    {
      "name": "Aprobar Propuesta",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"accion\": \"aprobar\",\n  \"observaciones\": \"Propuesta aprobada\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/envios/:id/aprobar-rechazar",
          "host": ["{{base_url}}"],
          "path": ["envios", ":id", "aprobar-rechazar"],
          "variable": [
            {
              "key": "id",
              "value": "123"
            }
          ]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://bomberos.dasalas.shop/api"
    }
  ]
}
```

---

## ⚠️ Errores Comunes

### Error 422 - Validación
```json
{
  "success": false,
  "message": "Error de validación: El almacén destino es requerido",
  "errors": {
    "almacen_destino_id": ["El almacén destino es requerido"]
  }
}
```

### Error 404 - Almacén no encontrado
```json
{
  "success": false,
  "message": "El almacén destino no existe"
}
```

### Error 500 - Error del servidor
```json
{
  "success": false,
  "message": "Error al crear envío: [mensaje de error]"
}
```

---

**Última actualización:** Diciembre 2025

