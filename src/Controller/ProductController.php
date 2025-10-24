<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Security\AdminAccessGuard;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/product')]
final class ProductController extends AbstractController
{

    public function __construct(
        private readonly AdminAccessGuard $guard
    ) {
    }

    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        $product = new Product();
        // Setear automáticamente el usuario que crea el producto
        $product->setCreateBy($this->getUser());


        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Producto creado exitosamente.');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product, Request $request): Response
    {
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {

        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Producto actualizado exitosamente.');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {

        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();


        }

        $this->addFlash('success', 'Producto eliminado exitosamente.');

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
