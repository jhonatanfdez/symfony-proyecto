<p align="center">
  <img src="public/logo-aquapanel.svg" alt="AquaPanel" width="96" height="96" />
</p>

<h1 align="center">AquaPanel — Sistema de Gestión</h1>

<p align="center">
  <a href="https://github.com/jhonatanfdez/symfony-proyecto/releases"><img alt="latest-tag" src="https://img.shields.io/github/v/tag/jhonatanfdez/symfony-proyecto?label=version&color=2563eb"></a>
</p>

Proyecto en Symfony para llevar el control de los productos de una empresa: catálogo, categorías, usuarios, inventario, compras/ventas y reportes. Actualmente en desarrollo activo.

Estado actual: v1.12.0 — Sistema de Inventario (Fase 2 - COMPLETA): Controlador `StockMovementController` totalmente integrado con servicio `StockMovementService`, formulario dinámico con opción `is_edit` para campos de solo lectura, interfaz visual mejorada con card pattern, menú lateral actualizado con iconos FontAwesome, validaciones exhaustivas, auditoría automática (fecha y usuario), y documentación completa. Sistema completamente funcional para gestionar movimientos de stock (entradas, salidas, ajustes).

• Changelog: ver [v1.12.0 en CHANGELOG.md](CHANGELOG.md#v1120---2025-11-09) · Tag: [v1.12.0](https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.12.0)

## Instalación y ejecución

### Requisitos previos

- PHP 8.1 o superior
- Composer
- Git
- MySQL/MariaDB o PostgreSQL
- Extensiones PHP: pdo_mysql o pdo_pgsql
- Opcional pero recomendado: Symfony CLI

### Pasos de instalación

1. Clonar el repositorio:
```bash
# HTTPS
git clone https://github.com/jhonatanfdez/symfony-proyecto.git
# o SSH
git clone git@github.com:jhonatanfdez/symfony-proyecto.git

cd symfony-proyecto
```

2. Instalar dependencias:
```bash
composer install
```

3. Configurar variables de entorno:
```bash
# Copiar el archivo de ejemplo
cp .env .env.local

# Editar .env.local y configurar la conexión a BD
# Ejemplo para MySQL:
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/nombre_base_datos?serverVersion=8.0"
```

4. Crear la base de datos:
```bash
# Con Symfony CLI
symfony console doctrine:database:create
# o con PHP
php bin/console doctrine:database:create
```

5. Ejecutar migraciones:
```bash
symfony console doctrine:migrations:migrate
# o
php bin/console doctrine:migrations:migrate
```

6. Crear usuario administrador:
```bash
# El comando te pedirá email y contraseña
symfony console app:create-admin
# o
php bin/console app:create-admin
```

7. Iniciar el servidor:
```bash
# Con Symfony CLI (recomendado)
symfony serve -d
# o con PHP
php -S localhost:8000 -t public/
```

8. Acceder a la aplicación:
- URL: `https://localhost:8000`
- Credenciales: las que configuraste en el paso 6

Notas:
- El primer usuario con rol administrador (`ROLE_ADMIN`) puede asignarse mediante actualización directa en BD o añadiendo lógica temporal/console para promoción de roles.
- Los formularios de eliminación ya incluyen CSRF y confirmación con SweetAlert2.

## Novedades recientes

- v1.12.0: **Sistema de Inventario (Fase 2 - COMPLETA)** 🚀 - Controlador `StockMovementController` integrado con servicio `StockMovementService`, inyección de dependencias automática, manejo de excepciones con mensajes flash contextualizados, formulario dinámico `StockMovementType` con opción `is_edit` (campos de solo lectura: fecha, usuario creador), interfaz visual mejorada con card pattern (`partials/_card.html.twig`), badges de colores para tipos de movimiento, menú lateral con iconos FontAwesome (fa-home, fa-user, fa-users, fa-tags, fa-flask, fa-box, fa-cubes), enlace "Movimientos de Stock", plantillas unificadas en `home/base.html.twig`, validaciones exhaustivas server-side, auditoría automática de fecha/usuario, documentación completa con docblocks, y 3 commits organizados.
- v1.11.0: **Sistema de Inventario (Fase 1)** 🎯 - Nueva entidad StockMovement con campos completos (cantidad, fecha, notas, referencias), validaciones exhaustivas (@Assert), relaciones con Product/User, enum TipoMovimiento (ENTRADA/SALIDA/AJUSTE) con validación en BD, timestamps automáticos, soporte para documentos relacionados (OC, facturas) y base para futuras características (almacenes, valorización).
- v1.10.0: **Sistema completo de imágenes de productos** 🎉 - Carousel Bootstrap 5 en show (layout 50/50), subida de imágenes al crear producto con redirección automática al show, botón "Eliminar todas", validación con SweetAlert2, mensajes de error específicos, y corrección de eliminación en cascada (ON DELETE CASCADE).
- v1.9.1: Controlador ProductImageController (subida y eliminación individual, base) y parámetro `uploads_products_dir` en configuración.
- v1.9.0: Infraestructura de imágenes múltiples - entidad ProductImage con relación OneToMany, migración de BD, directorio de uploads, formulario de carga con validaciones exhaustivas.
- v1.8.0: Autenticación renovada (UI moderna), branding AquaPanel, campos adicionales en registro.
- v1.7.0: Buscadores unificados, productos con descripción en listado, usuarios con estado Activo/Inactivo.

## Objetivo del proyecto

Construir un sistema interno que permita a una empresa gestionar su catálogo de productos y operaciones relacionadas:

- Administración de usuarios y roles
- Gestión de categorías y productos
- Control de inventario (stock, entradas, salidas y ajustes)
- Compras y proveedores
- Ventas y clientes (opcional)
- Reportes (inventario, rotación, ventas, compras)

## Funcionalidades actuales (v1.12.0)

- **Sistema de Inventario (Fase 2 - COMPLETA)** 🚀 NUEVO
  
  - **Controlador `StockMovementController`** completamente integrado con servicio
    - Rutas bajo `/admin/stock/movement`: 
      - `GET /` → index (listado con tabla de movimientos)
      - `GET /new`, `POST /new` → new (crear movimiento con servicio)
      - `GET /{id}` → show (detalles del movimiento)
      - `GET /{id}/edit`, `POST /{id}/edit` → edit (editar con campos deshabilitados)
      - `POST /{id}` → delete (eliminar con reverso de stock)
    - **Inyección de `StockMovementService`** en constructor para centralizar lógica
    - **Manejo automático de auditoría**:
      - Fecha: capturada al crear (hoy), no editable en edición
      - Usuario: autenticado capturado automáticamente (createBy), no editable
    - **Mensajes flash contextualizados en español**:
      - Éxito: "Movimiento creado/actualizado/eliminado correctamente"
      - Error: excepciones con mensajes descriptivos (stock insuficiente, cantidad inválida, etc.)
      - Validación: errores de formulario
    - **Manejo robusto de excepciones**:
      - `InvalidArgumentException`: violación de reglas de negocio (stock insuficiente)
      - `Exception`: errores generales con fallback graceful
      - Docblocks completos explicando flujo, parámetros y excepciones
  
  - **Servicio `StockMovementService`** con lógica de negocio centralizada
    - Inyección de dependencias: `EntityManager`, `StockMovementRepository`, `Validator`
    - **Método `createMovement(Product, int, TipoMovimiento, User, ...)`**:
      - Valida cantidad (no cero, no negativa)
      - Verifica stock disponible (para SALIDA/AJUSTE)
      - Actualiza stock del producto automáticamente
      - Crea movimiento con auditoría (fecha actual, usuario autenticado)
      - Persiste en BD
      - Lanza excepciones si falla validación
    - **Método `deleteMovement(StockMovement)`**:
      - Revierte cambios de stock (suma/resta inversa según tipo)
      - Aplica reglas restricción: solo elimina movimientos de hoy, solo el último por producto
      - Verifica que no cause stock negativo
      - Elimina movimiento y persiste
    - **Método `getMovementHistory(Product)`**:
      - Retorna historial ordenado por fecha descendente
      - Usado en `show.html.twig` para mostrar contexto
    - **Método `calcularStockAnterior(StockMovement)`**:
      - Recalcula estado de stock previo al movimiento
      - Usado para auditoría y cálculos
    - **Método helper `canDeleteMovement(StockMovement): bool`**:
      - Verifica si puede eliminarse (reglas de negocio)
    - **Documentación exhaustiva**:
      - Docblocks completos con @param, @return, @throws
      - Comentarios en línea explicando lógica compleja
      - Notas sobre mejoras futuras (restricciones por almacén, etc.)
  
  - **Formulario dinámico `StockMovementType`** con opción condicional
    - **Opción `is_edit`** (boolean): controla visibilidad de campos
    - **Modo creación** (`is_edit=false`, por defecto):
      - Campos editables: cantidad, tipo, descripción, producto
      - Campos no mostrados: fecha, createBy (se capturan en servicio)
    - **Modo edición** (`is_edit=true`):
      - Campos editables: cantidad, tipo, descripción, producto
      - Campos deshabilitados (readonly) para auditoría:
        - `fecha` (TextType): muestra fecha de creación formateada
        - `createBy` (TextType): muestra nombre del usuario creador o email
      - Estos campos son solo informativos (disabled + readonly en HTML)
    - **Campos detallados**:
      - `cantidad` (IntegerType): min 0, required, help text "Cantidad del movimiento"
      - `tipo` (ChoiceType): enum TipoMovimiento (ENTRADA, SALIDA, AJUSTE), required
      - `descripcion` (TextareaType): optional, help text "Detalles adicionales"
      - `product` (EntityType): query builder filtra productos activos, required
    - **Validaciones exhaustivas**:
      - Cantidad obligatoria, no cero, no negativa
      - Tipo obligatorio, debe estar en enum
      - Producto obligatorio, debe estar activo
      - Mensajes en español para cada validación
    - **Docblocks detallados** explicando estrategia `is_edit` vs opciones alternativas
  
  - **Interfaz de usuario visual y funcional**
    - **Plantillas heredan `home/base.html.twig`**:
      - `index.html.twig`: tabla con producto, cantidad, tipo (badge color), fecha, usuario, acciones (Ver, Editar, Eliminar)
      - `new.html.twig`: formulario para crear movimiento con título "Nuevo Movimiento"
      - `edit.html.twig`: formulario para editar con campos de solo lectura, botón eliminar incluido
      - `show.html.twig`: vista detallada en 2 columnas con badges, fechas formateadas, historial de movimientos
      - `_form.html.twig`: formulario reutilizable con botones Cancelar/Guardar
      - `_delete_form.html.twig`: confirmación con SweetAlert2 en español
    - **Uso de `partials/_card.html.twig`** para consistencia visual con módulo Productos
      - Bloque `content` en lugar de `body`
      - Padding uniforme, bordes redondeados, sombras suaves
    - **Badges de colores según tipo de movimiento**:
      - Verde: ENTRADA ✓ (aumento de stock)
      - Roja: SALIDA ✗ (disminución de stock)
      - Amarilla con texto oscuro: AJUSTE ⚠️ (corrección)
    - **Menú lateral actualizado con iconos FontAwesome 6**:
      - Nuevo enlace: "Movimientos de Stock" con icono fa-cubes (1.12.0)
      - Todos los enlaces del menú con iconos: fa-home, fa-user, fa-users, fa-tags, fa-flask, fa-box, fa-cubes
      - Mejora en UX: identificación rápida de secciones
    - **Fechas formateadas** con locale España (formato: dd/mm/yyyy hh:mm)
    - **Botones accionables** con iconos: Editar (fa-edit), Ver (fa-eye), Eliminar (fa-trash), Cancelar, Guardar
  
  - **Validaciones exhaustivas server-side**:
    - Cantidad: no cero, no negativa, validación en entidad y servicio
    - Tipo: debe estar en enum TipoMovimiento, validación en BD (CHECK constraint)
    - Descripción: obligatoria para tipo AJUSTE (validación en servicio)
    - Producto: debe estar activo, debe existir, validación en formulario
    - Stock: validación de suficiencia antes de SALIDA (regla de negocio)
    - Usuario creador: capturado automáticamente, no editado por usuario
  
  - **Auditoría automática completa**:
    - `fecha`: timestamp de creación (no editable post-creación)
    - `createBy`: usuario autenticado que creó el movimiento (no editable)
    - `updatedAt`: actualizado en cada edición (automático Doctrine)
    - Permite rastrear completo: quién creó, cuándo, qué cambió en ediciones posteriores
    - Campos visibles en modo edición pero deshabilitados (solo lectura)

- **Sistema de Inventario (Fase 1)** 🎯
  - Nueva entidad `StockMovement` para registro de movimientos
    - Campos: cantidad, fecha, tipo de movimiento, notas, referencias
    - Relaciones: producto (`ManyToOne`), usuario (`ManyToOne`)
    - Validaciones exhaustivas con @Assert
      - Cantidad no puede ser cero
      - Notas obligatorias para AJUSTE
      - Referencias opcionales (número de OC, factura, etc.)
    - Timestamps automáticos (createdAt, updatedAt)
  - Enum `TipoMovimiento` para control de operaciones
    - Tipos soportados: ENTRADA, SALIDA, AJUSTE
    - Validación a nivel de base de datos (CHECK constraint)
    - Mensajes de error personalizados en español
  - Infraestructura para extensión futura
    - Preparado para agregar almacenes
    - Soporte para documentos relacionados
    - Base para reportes de valorización

- Autenticación (login/logout) con UI moderna y branding AquaPanel
  - Redirección automática desde `/home` al login si no está autenticado (con flash "Acceso denegado")
  - Registro con campos: email, contraseña, nombre, fecha de nacimiento
- Dashboard con layout responsive (Bootstrap 5)
- Sistema de mensajes flash (éxito, error, advertencia, info)
- Protección de rutas administrativas bajo prefijo `/admin`
  - Redirección y mensaje si el usuario no es admin
- Módulo Usuarios (CRUD) con roles básicos
  - Perfil de usuario (`/profile/miperfil` y `/profile/editar`)
  - Flash messages funcionando correctamente tras edición de perfil
  - Campo `activo` para estado Activo/Inactivo con filtros
- Módulo Categorías (CRUD)
- **Módulo Productos (CRUD completo)** ⭐
  - SKU único, nombre, descripción, precio, costo, stock, estado activo/inactivo
  - Relación con categorías (obligatoria)
  - Auditoría: registro automático del usuario creador
  - **Sistema completo de imágenes múltiples** 📸 ⭐ NUEVO
    - **Carousel Bootstrap 5 en vista show**: layout 50/50 (carousel izquierda, info derecha)
      - Indicadores (puntitos), controles prev/next, auto-rotación
      - Imágenes con object-fit: contain (400px altura, sin distorsión)
      - Contador de imágenes, caption con nombre de archivo
      - Si no hay imágenes: alerta informativa y layout adaptativo (100% ancho)
    - **Subida de imágenes al crear producto**: 
      - Formulario integrado en `new.html.twig` (hasta 10 imágenes)
      - Redirección automática al show del producto recién creado
      - Mensaje flash dinámico según cantidad de imágenes subidas
    - **Gestión completa en edición**:
      - Formulario de subida con validación JavaScript (SweetAlert2)
      - No permite enviar sin seleccionar archivos
      - Botón "Eliminar todas las imágenes" con confirmación
      - Endpoint `/admin/product/{id}/images/delete-all` (BD + archivos físicos)
      - Mensajes de error específicos: límite cantidad, tipo no permitido, tamaño excedido, CSRF inválido
    - **Eliminación en cascada corregida**:
      - Cascade: ['persist', 'remove'] y orphanRemoval en relación OneToMany
      - Migración con ON DELETE CASCADE en FK de product_image
      - Eliminación física de archivos sincronizada con BD
      - Permite eliminar productos con imágenes sin errores de integridad referencial
    - Infraestructura técnica:
      - Entidad ProductImage con relación OneToMany a Product
      - Tabla product_image: imageName, imagePath, position, createdAt
      - Directorio: `public/uploads/products/`
      - Validaciones: máx 10 imágenes, 5MB cada una, JPEG/PNG/WEBP
      - ProductImageController con endpoints de subida y eliminación
  - **Validaciones robustas con manejo de errores mejorado** ⭐
    - Doble capa: HTML5 + servidor con @Assert
    - Sistema de errores exhaustivo: muestra todos los errores de validación campo por campo
    - Prevención de errores 500 con `empty_data` ('' para strings, 0 para integers)
    - Mensajes específicos por campo con etiquetas descriptivas
  - **Badges de stock con mejor legibilidad** ⭐
    - Verde (>10 unidades), Amarillo con texto oscuro (1-10), Rojo (Sin Stock)
    - Consistencia visual entre listado y vista detallada
  - Soft delete lógico (campo activo para ocultar sin eliminar)
  - Lifecycle callbacks: actualización automática de `updatedAt`
  - Protección con AdminAccessGuard en todos los endpoints
  - Mensajes flash en español para crear/editar/eliminar
- Confirmaciones de eliminación con SweetAlert2 (modales elegantes)
  - Reutilizable: basta con usar la clase `js-delete-form` en formularios de eliminación
  - Personalizable: `data-swal-title`, `data-swal-text`, `data-swal-confirm`, `data-swal-cancel`
  - Prevención de doble envío
- **Compatibilidad Turbo Drive** ⭐
  - `data-turbo="false"` en formularios para correcta visualización de mensajes flash
  - Sincronización de estado garantizada tras operaciones CRUD

## Próximos módulos (Roadmap)

- ~~Productos (CRUD) con SKU, precio, costo, estado~~ ✅ Completado en v1.4.0
- ~~Imágenes de productos - sistema completo~~ ✅ Completado en v1.10.0
  - ~~Carousel Bootstrap 5 en show~~ ✅
  - ~~Subida de imágenes al crear/editar~~ ✅
  - ~~Eliminación individual y en lote~~ ✅
  - ~~Corrección de eliminación en cascada~~ ✅
- Inventario: existencias, almacenes, movimientos (entradas/salidas/ajustes)
- Proveedores y compras (OC, recepción, costos)
- Ventas y clientes (opcional): pedidos, facturación ligera
- Reportes: stock bajo, valorización, ABC, rotación, compras/ventas por período
- Permisos avanzados por rol (p. ej., operador de almacén vs. administrador)
- Búsqueda y filtros avanzados (por categoría, SKU, proveedor, etc.)

## Tecnologías

- PHP 8.1+
- Symfony 6.x (HTTPKernel, Routing, Security, Doctrine ORM, Twig)
- Doctrine ORM (MySQL/PostgreSQL)
- Twig (plantillas)
- Bootstrap 5 (CDN)
- SweetAlert2 (CDN) para confirmaciones
- Turbo/Hotwire (controlado para evitar problemas de caché de `<head>`)

## Estructura de navegación

- Menú lateral con secciones:
  - Usuarios: Inicio, Mi perfil
  - Administrativa (solo admin): Usuarios, Categorías, Productos
- Prefijo `/admin` para rutas con acceso restringido a administradores



## Uso de SweetAlert2 en formularios de eliminación

En cualquier formulario de eliminación agrega:

```html
<form method="post"
      action="/ruta/eliminar/ID"
      class="js-delete-form"
      data-swal-title="¿Eliminar registro?"
      data-swal-text="Esta acción no se puede deshacer.">
  <!-- token CSRF -->
  <button class="btn btn-danger">Eliminar</button>
</form>
```

El script global del layout intercepta el submit, muestra el modal y envía el formulario solo si el usuario confirma.

## Validaciones de formularios (Seguridad)

El sistema implementa **doble capa de validación** para máxima seguridad:

### 1. Validaciones HTML5 (navegador)

- Mejora la experiencia de usuario con feedback inmediato
- Atributos: `required`, `min`, `max`, `step`, `pattern`
- **IMPORTANTE**: Puede ser bypasseada (deshabilitar JS, editar DOM, envío directo por API)

### 2. Validaciones Server-Side (PHP)

- Constraints `@Assert` en entidades (Product, User, Categoria)
- Validación **definitiva e infranqueable**
- Protege contra envíos directos por cURL, Postman, o modificación del HTML
- Tipos de validaciones usadas:
  - `@Assert\NotBlank`: campos obligatorios
  - `@Assert\Length`: longitud mínima/máxima
  - `@Assert\GreaterThanOrEqual`: valores numéricos no negativos
  - `@Assert\Regex`: formato específico (decimales, patrones)
  - `@Assert\Type`: tipo de dato correcto
  - `@Assert\NotNull`: relaciones obligatorias

**Principio de seguridad**: NUNCA confiar solo en validación del cliente. Siempre validar en el servidor.

## Seguridad y permisos

- Acceso a `/admin/*` restringido a usuarios con `ROLE_ADMIN`
- Redirección automática con mensaje de error si no tiene permisos
- CSRF habilitado en formularios sensibles (p. ej., eliminar)

## Desarrollo

- Estilo de commits: convencional (feat, fix, refactor, docs, chore)
- Versionado: SemVer con tags anotados `vX.Y.Z`
- Changelog: `CHANGELOG.md` siguiendo Keep a Changelog

## Licencia

Este proyecto está bajo la licencia incluida en `LICENSE`.

## Créditos

- Desarrollado por Jhonatan Fernandez y colaboradores.
