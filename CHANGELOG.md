# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [v1.12.0] - 2025-11-09

### Added
- **Sistema completo de Movimientos de Stock** 🚀
  - Integración del `StockMovementService` en `StockMovementController` con manejo automático de fecha y usuario
  - Inyección de dependencias del servicio para crear movimientos (entradas, salidas, ajustes)
  - Mensajes flash contextualizados (éxito/error) con manejo de excepciones
  - Opción `is_edit` en formulario para mostrar campos de solo lectura en modo edición
  
- **Mejoras en Formularios**:
  - Formulario `StockMovementType` mejorado con campos completamente configurados
  - Campos deshabilitados (fecha, usuario creador) visibles pero no editables en modo edición
  - Validaciones exhaustivas con mensajes en español
  - Documentación detallada de cada campo y comportamiento

- **Interfaz de Usuario (UI/UX)**:
  - Plantillas unificadas usando `partials/_card.html.twig` para consistencia visual
  - Templates mejorados: `index.html.twig`, `show.html.twig`, `new.html.twig`, `edit.html.twig`
  - Badges de colores para tipos de movimiento (ENTRADA verde, SALIDA roja, AJUSTE amarilla)
  - Iconos FontAwesome en todos los enlaces del menú lateral
  - Nuevo enlace "Movimientos de Stock" en el menú administrativo

- **Documentación Completa**:
  - Docblocks extensos en `StockMovementController` explicando flujo de cada método
  - Comentarios detallados en `StockMovementType` diferenciando creación vs edición
  - Anotaciones en `StockMovementService` sobre reglas de negocio
  - Notas sobre mejoras futuras (restricciones de edición, eliminación con reglas)

### Technical
- Estructura de carpetas organizada (Service, Form, Controller, Templates)
- Migraciones ejecutadas correctamente (fecha: 2025-11-08)
- Integración bidireccional de entidades (Product ↔ StockMovement ↔ User)
- Manejo de errores con excepciones personalizadas (`InvalidArgumentException`, `Exception`)
- Validación server-side con Symfony Validator

### Changed
- Ruta base del controlador: `/admin/stock/movement` (consistente con producto)
- Menú lateral ahora incluye iconos para mejor identificación visual
- Templates refactorizados para usar sistema de card y partials

## [v1.11.0] - 2025-11-07

### Added
- **Sistema de Control de Inventario** 🎯
  - Nueva entidad `StockMovement` para registro de movimientos
  - Enum `TipoMovimiento` (ENTRADA, SALIDA, AJUSTE)
  - Validaciones robustas con mensajes en español
  - Documentación exhaustiva del sistema
  - Preparación para implementación del servicio y controlador

### Technical
- Relaciones bidireccionales con Product y User
- Constructor con fecha automática
- Validaciones @Assert completas
- Enumeración para tipos de movimiento

## [v1.10.0] - 2025-10-26

### Added

- **Sistema completo de gestión de imágenes de productos**:
  - Carousel Bootstrap 5 en la vista `show` del producto con indicadores, controles prev/next y auto-rotación.
  - Layout 50/50: carousel a la izquierda (400px altura, object-fit: contain), información del producto a la derecha.
  - Si no hay imágenes, la información ocupa 100% con alerta informativa.
  - Contador de imágenes debajo del carousel.
  
- **Subida de imágenes al crear producto**:
  - Formulario integrado en `new.html.twig` para subir hasta 10 imágenes al crear el producto.
  - Procesamiento directo de archivos del request en `ProductController::new()`.
  - Al crear producto con imágenes, redirige automáticamente al `show` (en lugar del listado).
  - Mensaje flash dinámico según cantidad de imágenes subidas.

- **Gestión de imágenes en edición**:
  - Botón "Eliminar todas las imágenes" en la vista `edit` con confirmación SweetAlert2.
  - Endpoint `POST /admin/product/{id}/images/delete-all` que elimina todas las imágenes del producto (BD + archivos físicos).
  - Validación JavaScript en formulario de subida: no permite enviar sin seleccionar archivos (mensaje con SweetAlert2).
  - Mensajes de error específicos al subir imágenes: límite de cantidad, tipo no permitido, tamaño excedido, CSRF inválido.

### Fixed

- **Eliminación en cascada de productos con imágenes**:
  - `Product.php`: agregado `cascade: ['persist', 'remove']` y `orphanRemoval: true` en relación OneToMany.
  - `ProductController::delete()`: elimina archivos físicos antes de borrar el producto.
  - Migración `Version20251026030000`: altera FK de `product_image` para agregar `ON DELETE CASCADE`.
  - Resuelto error de integridad referencial al eliminar productos con imágenes asociadas.

