# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [v1.4.0] - 2025-10-24

### Added
- **Módulo completo de Productos (CRUD)** bajo `/admin/product`:
  - Entidad `Product` con 8 campos: id, sku (único), nombre, descripcion, precio, costo, stock, activo, createdAt, updatedAt
  - Relaciones: `categoria` (ManyToOne obligatoria con RESTRICT), `createBy` (ManyToOne a User para auditoría)
  - Constructor con valores por defecto: createdAt (actual), activo=true, stock=0
  - Lifecycle callback `PreUpdate` para actualizar automáticamente `updatedAt`
- **Validaciones @Assert completas en Product entity**:
  - `@Assert\NotBlank`: sku, nombre, precio, costo, stock
  - `@Assert\Length`: sku (max 50), nombre (max 180)
  - `@Assert\GreaterThanOrEqual(0)`: precio, costo, stock (no negativos)
  - `@Assert\Regex`: precio y costo (formato decimal con máximo 2 decimales)
  - `@Assert\Type(integer)`: stock
  - `@Assert\NotNull`: categoria (relación obligatoria)
- **Formulario ProductType** con 7 campos configurados:
  - TextType: sku, nombre
  - TextareaType: descripcion (opcional)
  - NumberType: precio, costo (scale=2, min=0, step=0.01)
  - IntegerType: stock (min=0)
  - CheckboxType: activo
  - EntityType: categoria (con placeholder)
- **ProductController** con protección completa:
  - Ruta base: `/admin/product`
  - AdminAccessGuard inyectado en constructor
  - Guards en todos los métodos: index, new, show, edit, delete
  - Auditoría: `setCreateBy($this->getUser())` automático en `new()`
  - Mensajes flash en español: 'Producto creado/actualizado/eliminado exitosamente.'
  - Tokens CSRF en eliminación
- **Templates del CRUD de productos**:
  - `index.html.twig`: listado con tabla Bootstrap
  - `new.html.twig`: formulario de creación
  - `edit.html.twig`: formulario de edición con botón eliminar
  - `show.html.twig`: vista detalle del producto
  - `_form.html.twig`: formulario parcial reutilizable
  - `_delete_form.html.twig`: formulario de eliminación con confirmación
- Enlace "Productos" en menú lateral administrativo con estado activo
- **Documentación exhaustiva en español**:
  - PHPDoc completo en Product entity explicando cada campo, validaciones y arquitectura
  - Comentarios en ProductType sobre doble capa de validación (HTML5 + servidor)
  - Explicación de seguridad: por qué no confiar solo en validaciones del cliente
  - Notas sobre soft delete lógico, auditoría y lifecycle callbacks

### Changed
- Migración de productos ejecutada: tabla `product` con constraints y foreign keys
- README.md actualizado con funcionalidades de v1.4.0
- Roadmap actualizado: módulo Productos marcado como completado

### Technical
- **Doble capa de validación** implementada:
  - HTML5: feedback inmediato en navegador (puede bypassearse)
  - Server-side: validación definitiva con @Assert (infranqueable)
- **Soft delete lógico**: campo `activo` permite ocultar productos sin eliminarlos
  - Conserva historial de ventas y referencias
  - Permite reactivación de productos
  - Mantiene integridad referencial
- **Auditoría completa**: campo `createBy` registra quién creó cada producto
- **Validación de unicidad**: SKU con `unique=true` en base de datos
- **Precisión decimal**: DECIMAL(12,2) mapeado como string para evitar problemas de precisión con floats
- **Protección de integridad referencial**: `onDelete='RESTRICT'` en relaciones (no permite borrar categoría/usuario con productos asociados)

## [v1.3.0] - 2025-10-23

### Added
- Integración de SweetAlert2 para confirmaciones de eliminación elegantes y modernas.
- CDN de SweetAlert2 cargado en `templates/home/base.html.twig`.
- Listener JavaScript global que intercepta formularios con clase `js-delete-form`.
- Diálogos de confirmación personalizables mediante atributos `data-swal-title`, `data-swal-text`, `data-swal-confirm` y `data-swal-cancel`.
- Mensajes flash de éxito (verde) al eliminar categorías y usuarios.
- Sistema de comentarios exhaustivo en español en todos los archivos relacionados:
  - Templates Twig explicando el flujo de activación de SweetAlert2.
  - Controllers PHP documentando el flujo completo desde el clic hasta la base de datos.
  - JavaScript explicando el listener, data attributes, y prevención de doble envío.

### Changed
- Eliminados `onsubmit="return confirm(...)"` nativos de JavaScript en formularios de eliminación.
- Formularios de eliminación actualizados con clase `js-delete-form` y atributos `data-swal-*`.
- Botones de eliminar con texto en español: "Eliminar".
- Sistema de flash messages documentado con comentarios explicando los 4 tipos (success, error, warning, info).

### Technical
- Protección contra doble envío usando flag `dataset.confirmed`.
- Validación CSRF mantenida en todos los endpoints de eliminación.
- Patrón reutilizable: cualquier formulario nuevo con `class="js-delete-form"` obtendrá confirmación automática.

## [v1.2.0] - 2025-10-23

### Added
- AdminAccessGuard: protección de rutas administrativas con prefijo `/admin` y redirección a Home si el usuario no es admin, incluyendo mensaje flash rojo: "Usted no tiene acceso a esta ruta".
- CRUD de Categoría bajo `/admin/categoria` (Entidad, Repositorio, Form, Controlador y migración).
- Enlace "Categorías" en el menú lateral (sección Administrativa) con estado activo dinámico.

### Changed
- Plantillas de Categoría actualizadas a `home/base.html.twig` (layout admin con sidebar y Bootstrap).
- Botones alineados en línea (Ver/Editar/Eliminar) en vistas `show` y `edit` de Usuarios y Categorías.
- Listados de Usuarios y Categorías: agregado botón Eliminar con confirmación y CSRF.

## [v1.1.0] - 2025-10-22

### Added
- ProfileController con rutas sin parámetros:
  - `/profile/miperfil` (mostrar)
  - `/profile/editar` (editar)
- Plantillas `profile/show.html.twig` y `profile/edit.html.twig`.

### Changed
- Formulario `UserType` con opción `disable_roles` para deshabilitar el campo Roles en el contexto de edición de perfil.
- Campo `password` no mapeado (`mapped => false`) para controlar manualmente el hash solo cuando se proporciona un nuevo valor.

## [v1.0.1] - 2025-10-22

### Fixed
- Problema de estilos tras iniciar sesión (Turbo/Hotwire conservaba el `<head>` anterior):
  - `data-turbo="false"` en el formulario de login.
  - `<meta name="turbo-visit-control" content="reload">` en el layout.
  - `data-turbo-track="reload"` en recursos CSS.

## [v1.0.0] - 2025-10-22

### Added
- Estructura inicial del proyecto Symfony.
- Autenticación base (login/logout) y CRUD de Usuarios.


[v1.4.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.4.0
[v1.3.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.3.0
[v1.2.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.2.0
[v1.1.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.1.0
[v1.0.1]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.0.1
[v1.0.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.0.0
