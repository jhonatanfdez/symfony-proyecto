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

        // Parámetros de búsqueda básicos: campo + texto
        $field = (string) $request->query->get('field', 'nombre');
        $textQuery = trim((string) $request->query->get('query', ''));

        $qb = $productRepository->createQueryBuilder('p');

        // Texto libre por campo permitido
        $allowedFields = ['nombre', 'sku', 'descripcion'];
        if ($textQuery !== '' && in_array($field, $allowedFields, true)) {
            $qb->andWhere($qb->expr()->like("p.$field", ':q'))
               ->setParameter('q', "%$textQuery%");
        }

        // Nota: filtros por categoría, stock y activo fueron retirados para simplificar la búsqueda

        // Orden por defecto
        $qb->orderBy('p.nombre', 'ASC');

        $products = $qb->getQuery()->getResult();

        return $this->render('product/index.html.twig', [
            'products' => $products,
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

        // Formulario principal de edición del producto
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Producto actualizado exitosamente.');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        // Formulario adicional para gestión de imágenes del producto
        // Este formulario permite subir múltiples imágenes (máx 10, 5MB c/u, JPEG/PNG/WEBP)
        // Se procesa en ProductImageController::uploadImages()
        $uploadForm = $this->createForm(\App\Form\ProductImageType::class);

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'uploadForm' => $uploadForm->createView(), // Vista del formulario de subida de imágenes
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
