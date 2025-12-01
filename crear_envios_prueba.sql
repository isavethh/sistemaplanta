-- Script para crear envíos de prueba y asignarlos a transportistas
-- Ejecutar en Laravel con: php artisan db:statement --file=crear_envios_prueba.sql
-- O copiar y pegar en php artisan tinker

-- 1. Crear envío de prueba
INSERT INTO envios (codigo, almacen_destino_id, categoria, fecha_creacion, fecha_estimada_entrega, hora_estimada, estado, total_cantidad, total_peso, total_precio, observaciones, created_at, updated_at)
VALUES 
('ENV-' || TO_CHAR(NOW(), 'YYYYMMDD') || '-001', 1, 'Alimentos', CURRENT_DATE, CURRENT_DATE + INTERVAL '2 days', '14:00', 'asignado', 10, 250.5, 1500.00, 'Envío de prueba 1', NOW(), NOW()),
('ENV-' || TO_CHAR(NOW(), 'YYYYMMDD') || '-002', 2, 'Bebidas', CURRENT_DATE, CURRENT_DATE + INTERVAL '1 days', '10:00', 'asignado', 5, 120.0, 800.00, 'Envío de prueba 2', NOW(), NOW()),
('ENV-' || TO_CHAR(NOW(), 'YYYYMMDD') || '-003', 3, 'Mixto', CURRENT_DATE, CURRENT_DATE + INTERVAL '3 days', '16:00', 'pendiente', 15, 350.0, 2200.00, 'Envío de prueba 3', NOW(), NOW());

-- 2. Asignar envíos a transportistas (usando los IDs de envíos recién creados)
-- Asignar envío 1 al transportista ID 1
INSERT INTO envio_asignaciones (envio_id, transportista_id, vehiculo_id, fecha_asignacion, observaciones, created_at, updated_at)
SELECT 
    e.id,
    1, -- ID del transportista
    1, -- ID del vehículo (asegúrate de que exista)
    NOW(),
    'Asignación automática de prueba',
    NOW(),
    NOW()
FROM envios e 
WHERE e.codigo LIKE 'ENV-%001'
LIMIT 1;

-- Asignar envío 2 al transportista ID 2
INSERT INTO envio_asignaciones (envio_id, transportista_id, vehiculo_id, fecha_asignacion, observaciones, created_at, updated_at)
SELECT 
    e.id,
    2, -- ID del transportista
    2, -- ID del vehículo
    NOW(),
    'Asignación automática de prueba',
    NOW(),
    NOW()
FROM envios e 
WHERE e.codigo LIKE 'ENV-%002'
LIMIT 1;

-- Ver los envíos creados
SELECT e.id, e.codigo, e.estado, e.almacen_destino_id, 
       ea.transportista_id, u.name as transportista_nombre
FROM envios e
LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
LEFT JOIN users u ON ea.transportista_id = u.id
WHERE e.codigo LIKE 'ENV-%'
ORDER BY e.created_at DESC;

