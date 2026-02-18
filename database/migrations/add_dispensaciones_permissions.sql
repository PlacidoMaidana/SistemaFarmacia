-- Configurar permisos para el módulo de Dispensaciones
-- Script para Sistema de Farmacia

-- Verificar si ya existen permisos para dispensaciones
SELECT * FROM permissions WHERE table_name = 'dispensaciones';

-- Si no existen, insertar permisos básicos
-- Nota: Ajustar los IDs según la base de datos
INSERT INTO permissions (table_name, `key`, created_at, updated_at) 
VALUES 
    ('dispensaciones', 'browse_dispensaciones', NOW(), NOW()),
    ('dispensaciones', 'read_dispensaciones', NOW(), NOW()),
    ('dispensaciones', 'edit_dispensaciones', NOW(), NOW()),
    ('dispensaciones', 'add_dispensaciones', NOW(), NOW()),
    ('dispensaciones', 'delete_dispensaciones', NOW(), NOW())
ON DUPLICATE KEY UPDATE table_name = table_name;

-- Asignar permisos al rol admin (ID 1)
-- Obtener los IDs de los permisos recién creados
SET @browse_id = (SELECT id FROM permissions WHERE `key` = 'browse_dispensaciones');
SET @read_id = (SELECT id FROM permissions WHERE `key` = 'read_dispensaciones');
SET @edit_id = (SELECT id FROM permissions WHERE `key` = 'edit_dispensaciones');
SET @add_id = (SELECT id FROM permissions WHERE `key` = 'add_dispensaciones');
SET @delete_id = (SELECT id FROM permissions WHERE `key` = 'delete_dispensaciones');

-- Asignar al rol admin si no están asignados
INSERT IGNORE INTO permission_role (permission_id, role_id) VALUES
    (@browse_id, 1),
    (@read_id, 1),
    (@edit_id, 1),
    (@add_id, 1),
    (@delete_id, 1);

-- Verificar permisos asignados
SELECT p.`key`, r.name 
FROM permissions p
JOIN permission_role pr ON p.id = pr.permission_id
JOIN roles r ON pr.role_id = r.id
WHERE p.table_name = 'dispensaciones';