### Technical

- Validación client-side con SweetAlert2 (más amigable que alert nativo).
- Recolección y display de errores específicos de formulario mediante `form->getErrors(true, true)`.
- Procesamiento manual de archivos en creación de productos para evitar problemas de múltiples formularios.
- Eliminación física de archivos en servidor sincronizada con eliminación en BD.

## [v1.9.1] - 2025-10-25

### Added

- Controlador `ProductImageController`: endpoints base para subir múltiples imágenes a un producto y eliminar una imagen individual.
- Plantilla generada por maker para `product_image` (no utilizada en producción, se mantendrá para referencia temporal).

### Technical

- Configuración: parámetro `uploads_products_dir` en `services.yaml` para definir el directorio absoluto de subidas (`public/uploads/products`).
- README y CHANGELOG actualizados a v1.9.1 con próximos pasos claros (integración de formulario en la vista y carrusel en show del producto).

## [v1.9.0] - 2025-10-25

### Added

- Productos: entidad `ProductImage` para gestionar múltiples imágenes por producto.
- Relación OneToMany en `Product` → `ProductImage` (colección de imágenes).
- Migración: tabla `product_image` con FK a `product` y campos `imageName`, `imagePath`, `position`, `createdAt`.
- Directorio de almacenamiento: `public/uploads/products/` para guardar archivos de imágenes.
- Formulario `ProductImageType` para carga múltiple de imágenes:
  - Validación de máximo 10 imágenes por producto.
  - Validación de tamaño máximo: 5MB por imagen.
  - Formatos permitidos: JPEG, PNG, WEBP.
  - Documentación exhaustiva en español sobre arquitectura y seguridad.

### Technical

- Configuración `.gitignore` actualizada para excluir archivos subidos (mantiene `.gitkeep`).
- Validaciones server-side robustas con `Assert\Count` y `Assert\Image`.
- Campo `FileType` con `multiple=true` y `mapped=false` para procesamiento manual en controlador.
- Preparación de infraestructura para carga, visualización en carousel y eliminación individual de imágenes.

## [v1.8.0] - 2025-10-25

### Added

- Autenticación: nuevo layout moderno para login y register con diseño glass y animaciones.
- Branding: incorporación de marca AquaPanel (logo SVG en `public/logo-aquapanel.svg`) y favicon.
- Home: navbar brand actualizado (logo + AquaPanel) y colores unificados.
- Registro de usuarios: campos adicionales `name` y `fecha_cumpleanos` con validaciones básicas.
- Seguridad UX: mensaje flash "Acceso denegado" al intentar acceder a `/home` sin autenticación.

### Changed

- Unificación visual de colores (azules) en páginas de autenticación y layout principal.
- README actualizado con logo y versión v1.8.0.

### Technical

- Plantillas: `templates/auth/base.html.twig`, `templates/security/login.html.twig`, `templates/registration/register.html.twig`, `templates/home/base.html.twig`.
- Formulario: `src/Form/RegistrationFormType.php` con nuevos campos.

## [v1.7.0] - 2025-10-25

### Added
- Usuarios: campo booleano `activo` en la entidad, migración automática y columna "Estado" en el listado.
- Usuarios: filtros en el index por texto (nombre/email), rol (ROLE_USER/ROLE_ADMIN) y estado (Activo/Inactivo).
- Productos: columna "Descripción" en el listado con truncado seguro.

### Changed
- Buscadores unificados con diseño neutro y alturas consistentes en categorías, productos y usuarios.
- Productos: simplificado el buscador al modo básico (campo + texto) usando Nombre, SKU o Descripción.

### Fixed
- Twig: reemplazo del filtro `u.truncate` para evitar dependencia de `twig/string-extra` y errores 500.
- Productos: limpieza de estilos duplicados que se renderizaban como texto en la tarjeta.

### Technical
- Migración `Version20251025191354` añade `user.activo TINYINT(1) DEFAULT 1 NOT NULL`.
- Commit y tag publicados: `v1.7.0`.

## [v1.6.1] - 2025-10-25

### Removed

- Eliminadas la ruta y la plantilla de diagnóstico `/test-icons` del repositorio.
  - La interfaz AdminLTE-like y las herramientas de card permanecen intactas.

## [v1.6.0] - 2025-10-25

### Added
- Página de diagnóstico `/test-icons` y controlador asociado para verificar renderizado de íconos y herramientas de card.

