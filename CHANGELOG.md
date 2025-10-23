# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

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
- Layout con menú lateral y dashboard inicial.


[v1.2.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.2.0
[v1.1.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.1.0
[v1.0.1]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.0.1
[v1.0.0]: https://github.com/jhonatanfdez/symfony-proyecto/releases/tag/v1.0.0
