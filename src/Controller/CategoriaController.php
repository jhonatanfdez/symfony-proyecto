<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Form\CategoriaType;
use App\Security\AdminAccessGuard;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('admin/categoria')]
final class CategoriaController extends AbstractController
{
    public function __construct(
        private readonly AdminAccessGuard $guard
    ) {
    }

    #[Route(name: 'app_categoria_index', methods: ['GET'])]
    public function index(CategoriaRepository $categoriaRepository, Request $request): Response
    {
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        // Capturar parámetros de búsqueda desde la URL
        $searchField = $request->query->get('field', 'nombre'); // Default: nombre
        $searchQuery = $request->query->get('query', '');

        // Si hay una query de búsqueda, filtrar; sino, traer todas
        if (!empty($searchQuery)) {
            // Validar que el campo sea uno permitido para evitar inyección SQL
            $allowedFields = ['nombre', 'descripcion'];
            if (!in_array($searchField, $allowedFields)) {
                $searchField = 'nombre';
            }

            // Buscar con LIKE %query% para coincidir en cualquier parte del texto
            $categorias = $categoriaRepository->createQueryBuilder('c')
                ->where("c.$searchField LIKE :query")
                ->setParameter('query', '%' . $searchQuery . '%')
                ->getQuery()
                ->getResult();
        } else {
            $categorias = $categoriaRepository->findAll();
        }

        return $this->render('categoria/index.html.twig', [
            'categorias' => $categorias,
        ]);
    }

    #[Route('/new', name: 'app_categoria_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        $categorium = new Categoria();
        $form = $this->createForm(CategoriaType::class, $categorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorium);
            $entityManager->flush();

            // Mensaje flash de éxito para creación
            $this->addFlash('success', 'Categoría creada exitosamente.');

            return $this->redirectToRoute('app_categoria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categoria/new.html.twig', [
            'categorium' => $categorium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categoria_show', methods: ['GET'])]
    public function show(Categoria $categorium, Request $request): Response
    {
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        return $this->render('categoria/show.html.twig', [
            'categorium' => $categorium,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categoria_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categoria $categorium, EntityManagerInterface $entityManager): Response
    {
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        $form = $this->createForm(CategoriaType::class, $categorium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Mensaje flash de éxito para actualización
            $this->addFlash('success', 'Categoría actualizada exitosamente.');

            return $this->redirectToRoute('app_categoria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categoria/edit.html.twig', [
            'categorium' => $categorium,
            'form' => $form,
        ]);
    }

    /**
     * Elimina una categoría de la base de datos
     *
     * FLUJO DE ELIMINACIÓN CON SWEETALERT2:
     * 1. Usuario hace clic en botón "Eliminar" en la vista
     * 2. El JavaScript en base.html.twig intercepta el submit del formulario
     * 3. SweetAlert2 muestra un modal de confirmación con dos botones:
     *    - "Cancelar": Cierra el modal, no pasa nada
     *    - "Sí, eliminar": Envía el formulario a esta ruta (POST)
     * 4. Este método recibe la petición solo si el usuario confirmó
     * 5. Valida el token CSRF para seguridad
     * 6. Elimina la categoría y redirige al listado
     *
     * @param Request $request - Objeto con los datos de la petición HTTP
     * @param Categoria $categorium - Entidad a eliminar (inyectada automáticamente por Symfony según el ID de la URL)
     * @param EntityManagerInterface $entityManager - Servicio de Doctrine para operaciones con BD
     * @return Response - Redirección al listado de categorías
     */
    #[Route('/{id}', name: 'app_categoria_delete', methods: ['POST'])]
    public function delete(Request $request, Categoria $categorium, EntityManagerInterface $entityManager): Response
    {
        // Verifica que el usuario tenga permisos de administrador (ROLE_ADMIN)
        // Si no los tiene, redirige a home con mensaje de error
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        // Valida el token CSRF para proteger contra ataques de falsificación de peticiones
        // El token debe coincidir con el generado en el formulario (templates/categoria/_delete_form.html.twig)
        if ($this->isCsrfTokenValid('delete'.$categorium->getId(), $request->getPayload()->getString('_token'))) {
            // Marca la entidad para ser eliminada
            $entityManager->remove($categorium);

            // Ejecuta la eliminación en la base de datos (DELETE FROM categoria WHERE id = ?)
            $entityManager->flush();

            // Añade un mensaje flash de éxito que se mostrará en el listado con fondo verde
            // Este mensaje se renderiza en base.html.twig dentro del bloque de flash messages
            // El tipo 'success' activa el estilo de alerta verde (alert-success)
            $this->addFlash('success', 'Categoría eliminada exitosamente.');
        }

        // Redirige al listado de categorías con código HTTP 303 (See Other)
        return $this->redirectToRoute('app_categoria_index', [], Response::HTTP_SEE_OTHER);
    }
}
