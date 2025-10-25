<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test-icons', name: 'test_icons')]
    public function testIcons(): Response
    {
        return $this->render('test_icons.html.twig');
    }
}
