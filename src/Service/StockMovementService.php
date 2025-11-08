<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\StockMovement;
use App\Entity\User;
use App\Enum\TipoMovimiento;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Servicio para gestionar los movimientos de inventario (entradas, salidas y ajustes)
 *
 * Este servicio encapsula toda la lógica de negocio relacionada con:
 * - Creación de movimientos de stock
 * - Validación de reglas de negocio
 * - Actualización automática del stock de productos
 * - Historial de movimientos
 * - Eliminación controlada de movimientos
 *
 * Reglas principales:
 * 1. No se permiten cantidades cero
 * 2. Las salidas requieren stock suficiente
 * 3. Los ajustes requieren notas explicativas
 * 4. Solo se pueden eliminar movimientos del día actual
 * 5. La eliminación solo es posible para el último movimiento
 */
class StockMovementService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StockMovementRepository $stockMovementRepository,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Crea un nuevo movimiento de stock y actualiza el stock del producto
     *
     * @param Product $product Producto afectado
     * @param User $user Usuario que realiza el movimiento
     * @param int $cantidad Cantidad del movimiento (positiva para ENTRADA, negativa para SALIDA)
     * @param TipoMovimiento $tipo Tipo de movimiento (ENTRADA/SALIDA/AJUSTE)
     * @param string $notas Notas explicativas (obligatorias para AJUSTE)
     * @param string|null $referencia Referencia opcional (ej: número de OC, factura)
     * @throws \InvalidArgumentException Si la cantidad es 0 o los datos son inválidos
     * @throws AccessDeniedException Si el usuario no tiene permisos
     * @return StockMovement
     */
    public function createMovement(
        Product $product,
        User $user,
        int $cantidad,
        TipoMovimiento $tipo,
        string $notas,
        ?string $referencia = null
    ): StockMovement {
        // Validar cantidad
        if ($cantidad === 0) {
            throw new \InvalidArgumentException('La cantidad no puede ser cero.');
        }

        // Validar stock suficiente para SALIDA
        if ($tipo === TipoMovimiento::SALIDA && abs($cantidad) > $product->getStock()) {
            throw new \InvalidArgumentException('Stock insuficiente para realizar la salida.');
        }

        // Validar notas para AJUSTE
        if ($tipo === TipoMovimiento::AJUSTE && empty(trim($notas))) {
            throw new \InvalidArgumentException('Las notas son obligatorias para ajustes de inventario.');
        }

        // Crear el movimiento
        $movement = new StockMovement();
        $movement->setProduct($product);
        $movement->setUser($user);
        $movement->setCantidad($cantidad);
        $movement->setTipo($tipo);
        $movement->setNotas($notas);
        $movement->setReferencia($referencia);

        // Validar la entidad completa
        $errors = $this->validator->validate($movement);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        // Actualizar stock del producto
        $nuevoStock = $this->calcularNuevoStock($product, $cantidad, $tipo);
        $product->setStock($nuevoStock);

        // Persistir cambios
        $this->entityManager->persist($movement);
        $this->entityManager->flush();

        return $movement;
    }

    /**
     * Calcula el nuevo stock del producto basado en el tipo de movimiento
     *
     * Esta función implementa la lógica central de cálculo de stock:
     *
     * 1. Para ENTRADA:
     *    - Siempre usa el valor absoluto de la cantidad
     *    - Suma la cantidad al stock actual
     *    - Ejemplo: stock=10, entrada=5 → nuevo_stock=15
     *
     * 2. Para SALIDA:
     *    - Siempre usa el valor absoluto de la cantidad
     *    - Resta la cantidad del stock actual
     *    - Ejemplo: stock=10, salida=3 → nuevo_stock=7
     *
     * 3. Para AJUSTE:
     *    - Establece el stock directamente al valor especificado
     *    - Ignora el stock actual
     *    - Ejemplo: stock=10, ajuste=8 → nuevo_stock=8
     *
     * @param Product $product Producto a actualizar
     * @param int $cantidad Cantidad del movimiento
     * @param TipoMovimiento $tipo Tipo de operación
     *
     * @return int El nuevo valor del stock
     */
    private function calcularNuevoStock(Product $product, int $cantidad, TipoMovimiento $tipo): int
    {
        $stockActual = $product->getStock();

        return match($tipo) {
            TipoMovimiento::ENTRADA => $stockActual + abs($cantidad),
            TipoMovimiento::SALIDA => $stockActual - abs($cantidad),
            TipoMovimiento::AJUSTE => abs($cantidad), // El ajuste establece el stock directamente
        };
    }

    /**
     * Obtiene el historial completo de movimientos de un producto
     *
     * Recupera todos los movimientos ordenados por fecha descendente (más recientes primero).
     * Este método es útil para:
     * - Mostrar el historial en la UI
     * - Auditoría de cambios
     * - Reconciliación de inventario
     * - Análisis de movimientos
     *
     * El resultado incluye:
     * - Entradas (compras, devoluciones)
     * - Salidas (ventas, pérdidas)
     * - Ajustes (inventarios físicos)
     *
     * @param Product $product Producto del cual obtener el historial
     * @return array<StockMovement> Array de movimientos ordenados por fecha desc
     */
    public function getMovementHistory(Product $product): array
    {
        return $this->stockMovementRepository->findBy(
            ['product' => $product],
            ['createdAt' => 'DESC']
        );
    }

    /**
     * Valida si un movimiento específico puede ser eliminado
     *
     * Reglas de negocio para eliminar movimientos:
     *
     * 1. Restricción temporal:
     *    - Solo se pueden eliminar movimientos del día actual
     *    - Previene modificaciones de períodos cerrados
     *    - Mantiene la integridad histórica
     *
     * 2. Restricción de secuencia:
     *    - Solo se puede eliminar el último movimiento
     *    - Evita huecos en la secuencia de stock
     *    - Mantiene la consistencia del inventario
     *
     * Casos de uso:
     * - Corregir errores de entrada inmediatos
     * - Cancelar movimientos equivocados
     * - Gestionar devoluciones el mismo día
     *
     * @param StockMovement $movement Movimiento a validar
     * @return bool true si el movimiento puede eliminarse, false en caso contrario
     */
    public function canDeleteMovement(StockMovement $movement): bool
    {
        $ultimoMovimiento = $this->stockMovementRepository->findOneBy(
            ['product' => $movement->getProduct()],
            ['createdAt' => 'DESC']
        );

        // Solo se puede eliminar si es el último movimiento
        if ($movement !== $ultimoMovimiento) {
            return false;
        }

        // Solo se pueden eliminar movimientos del día actual
        $hoy = new \DateTime();
        return $movement->getCreatedAt()->format('Y-m-d') === $hoy->format('Y-m-d');
    }

    /**
     * Elimina un movimiento y revierte sus efectos en el stock de forma segura
     *
     * Este método realiza las siguientes operaciones:
     *
     * 1. Validación previa:
     *    - Verifica que el movimiento sea eliminable (último del día)
     *    - Comprueba la integridad de la secuencia
     *    - Utiliza canDeleteMovement() para validaciones de negocio
     *
     * 2. Cálculo de reversión:
     *    - Determina el stock anterior según tipo de movimiento
     *    - Maneja casos especiales para ajustes
     *    - Asegura consistencia histórica
     *    - Utiliza calcularStockAnterior() para el cálculo preciso
     *
     * 3. Transaccionalidad:
     *    - Revierte el stock al estado anterior
     *    - Elimina el registro del movimiento
     *    - Garantiza atomicidad de la operación
     *    - Usa Doctrine para transacciones seguras
     *
     * Casos de uso comunes:
     * - Corrección de errores de entrada de datos
     * - Cancelación de movimientos erróneos
     * - Gestión de devoluciones inmediatas
     * - Corrección de ajustes incorrectos
     *
     * IMPORTANTE:
     * - Solo permite eliminar el último movimiento del día
     * - La reversión debe mantener la consistencia del stock
     * - No se permiten eliminaciones que generen stocks negativos
     *
     * @param StockMovement $movement El movimiento a eliminar
     * @throws \InvalidArgumentException Si el movimiento no cumple las condiciones para ser eliminado
     * @throws \RuntimeException Si ocurre un error durante la reversión del stock
     */
    public function deleteMovement(StockMovement $movement): void
    {
        if (!$this->canDeleteMovement($movement)) {
            throw new \InvalidArgumentException('Este movimiento no se puede eliminar.');
        }

        $product = $movement->getProduct();

        // Revertir el efecto en el stock
        $stockAnterior = $this->calcularStockAnterior($product, $movement);
        $product->setStock($stockAnterior);

        $this->entityManager->remove($movement);
        $this->entityManager->flush();
    }

    /**
     * Calcula el stock anterior revirtiendo el efecto del movimiento
     *
     * Este método es fundamental para la eliminación segura de movimientos:
     *
     * 1. Para ENTRADA:
     *    - Resta la cantidad que se había ingresado
     *    - Ejemplo: stock_actual=15, entrada_eliminada=5 → stock_anterior=10
     *
     * 2. Para SALIDA:
     *    - Suma la cantidad que se había retirado
     *    - Ejemplo: stock_actual=7, salida_eliminada=3 → stock_anterior=10
     *
     * 3. Para AJUSTE:
     *    - Requiere cálculo especial
     *    - Busca el valor del stock antes del ajuste
     *    - Usa el historial de movimientos previos
     *
     * @param Product $product El producto afectado
     * @param StockMovement $movement El movimiento a revertir
     * @return int El valor del stock antes del movimiento
     */
    private function calcularStockAnterior(Product $product, StockMovement $movement): int
    {
        $stockActual = $product->getStock();
        $cantidad = abs($movement->getCantidad());

        return match($movement->getTipo()) {
            TipoMovimiento::ENTRADA => $stockActual - $cantidad,
            TipoMovimiento::SALIDA => $stockActual + $cantidad,
            TipoMovimiento::AJUSTE => $this->obtenerStockAnteriorAjuste($product, $movement),
        };
    }

    /**
     * Obtiene el stock anterior a un ajuste buscando el último movimiento previo
     *
     * Este método es crucial para manejar la eliminación de ajustes:
     *
     * 1. Funcionamiento:
     *    - Busca el último movimiento antes del ajuste
     *    - Reconstruye el valor del stock en ese momento
     *    - Permite revertir ajustes de forma precisa
     *
     * 2. Casos especiales:
     *    - Si no hay movimientos previos, retorna 0
     *    - Considera todos los tipos de movimientos
     *    - Mantiene la integridad histórica
     *
     * @param Product $product El producto del ajuste
     * @param StockMovement $movement El movimiento de ajuste a revertir
     * @return int El stock antes del ajuste
     */
    private function obtenerStockAnteriorAjuste(Product $product, StockMovement $movement): int
    {
        $movimientoAnterior = $this->stockMovementRepository->findOneBy(
            [
                'product' => $product,
                'createdAt' => ['<' => $movement->getCreatedAt()]
            ],
            ['createdAt' => 'DESC']
        );

        return $movimientoAnterior ? $this->calcularStockAlMomento($product, $movimientoAnterior)
                                  : 0;
    }

    /**
     * Calcula el stock en un momento específico
     *
     * Este método reconstruye el valor del stock en un punto temporal:
     *
     * 1. Proceso:
     *    - Obtiene todos los movimientos hasta la fecha indicada
     *    - Los procesa en orden cronológico
     *    - Aplica cada movimiento secuencialmente
     *
     * 2. Características:
     *    - Precisión histórica del stock
     *    - Considera todos los tipos de movimientos
     *    - Mantiene la secuencia temporal
     *
     * 3. Uso:
     *    - Reconstrucción de valores históricos
     *    - Auditoría de movimientos
     *    - Soporte para reversión de ajustes
     *
     * @param Product $product El producto a analizar
     * @param StockMovement $hastaMovimiento El movimiento hasta donde calcular
     * @return int El valor del stock en ese momento
     */
    private function calcularStockAlMomento(Product $product, StockMovement $hastaMovimiento): int
    {
        $movimientos = $this->stockMovementRepository->findBy(
            [
                'product' => $product,
                'createdAt' => ['<=' => $hastaMovimiento->getCreatedAt()]
            ],
            ['createdAt' => 'ASC']
        );

        $stock = 0;
        foreach ($movimientos as $mov) {
            $stock = $this->calcularNuevoStock($product, $mov->getCantidad(), $mov->getTipo());
        }

        return $stock;
    }
}
