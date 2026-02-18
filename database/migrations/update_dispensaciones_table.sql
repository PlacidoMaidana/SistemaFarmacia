-- Script SQL para actualizar la tabla dispensaciones
-- Agregar soporte para materiales de enfermería

-- 1. Verificar y agregar campo id_material si no existe
ALTER TABLE `dispensaciones` 
ADD COLUMN IF NOT EXISTS `id_material` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `id_medicamento`,
ADD INDEX IF NOT EXISTS `idx_id_material` (`id_material`);

-- 2. Agregar foreign key constraint (opcional, descomentar si aplica)
-- ALTER TABLE `dispensaciones`
-- ADD CONSTRAINT `fk_dispensacion_material`
-- FOREIGN KEY (`id_material`) REFERENCES `materiales_enfermeria` (`id_material`)
-- ON DELETE SET NULL ON UPDATE CASCADE;

-- 3. Actualizar índices para mejorar consultas
ALTER TABLE `dispensaciones`
ADD INDEX IF NOT EXISTS `idx_tipo_origen` (`tipo_origen`),
ADD INDEX IF NOT EXISTS `idx_id_origen` (`id_origen`),
ADD INDEX IF NOT EXISTS `idx_tipo_id_origen` (`tipo_origen`, `id_origen`),
ADD INDEX IF NOT EXISTS `idx_fecha` (`fecha`);

-- 4. Verificar datos existentes
SELECT 
    tipo_origen,
    COUNT(*) as total,
    COUNT(DISTINCT id_origen) as origenes_unicos
FROM dispensaciones
GROUP BY tipo_origen;

-- 5. Verificar integridad de datos
-- Mostrar recetas sin dispensaciones (no debería haber ninguna si el sistema funciona bien)
SELECT r.id_receta, r.numero_receta, r.fecha_emision
FROM recetas r
LEFT JOIN dispensaciones d ON d.tipo_origen = 'receta' AND d.id_origen = r.id_receta
WHERE d.id_dispensacion IS NULL;

-- 6. Mostrar tratamientos sin dispensaciones
SELECT t.id_tratamiento, t.diagnostico, t.fecha_inicio
FROM tratamientos_cronicos t
LEFT JOIN dispensaciones d ON d.tipo_origen = 'tratamiento' AND d.id_origen = t.id_tratamiento
WHERE d.id_dispensacion IS NULL;
