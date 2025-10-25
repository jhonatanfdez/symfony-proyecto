# Sistema de Gestión de Productos (Symfony)

![latest-tag](https://img.shields.io/github/v/tag/jhonatanfdez/symfony-proyecto?label=version&color=0d6efd)

Proyecto en Symfony para llevar el control de los productos de una empresa: catálogo, categorías, usuarios, inventario, compras/ventas y reportes. Actualmente en desarrollo activo.

Estado actual: v1.6.0 — UI AdminLTE-like (fondo gris y cards flotantes), herramientas de card robustas (colapsar/cerrar con fallback Unicode), cambio a Font Awesome 6.4.0 y página de diagnóstico `/test-icons`.

• Changelog: ver [v1.6.0 en CHANGELOG.md](CHANGELOG.md#v160---2025-10-25) · Tag: [v1.6.0](https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.6.0)

### Novedades v1.6.0 (resumen)
- Fondo general y del contenido en gris suave `#f4f6f9` al estilo AdminLTE; cards blancas con sombra doble para mejor contraste.
- Botones de card (minimizar/cerrar) siempre visibles, con mejor área de clic y animación hover.
- Fallback confiable con símbolos Unicode (− / ×) si Font Awesome no carga.
- Actualización a CDN de Font Awesome 6.4.0 para mayor estabilidad.
- Página de diagnóstico `/test-icons` (solo dev/QA) para verificar íconos y comportamiento de las cards.

## Objetivo del proyecto

Construir un sistema interno que permita a una empresa gestionar su catálogo de productos y operaciones relacionadas:

- Administración de usuarios y roles
- Gestión de categorías y productos
- Control de inventario (stock, entradas, salidas y ajustes)
- Compras y proveedores
- Ventas y clientes (opcional)
- Reportes (inventario, rotación, ventas, compras)

## Funcionalidades actuales (v1.6.0)

- Autenticación (login/logout)
- Dashboard con layout responsive (Bootstrap 5)
- Sistema de mensajes flash (éxito, error, advertencia, info)
- Protección de rutas administrativas bajo prefijo `/admin`
  - Redirección y mensaje si el usuario no es admin
- Módulo Usuarios (CRUD) con roles básicos
  - Perfil de usuario (`/profile/miperfil` y `/profile/editar`)
  - Flash messages funcionando correctamente tras edición de perfil
- Módulo Categorías (CRUD)
- **Módulo Productos (CRUD completo)** ⭐
  - SKU único, nombre, descripción, precio, costo, stock, estado activo/inactivo
  - Relación con categorías (obligatoria)
  - Auditoría: registro automático del usuario creador
  - **Validaciones robustas con manejo de errores mejorado** ⭐ NUEVO
    - Doble capa: HTML5 + servidor con @Assert
    - Sistema de errores exhaustivo: muestra todos los errores de validación campo por campo
    - Prevención de errores 500 con `empty_data` ('' para strings, 0 para integers)
    - Mensajes específicos por campo con etiquetas descriptivas
  - **Badges de stock con mejor legibilidad** ⭐ NUEVO
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
- **Compatibilidad Turbo Drive** ⭐ NUEVO
  - `data-turbo="false"` en formularios para correcta visualización de mensajes flash
  - Sincronización de estado garantizada tras operaciones CRUD

## Próximos módulos (Roadmap)

- ~~Productos (CRUD) con SKU, precio, costo, estado~~ ✅ Completado en v1.4.0
- Imágenes de productos (upload, gestión, galería)
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
  - Administrativa (solo admin): Usuarios, Categorías, Productos, Prueba
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

- En cualquier formulario de eliminación agrega:

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

