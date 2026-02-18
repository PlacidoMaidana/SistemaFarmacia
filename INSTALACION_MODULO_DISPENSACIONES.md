# Instalación del Módulo de Dispensaciones

## Paso 1: Verificar Requisitos

✅ Laravel 8.x o superior
✅ PHP 7.4 o superior
✅ MySQL/MariaDB
✅ Voyager instalado y configurado

## Paso 2: Actualizar Base de Datos

### Opción A: Ejecutar script SQL manualmente

```bash
# Conectarse a MySQL
cd c:\xampp
.\mysql\bin\mysql.exe -u root

# Seleccionar la base de datos
USE farmaciau7;

# Ejecutar el script
SOURCE path/to/update_dispensaciones_table.sql;
```

### Opción B: Ejecutar desde consola

```bash
cd c:\xampp\htdocs\SistemaDeFarmacia\SistemaDeFarmacia
mysql -u root farmaciau7 < database/migrations/update_dispensaciones_table.sql
```

## Paso 3: Limpiar Cachés de Laravel

```bash
cd c:\xampp\htdocs\SistemaDeFarmacia\SistemaDeFarmacia

# Limpiar todas las cachés
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Opcional: reconstruir cachés para producción
php artisan config:cache
php artisan route:cache
```

## Paso 4: Verificar Permisos

Asegúrate de que el usuario tenga permisos para:
- Ver recetas
- Crear/editar dispensaciones
- Acceder al módulo de dispensaciones

En Voyager Admin:
1. Ir a **Admin → Roles**
2. Seleccionar el rol del usuario
3. Verificar permisos para:
   - `browse_recetas`
   - `browse_dispensaciones` (crear en Voyager si no existe)
   - `add_dispensaciones`
   - `edit_dispensaciones`
   - `delete_dispensaciones`

## Paso 5: Agregar al Menú de Voyager

1. Ir a **Admin → Herramientas → Menu Builder**
2. Seleccionar el menú **admin**
3. Agregar nuevo item:
   - **Título**: Dispensaciones
   - **URL**: `/dispensaciones`
   - **Ruta**: `dispensaciones.index`
   - **Ícono**: `voyager-list` o `voyager-receipt`
   - **Objetivo**: Same Tab
4. Guardar cambios

## Paso 6: Probar el Módulo

### Prueba 1: Acceso General
```
URL: http://127.0.0.1:8000/dispensaciones
```
Debería mostrar el listado general de dispensaciones con filtros.

### Prueba 2: Dispensaciones de una Receta
```
URL: http://127.0.0.1:8000/dispensaciones/origen?tipo_origen=receta&id_origen=1
```
Reemplazar `1` con un ID de receta existente.

### Prueba 3: Crear Dispensación
1. Ir a una receta en Voyager
2. Hacer clic en "Ver Dispensaciones"
3. Hacer clic en "Nueva Dispensación"
4. Completar el formulario
5. Guardar

### Prueba 4: Validación de Integridad
1. Intentar eliminar la única dispensación de una receta
2. El sistema debería mostrar un error: "No se puede eliminar la única dispensación"

## Estructura de Archivos Instalados

```
app/
├── Http/
│   └── Controllers/
│       └── DispensacionController.php       ✅ Nuevo
├── Models/
│   ├── Dispensacion.php                     ✅ Actualizado
│   ├── Receta.php                           ✅ Actualizado
│   ├── TratamientoCronico.php               ✅ Actualizado
│   └── MaterialesEnfermeria.php             ✅ Actualizado
├── Observers/
│   └── OrigenDispensacionObserver.php       ✅ Nuevo
└── Providers/
    └── AppServiceProvider.php               ✅ Actualizado

resources/
└── views/
    ├── dispensaciones/
    │   ├── index.blade.php                  ✅ Nuevo
    │   ├── por-origen.blade.php             ✅ Nuevo
    │   ├── create.blade.php                 ✅ Nuevo
    │   ├── edit.blade.php                   ✅ Nuevo
    │   └── show.blade.php                   ✅ Nuevo
    └── vendor/
        └── voyager/
            └── recetas/
                └── browse.blade.php         ✅ Actualizado

routes/
└── web.php                                  ✅ Actualizado

database/
└── migrations/
    └── update_dispensaciones_table.sql      ✅ Nuevo
```

## Solución de Problemas

### Error: "Class 'App\Observers\OrigenDispensacionObserver' not found"
```bash
composer dump-autoload
php artisan config:clear
```

### Error: "Column 'id_material' not found"
Ejecutar el script SQL de actualización de base de datos (Paso 2).

### Error: "Route [dispensaciones.index] not defined"
```bash
php artisan route:clear
php artisan route:cache
```

### Las vistas no se ven correctamente
```bash
php artisan view:clear
```

### Error 500 al acceder a dispensaciones
1. Revisar logs: `storage/logs/laravel.log`
2. Verificar permisos de archivos
3. Limpiar todas las cachés

## Verificación de Instalación Exitosa

Ejecutar estos comandos para verificar:

```bash
# Ver rutas del módulo
php artisan route:list | findstr dispensacion

# Debería mostrar:
# GET    dispensaciones
# GET    dispensaciones/create
# POST   dispensaciones
# GET    dispensaciones/origen
# GET    dispensaciones/{id}
# GET    dispensaciones/{id}/edit
# PUT    dispensaciones/{id}
# DELETE dispensaciones/{id}
```

## Backup Antes de Instalar

**IMPORTANTE**: Hacer backup antes de aplicar cambios:

```bash
# Backup de base de datos
mysqldump -u root farmaciau7 > backup_farmacia_antes_modulo_dispensaciones.sql

# Backup de archivos (opcional)
# Copiar toda la carpeta del proyecto a un lugar seguro
```

## Rollback (si algo sale mal)

```bash
# Restaurar base de datos
mysql -u root farmaciau7 < backup_farmacia_antes_modulo_dispensaciones.sql

# Revertir cambios en código
git checkout -- .
# o restaurar desde el backup de archivos
```

## Próximos Pasos

Después de la instalación:

1. ✅ Agregar item al menú de Voyager
2. ✅ Configurar permisos de usuario
3. ✅ Probar todas las funcionalidades
4. 📋 Capacitar a usuarios finales
5. 📊 Configurar reportes (siguiente fase)

## Soporte

Para más información, consultar:
- [MODULO_DISPENSACIONES.md](MODULO_DISPENSACIONES.md) - Guía de uso completa
- Logs del sistema: `storage/logs/laravel.log`
- Documentación de Voyager: https://voyager.devdojo.com/

---

**Fecha de creación**: {{ date('Y-m-d') }}
**Versión del módulo**: 1.0.0
