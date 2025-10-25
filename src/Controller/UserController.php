<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, \App\Security\AdminAccessGuard $guard): Response
    {
        if ($redirect = $guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        // Parámetros de búsqueda y filtros
        $field = (string) $request->query->get('field', 'name');
        $textQuery = trim((string) $request->query->get('query', ''));
        $role = $request->query->get('role'); // 'ROLE_ADMIN' | 'ROLE_USER' | null
        $activo = $request->query->get('activo'); // '1' | '0' | null

        $qb = $userRepository->createQueryBuilder('u');

        // Filtro de texto
        $allowedFields = ['name', 'email'];
        if ($textQuery !== '' && in_array($field, $allowedFields, true)) {
            $qb->andWhere($qb->expr()->like("u.$field", ':q'))
               ->setParameter('q', "%$textQuery%");
        }

        // Filtro por rol (roles es JSON serializado)
        if ($role === 'ROLE_ADMIN' || $role === 'ROLE_USER') {
            $qb->andWhere('u.roles LIKE :roleLike')
               ->setParameter('roleLike', '%"' . $role . '"%');
        }

        // Filtro activo/inactivo
        if ($activo === '1' || $activo === '0') {
            $qb->andWhere('u.activo = :activo')
               ->setParameter('activo', $activo === '1');
        }

        $qb->orderBy('u.name', 'ASC');
        $users = $qb->getQuery()->getResult();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, \App\Security\AdminAccessGuard $guard): Response
    {
        if ($redirect = $guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Convertir el rol seleccionado a array
            $selectedRole = $form->get('roles')->getData();
            if ($selectedRole) {
                $user->setRoles([$selectedRole]);
            }

            // Hashear la contraseña antes de guardar
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

             //aqui se añade una variable para el mensaje flash
            $this->addFlash('success', 'Usuario creado exitosamente.');

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(Request $request, User $user, \App\Security\AdminAccessGuard $guard): Response
    {
        if ($redirect = $guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, \App\Security\AdminAccessGuard $guard): Response
    {
        if ($redirect = $guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }
        // Guardar los valores originales
        $originalEmail = $user->getEmail();
        $originalName = $user->getName();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Convertir el rol seleccionado a array
            $selectedRole = $form->get('roles')->getData();
            if ($selectedRole) {
                $user->setRoles([$selectedRole]);
            }

            // Email: actualizar solo si no está vacío
            if (empty($form->get('email')->getData())) {
                $user->setEmail($originalEmail);
            }

            // Name: actualizar solo si no está vacío
            if (empty($form->get('name')->getData())) {
                $user->setName($originalName);
            }

            // Password: actualizar solo si se proporcionó una nueva
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            //aqui se añade una variable para el mensaje flash
            $this->addFlash('success', 'Usuario actualizado exitosamente.');

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Elimina un usuario de la base de datos
     *
     * FLUJO DE ELIMINACIÓN CON SWEETALERT2:
     * 1. Usuario hace clic en botón "Eliminar" en el listado (templates/user/index.html.twig)
     * 2. El formulario tiene la clase "js-delete-form" que activa el listener en base.html.twig
     * 3. El JavaScript intercepta el submit y muestra un modal SweetAlert2 de confirmación:
     *    - Título: "¿Eliminar usuario?" (personalizado con data-swal-title)
     *    - Texto: "Esta acción no se puede deshacer." (personalizado con data-swal-text)
     *    - Botones: "Cancelar" (gris) y "Sí, eliminar" (rojo)
     * 4. Si el usuario hace clic en "Cancelar": El modal se cierra, no pasa nada
     * 5. Si el usuario hace clic en "Sí, eliminar": El formulario se envía a esta ruta (POST)
     * 6. Este método recibe la petición solo si hubo confirmación
     * 7. Valida permisos de administrador (AdminAccessGuard)
     * 8. Valida el token CSRF para seguridad
     * 9. Elimina el usuario de la base de datos
     * 10. Muestra mensaje flash de éxito y redirige al listado
     *
     * @param Request $request - Objeto con los datos de la petición HTTP
     * @param User $user - Entidad del usuario a eliminar (inyectada automáticamente por Symfony según el ID)
     * @param EntityManagerInterface $entityManager - Servicio de Doctrine para operaciones con BD
     * @param AdminAccessGuard $guard - Servicio personalizado para verificar permisos de administrador
     * @return Response - Redirección al listado de usuarios
     */
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, \App\Security\AdminAccessGuard $guard): Response
    {
        // Verifica que el usuario tenga ROLE_ADMIN
        // Si no, redirige a home con flash message de error
        if ($redirect = $guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        // Valida el token CSRF para proteger contra ataques de falsificación de peticiones
        // El token debe coincidir con el generado en el formulario (csrf_token('delete' ~ user.id))
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            // Marca la entidad User para ser eliminada
            $entityManager->remove($user);

            // Ejecuta la eliminación en la base de datos (DELETE FROM user WHERE id = ?)
            $entityManager->flush();

            // Añade un mensaje flash de éxito que se mostrará en el listado con fondo verde
            // Este mensaje se renderiza en base.html.twig dentro del bloque de flash messages
            // El tipo 'success' activa el estilo de alerta verde (alert-success)
            $this->addFlash('success', 'Usuario eliminado exitosamente.');
        }

        // Redirige al listado de usuarios con código HTTP 303 (See Other)
        // Este código indica que la respuesta se encuentra en otra URI (evita reenvíos duplicados)
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
