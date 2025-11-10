<?php

namespace App\Controller;

use App\Entity\StockMovement;
use App\Form\StockMovementType;
use App\Repository\StockMovementRepository;
use App\Service\StockMovementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para la gestión de movimientos de stock
 *
 * Rutas base: /admin/stock/movement
 *
 * Responsabilidades:
 * - Listar todos los movimientos de inventario
 * - Crear nuevos movimientos (entradas, salidas, ajustes)
 * - Ver detalles de un movimiento específico
 * - Editar movimientos existentes
 * - Eliminar movimientos
 *
 * IMPORTANTE: Este controlador delega la lógica de negocio al StockMovementService
 * que se encarga de validaciones, cálculos de stock y auditoría
 */
#[Route('/admin/stock/movement')]
final class StockMovementController extends AbstractController
{
    /**
     * Instancia del servicio de movimientos de stock
     * Se inyecta automáticamente por Symfony
     */
    private StockMovementService $stockMovementService;

    /**
     * Constructor con inyección de dependencias
     *
     * @param StockMovementService $stockMovementService Servicio para gestionar movimientos
     */
    public function __construct(StockMovementService $stockMovementService)
    {
        $this->stockMovementService = $stockMovementService;
    }

    /**
     * Lista todos los movimientos de stock
     *
     * Ruta: GET /admin/stock/movement
     *
     * @param StockMovementRepository $stockMovementRepository Repositorio para acceder a movimientos
     * @return Response Renderiza la plantilla con el listado
     */
    #[Route(name: 'app_stock_movement_index', methods: ['GET'])]
    public function index(StockMovementRepository $stockMovementRepository): Response
    {
        return $this->render('stock_movement/index.html.twig', [
            'stock_movements' => $stockMovementRepository->findAll(),
        ]);
    }

    /**
     * Crea un nuevo movimiento de stock
     *
     * Ruta: GET/POST /admin/stock/movement/new
     *
     * Flujo:
     * 1. GET: Muestra el formulario vacío
     * 2. POST: Valida y procesa el formulario
     * 3. Si es válido: Llama al servicio para crear el movimiento
     * 4. El servicio automáticamente:
     *    - Establece la fecha actual
     *    - Asigna el usuario actual (desde getUser())
     *    - Actualiza el stock del producto
     *    - Valida reglas de negocio
     * 5. Redirige al listado con mensaje de éxito/error
     *
     * @param Request $request Objeto de la solicitud HTTP
     * @param EntityManagerInterface $entityManager Gestor de entidades (no usado directamente, el servicio lo maneja)
     * @return Response Renderiza el formulario o redirige
     */
    #[Route('/new', name: 'app_stock_movement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stockMovement = new StockMovement();
        $form = $this->createForm(StockMovementType::class, $stockMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Usar el servicio para crear el movimiento
                // El servicio se encarga de validaciones y actualizar el stock
                $this->stockMovementService->createMovement(
                    $stockMovement->getProduct(),
                    $this->getUser(),
                    $stockMovement->getCantidad(),
                    $stockMovement->getTipo(),
                    $stockMovement->getDescripcion()
                );

                // Mensaje de éxito
                $this->addFlash('success', 'Movimiento de stock creado exitosamente.');
                return $this->redirectToRoute('app_stock_movement_index', [], Response::HTTP_SEE_OTHER);

            } catch (\InvalidArgumentException $e) {
                // Error de validación de negocio (stock insuficiente, etc)
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                // Error genérico
                $this->addFlash('error', 'Error al crear el movimiento: ' . $e->getMessage());
            }
        }

        return $this->render('stock_movement/new.html.twig', [
            'stock_movement' => $stockMovement,
            'form' => $form,
        ]);
    }

    /**
     * Muestra los detalles de un movimiento específico
     *
     * Ruta: GET /admin/stock/movement/{id}
     *
     * @param StockMovement $stockMovement Movimiento inyectado por Symfony (parameter converter)
     * @return Response Renderiza la plantilla de detalles
     */
    #[Route('/{id}', name: 'app_stock_movement_show', methods: ['GET'])]
    public function show(StockMovement $stockMovement): Response
    {
        return $this->render('stock_movement/show.html.twig', [
            'stock_movement' => $stockMovement,
        ]);
    }

    /**
     * Edita un movimiento de stock existente
     *
     * Ruta: GET/POST /admin/stock/movement/{id}/edit
     *
     * Flujo:
     * 1. GET: Muestra el formulario con campos bloqueados (fecha y usuario)
     * 2. POST: Procesa cambios en cantidad, tipo y descripción
     * 3. Los campos fecha y createBy se mostran como solo lectura
     *
     * NOTA: Actualmente permite editar cualquier movimiento.
     * PENDIENTE: Considerar si se debe restringir la edición
     * (ej: solo movimientos del día actual, solo por quién lo creó)
     *
     * @param Request $request Objeto de la solicitud HTTP
     * @param StockMovement $stockMovement Movimiento a editar (inyectado por parameter converter)
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return Response Renderiza el formulario o redirige
     */
    #[Route('/{id}/edit', name: 'app_stock_movement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StockMovement $stockMovement, EntityManagerInterface $entityManager): Response
    {
        // Pasar is_edit=true para mostrar campos deshabilitados
        $form = $this->createForm(StockMovementType::class, $stockMovement, [
            'is_edit' => true  // ← Opción para mostrar campos de solo lectura
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_movement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock_movement/edit.html.twig', [
            'stock_movement' => $stockMovement,
            'form' => $form,
        ]);
    }    /**
     * Elimina un movimiento de stock
     *
     * Ruta: POST /admin/stock/movement/{id}
     *
     * NOTA: Actualmente permite eliminar cualquier movimiento.
     * PENDIENTE: Usar StockMovementService->deleteMovement() para validar
     * reglas de negocio (solo hoy, solo último movimiento, etc)
     *
     * @param Request $request Objeto de la solicitud HTTP
     * @param StockMovement $stockMovement Movimiento a eliminar
     * @param EntityManagerInterface $entityManager Gestor de entidades
     * @return Response Redirige al listado
     */
    #[Route('/{id}', name: 'app_stock_movement_delete', methods: ['POST'])]
    public function delete(Request $request, StockMovement $stockMovement, EntityManagerInterface $entityManager): Response
    {
        // Validar token CSRF
        if ($this->isCsrfTokenValid('delete'.$stockMovement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stockMovement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stock_movement_index', [], Response::HTTP_SEE_OTHER);
    }
}
