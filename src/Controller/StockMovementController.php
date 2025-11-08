<?php

namespace App\Controller;

use App\Entity\StockMovement;
use App\Form\StockMovementType;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/stock/movement')]
final class StockMovementController extends AbstractController
{
    #[Route(name: 'app_stock_movement_index', methods: ['GET'])]
    public function index(StockMovementRepository $stockMovementRepository): Response
    {
        return $this->render('stock_movement/index.html.twig', [
            'stock_movements' => $stockMovementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_stock_movement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stockMovement = new StockMovement();
        $form = $this->createForm(StockMovementType::class, $stockMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stockMovement);
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_movement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock_movement/new.html.twig', [
            'stock_movement' => $stockMovement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_movement_show', methods: ['GET'])]
    public function show(StockMovement $stockMovement): Response
    {
        return $this->render('stock_movement/show.html.twig', [
            'stock_movement' => $stockMovement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_movement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StockMovement $stockMovement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StockMovementType::class, $stockMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_movement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock_movement/edit.html.twig', [
            'stock_movement' => $stockMovement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_movement_delete', methods: ['POST'])]
    public function delete(Request $request, StockMovement $stockMovement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stockMovement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stockMovement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stock_movement_index', [], Response::HTTP_SEE_OTHER);
    }
}