### Changed
- Herramientas de card (colapsar/cerrar):
  - Iconos siempre visibles y con mejor área de clic y estados hover.
  - Cambio a CDN de Font Awesome 6.4.0 por mayor estabilidad.
  - Respaldo con símbolos Unicode (− / × vía `::before`) para funcionar incluso si FA no carga.
  - Estilos finales minimalistas y consistentes.
- Estética AdminLTE:
  - Fondo general y del contenido a gris suave `#f4f6f9` para resaltar cards.
  - Cards blancas con sombra doble sutil que las hace “flotar”.

### Notes
- La ruta `/test-icons` es solo para dev/QA; puede eliminarse más adelante.

## [v1.5.3] - 2025-10-25

### Changed

- UI: Formularios de Usuario y Categoría con botones alineados en una sola fila (Guardar, Volver, Eliminar) usando utilidades Bootstrap (`d-flex`, `gap-2`, `flex-wrap`).
- UX: Títulos de listados actualizados a formato unificado “Listado de …” para Usuarios, Categorías y Productos.

### Fixed

- Categoría: normalización del nombre con `trim()` para evitar duplicados por espacios.

### Technical

- Validación reforzada en `Categoria`:
  - `#[UniqueEntity(fields: ['nombre'])]` para unicidad a nivel de aplicación.
  - Columna `nombre` con `unique: true` y longitud mínima `min=2`.
  - `descripcion` limitada a `max=2000` caracteres.
- Migración añadida para el índice único en `categoria.nombre` (Version20251025144724).

## [v1.5.2] - 2025-10-24

### Fixed
- **Legibilidad de badges de stock**: Añadido `text-dark` a badges con `bg-warning` (amarillo) en vistas de productos para mejorar contraste y legibilidad.
- **Consistencia en badges de stock**: Unificado texto "N unidades" en templates `index.html.twig` y `show.html.twig` de productos.
- **Mensajes flash en perfil**: Añadido `data-turbo="false"` al formulario de edición de perfil para garantizar visibilidad de mensajes de éxito tras redirección.
- **Errores de tipo en formularios**: Implementado `empty_data` en ProductType para prevenir excepciones 500:
  - `empty_data: ''` en campos de texto (sku, nombre) para convertir null a string vacío.
  - `empty_data: 0` en campo stock (IntegerType) para convertir null/valores inválidos a 0.
- **Display de errores de validación**: Reescrito bloque de errores en `_form.html.twig` de productos para iterar `form.children` y mostrar mensajes específicos de cada campo con sus etiquetas correspondientes.

### Changed
- Template `product/index.html.twig`: Aplicada lógica de badges con colores (verde >10, amarillo 1-10, rojo 0) en lugar de números planos.
- Template `product/show.html.twig`: Badge de stock con `text-dark` en estado warning.
- Template `product/_form.html.twig`: Resumen de errores exhaustivo mostrando campo por campo con `error.message`.

### Technical
- **Arquitectura de validación robusta**: Implementada doble capa de conversión de datos:
  1. `empty_data` convierte entradas vacías/inválidas a tipos válidos ('' para strings, 0 para integers).
  2. Validaciones @Assert procesan valores convertidos y muestran mensajes configurados.
- **UX mejorada**: Usuarios ven errores específicos ("El SKU es obligatorio", "El stock debe ser mayor o igual a 0") en lugar de errores genéricos o pantallas de error 500.
- **Turbo Drive**: Deshabilitación selectiva en formularios de perfil para compatibilidad con sistema de flash messages.

## [v1.5.1] - 2025-10-24

### Fixed
- **Formularios con Turbo Drive**: Añadido `data-turbo="false"` a todos los formularios del proyecto para solucionar problemas con mensajes flash y recarga de contenido:
  - Formularios de Usuario (new, edit)
  - Formularios de Categoría (new, edit)
  - Formularios de Producto (new, edit)
- **Recarga completa en categorías**: Forzada recarga de página completa en formularios de categorías para garantizar sincronización de estado.

### Technical
- **Compatibilidad Turbo**: Implementación de patrón consistente `data-turbo="false"` en todos los formularios CRUD para evitar conflictos con Hotwire Turbo Drive y sistema de flash messages de Symfony.

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


[v1.10.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.10.0
[v1.9.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.9.0
[v1.9.1]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.9.1
[v1.5.3]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.5.3
[v1.8.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.8.0
[v1.7.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.7.0
[v1.6.1]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.6.1
[v1.6.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.6.0
[v1.5.2]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.5.2
[v1.5.1]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.5.1
[v1.4.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.4.0
[v1.3.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.3.0
[v1.2.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.2.0
[v1.1.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.1.0
[v1.0.1]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.0.1
[v1.0.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.0.0
