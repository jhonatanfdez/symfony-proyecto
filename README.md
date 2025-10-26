<p align="center">
  <img src="public/logo-aquapanel.svg" alt="AquaPanel" width="96" height="96" />
</p>

<h1 align="center">AquaPanel — Sistema de Gestión</h1>

<p align="center">
  <a href="https://github.com/jhonatanfdez/symfony-proyecto/releases"><img alt="latest-tag" src="https://img.shields.io/github/v/tag/jhonatanfdez/symfony-proyecto?label=version&color=2563eb"></a>
</p>

Proyecto en Symfony para llevar el control de los productos de una empresa: catálogo, categorías, usuarios, inventario, compras/ventas y reportes. Actualmente en desarrollo activo.

Estado actual: v1.10.0 — Sistema completo de gestión de imágenes de productos: carousel en show, subida de imágenes al crear/editar, eliminación individual y en lote, corrección de eliminación en cascada.

• Changelog: ver [v1.10.0 en CHANGELOG.md](CHANGELOG.md#v1100---2025-10-26) · Tag: [v1.10.0](https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.10.0)

## Novedades recientes

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

## Funcionalidades actuales (v1.10.0)

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

## Instalación y ejecución

Requisitos previos:

- PHP 8.1 o superior, Composer, extensión PDO para tu DB
- Base de datos (MySQL/MariaDB o PostgreSQL)
- Opcional: Symfony CLI para un server local sencillo

Pasos básicos:

1) Clonar el repositorio y instalar dependencias con Composer
2) Configurar la conexión a BD en `DATABASE_URL` (en `.env.local`)
3) Crear base de datos y ejecutar migraciones de Doctrine
4) Levantar el servidor (Symfony CLI o PHP servidor embebido)

Notas:

- El primer usuario con rol administrador (`ROLE_ADMIN`) puede asignarse mediante actualización directa en BD o añadiendo lógica temporal/console para promoción de roles.
- Los formularios de eliminación ya incluyen CSRF y confirmación con SweetAlert2.

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
