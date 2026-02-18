# Módulo de Dispensaciones - Guía de Uso

## Descripción

El módulo de dispensaciones es un sistema centralizado para gestionar todas las entregas de medicamentos y materiales de enfermería, independientemente de su origen (recetas, tratamientos crónicos o suministros).

## Características Principales

### 1. Gestión Centralizada
- **Una sola tabla**: Todas las dispensaciones se almacenan en `dispensaciones`
- **Múltiples orígenes**: Soporta recetas, tratamientos crónicos y suministros
- **Identificación clara**: Usa `tipo_origen` e `id_origen` para referenciar el origen

### 2. Validaciones de Integridad
- **Obligatoriedad**: Cada registro de origen DEBE tener al menos una dispensación
- **Eliminación automática**: Si se elimina la última dispensación, se elimina el origen
- **XOR de items**: Cada dispensación debe tener medicamento O material (no ambos)

### 3. Trazabilidad Completa
- Registro de usuario que realizó la dispensación
- Fecha y hora de dispensación
- Información de lotes y vencimientos
- Observaciones

## Estructura de Archivos

### Modelos
- `app/Models/Dispensacion.php` - Modelo principal con relaciones polimórficas
- `app/Models/Receta.php` - Actualizado con relación a dispensaciones
- `app/Models/TratamientoCronico.php` - Actualizado con relación a dispensaciones
- `app/Models/MaterialesEnfermeria.php` - Actualizado con relación a dispensaciones

### Controladores
- `app/Http/Controllers/DispensacionController.php` - Controlador centralizado

### Observers
- `app/Observers/OrigenDispensacionObserver.php` - Valida integridad referencial

### Vistas
- `resources/views/dispensaciones/index.blade.php` - Listado general
- `resources/views/dispensaciones/por-origen.blade.php` - Dispensaciones por origen
- `resources/views/dispensaciones/create.blade.php` - Crear dispensación
- `resources/views/dispensaciones/edit.blade.php` - Editar dispensación
- `resources/views/dispensaciones/show.blade.php` - Ver detalles

## Rutas Disponibles

### Rutas Principales
```
GET  /dispensaciones              - Listado general con filtros
GET  /dispensaciones/origen       - Dispensaciones de un origen específico
GET  /dispensaciones/create       - Formulario de creación
POST /dispensaciones              - Guardar nueva dispensación
GET  /dispensaciones/{id}         - Ver detalles
GET  /dispensaciones/{id}/edit    - Formulario de edición
PUT  /dispensaciones/{id}         - Actualizar dispensación
DELETE /dispensaciones/{id}       - Eliminar dispensación
```

### Rutas de Compatibilidad (Recetas)
Las rutas antiguas de recetas redirigen automáticamente al módulo centralizado:
```
/recetas/{id}/dispensaciones       → /dispensaciones/origen?tipo_origen=receta&id_origen={id}
/recetas/{id}/dispensaciones/create → /dispensaciones/create?tipo_origen=receta&id_origen={id}
```

## Uso del Módulo

### 1. Acceder a Dispensaciones de un Origen

**Desde una Receta:**
```
URL: /dispensaciones/origen?tipo_origen=receta&id_origen=12
```

**Desde un Tratamiento:**
```
URL: /dispensaciones/origen?tipo_origen=tratamiento&id_origen=5
```

**Desde un Suministro:**
```
URL: /dispensaciones/origen?tipo_origen=suministro&id_origen=8
```

### 2. Crear una Dispensación

1. Ir a la página de origen (receta, tratamiento, etc.)
2. Hacer clic en "Nueva Dispensación"
3. Completar el formulario:
   - Seleccionar interno
   - Elegir tipo de item (medicamento o material)
   - Seleccionar el item específico
   - Ingresar cantidad y unidad
   - Especificar fecha
   - Opcionalmente: lote, vencimiento, observaciones
4. Guardar

### 3. Editar una Dispensación

1. Desde la lista de dispensaciones, hacer clic en el botón "Editar"
2. Modificar los campos necesarios
3. Guardar cambios

### 4. Eliminar una Dispensación

**IMPORTANTE**: No se puede eliminar la última dispensación de un origen. El sistema lo previene automáticamente.

Si se elimina una dispensación y quedan otras, la operación se completa normalmente.

## Validaciones Implementadas

### Al Crear/Editar
- ✅ Debe tener interno válido
- ✅ Debe tener medicamento O material (no ambos, no ninguno)
- ✅ Cantidad debe ser mayor a 0
- ✅ Fecha es obligatoria
- ✅ El origen debe existir

### Al Eliminar
- ✅ No se puede eliminar si es la única dispensación
- ✅ Si se elimina la última, se elimina el origen automáticamente

## Relaciones en Modelos

### Dispensacion
```php
$dispensacion->origen();        // Obtiene el origen (polimórfico)
$dispensacion->receta();        // Si es de tipo receta
$dispensacion->tratamiento();   // Si es de tipo tratamiento
$dispensacion->medicamento();   // Medicamento dispensado
$dispensacion->material();      // Material dispensado
$dispensacion->interno();       // Interno que recibió
$dispensacion->usuario();       // Usuario que registró
```

### Receta, TratamientoCronico
```php
$receta->dispensaciones();      // Todas las dispensaciones
```

### MaterialesEnfermeria
```php
$material->dispensaciones();    // Todas las dispensaciones
$material->suministros();       // Solo suministros de enfermería
```

## Scopes Disponibles

```php
// Filtrar por tipo de origen
Dispensacion::deReceta($idReceta)->get();
Dispensacion::deTratamiento($idTratamiento)->get();
Dispensacion::deSuministro($idSuministro)->get();
```

## Migración desde Sistema Anterior

Si ya tienes recetas con dispensaciones creadas con el sistema anterior:

1. Las dispensaciones existentes seguirán funcionando
2. Las vistas y rutas antiguas redirigen automáticamente al nuevo módulo
3. No es necesaria migración de datos

## Próximos Pasos

1. **Agregar al menú de Voyager**: Crear item de menú para acceso rápido
2. **Dashboard**: Agregar widget con estadísticas de dispensaciones
3. **Reportes**: Crear módulo de reportes de dispensaciones
4. **Notificaciones**: Alertas por vencimiento de lotes
5. **Control de Stock**: Integrar con inventario de medicamentos

## Soporte

Para problemas o sugerencias:
- Revisar logs en `storage/logs/laravel.log`
- Verificar validaciones en consola del navegador
- Comprobar permisos de usuario en Voyager

## Notas Técnicas

- **Observer**: `OrigenDispensacionObserver` se ejecuta al eliminar dispensaciones
- **Transacciones**: Las operaciones usan transacciones DB para garantizar consistencia
- **Eager Loading**: Las relaciones se cargan anticipadamente para optimizar consultas
- **Soft Deletes**: No implementado (eliminación directa)
