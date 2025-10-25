<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{

    #[Route(path: '/', name: 'app_root')]
    public function root(): Response
    {
        return $this->redirectToRoute('app_login');
    }


    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        // Redirigir a login si el usuario no está autenticado
        if (!$this->getUser()) {
            // Mostrar mensaje de error si intenta acceder sin autenticarse
            $this->addFlash('error', 'Acceso denegado. Por favor, inicia sesión.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/index.html.twig', [
            'page_title' => 'Dashboard',
            'template_path' => null
        ]);
    }

    #[Route('/admin/prueba', name: 'app_prueba')]
    public function prueba(\Symfony\Component\HttpFoundation\Request $request, \App\Security\AdminAccessGuard $guard): Response
    {
        if ($redirect = $guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }
        return $this->render('home/prueba.html.twig', [
            'page_title' => 'Página de Prueba'
        ]);
    }
}
