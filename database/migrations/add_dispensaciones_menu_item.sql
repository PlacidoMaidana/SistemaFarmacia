-- Agregar item de Dispensaciones al menú de Voyager
-- Script para Sistema de Farmacia

-- Insertar el item de menú para Dispensaciones
INSERT INTO `menu_items` (
    `id`,
    `menu_id`,
    `title`,
    `url`,
    `target`,
    `icon_class`,
    `color`,
    `parent_id`,
    `order`,
    `created_at`,
    `updated_at`,
    `route`
) VALUES (
    22,
    1,
    'Dispensaciones',
    '',
    '_self',
    'voyager-list',
    NULL,
    NULL,
    22,
    NOW(),
    NOW(),
    'dispensaciones.index'
);

-- Verificar que se insertó correctamente
SELECT id, title, route, icon_class FROM menu_items WHERE id = 22;
