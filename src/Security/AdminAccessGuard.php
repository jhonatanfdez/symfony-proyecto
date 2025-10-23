<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Pequeño guard utilitario para proteger rutas administrativas.
 * Si la URL comienza con /admin y el usuario no es ROLE_ADMIN,
 * devuelve una RedirectResponse a la página de inicio.
 */
class AdminAccessGuard
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * Si corresponde, retorna una redirección; si no, null para continuar.
     */
    public function maybeRedirect(Request $request, ?UserInterface $user): ?RedirectResponse
    {
        $path = $request->getPathInfo() ?? '';

        // Solo aplicamos la regla al prefijo administrativo
        $isAdminArea = str_starts_with($path, '/admin');
        if (!$isAdminArea) {
            return null;
        }

        // Si no hay usuario o no tiene ROLE_ADMIN, agregar flash y redirigir a home
        $hasAdminRole = $user && in_array('ROLE_ADMIN', $user->getRoles(), true);
        if (!$hasAdminRole) {
            // Mensaje flash rojo (plantilla usa 'error' -> alert-danger)
            if ($request->hasSession()) {
                $session = $request->getSession();
                if ($session) {
                    // Usar la bolsa de flashes registrada por defecto
                    /** @var FlashBagInterface $flashBag */
                    $flashBag = $session->getBag('flashes');
                    $flashBag->add('error', 'Usted no tiene acceso a esta ruta');
                }
            }
            $homeUrl = $this->urlGenerator->generate('app_home');
            return new RedirectResponse($homeUrl);
        }

        return null;
    }
}
