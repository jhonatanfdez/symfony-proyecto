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
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
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

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, \App\Security\AdminAccessGuard $guard): Response
    {
        if ($redirect = $guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

             //aqui se añade una variable para el mensaje flash

            $this->addFlash('success', 'Usuario eliminado exitosamente.');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
