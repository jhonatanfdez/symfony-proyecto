<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        $email = $user->getUserIdentifier();

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $user,
            'email' => $email,
        ]);
    }

    #[Route('/miperfil', name: 'app_profile_show', methods: ['GET'])]
    public function show(): Response
    {
        // Obtener el usuario autenticado
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Debes iniciar sesión para ver tu perfil.');
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/editar', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Obtener el usuario autenticado
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Debes iniciar sesión para editar tu perfil.');
        }

        // Guardar los valores originales
        $originalEmail = $user->getUserIdentifier();
        $originalName = $user->getName();

        $form = $this->createForm(UserType::class, $user, [
            'disable_roles' => true, // Deshabilitar el campo de roles en el perfil
        ]);
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

            $this->addFlash('success', 'Perfil actualizado exitosamente.');

            return $this->redirectToRoute('app_profile_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
