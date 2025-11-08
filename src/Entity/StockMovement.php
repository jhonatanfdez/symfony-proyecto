<?php

namespace App\Entity;

use App\Repository\StockMovementRepository;
use App\Enum\TipoMovimiento;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entidad StockMovement (Movimiento de Stock)
 * Registra todos los movimientos de inventario: entradas, salidas y ajustes.
 *
 * VALIDACIONES:
 * 1. cantidad: No nulo, entero, obligatorio
 * 2. tipo: Enum TipoMovimiento (ENTRADA, SALIDA, AJUSTE)
 * 3. fecha: Automática en constructor
 * 4. product: Relación obligatoria con producto
 * 5. createBy: Usuario que realiza el movimiento (obligatorio)
 */
#[ORM\Entity(repositoryClass: StockMovementRepository::class)]
class StockMovement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Cantidad de unidades del movimiento
     * - Positivo para entradas
     * - Negativo para salidas (se convierte automáticamente)
     */
    #[Assert\NotNull(message: 'La cantidad es obligatoria')]
    #[Assert\NotBlank(message: 'La cantidad es obligatoria')]
    #[Assert\Type(type: 'integer', message: 'La cantidad debe ser un número entero')]
    #[Assert\GreaterThan(value: 0, message: 'La cantidad debe ser mayor a 0')]
    #[ORM\Column]
    private ?int $cantidad = null;

    /**
     * Tipo de movimiento (ENTRADA, SALIDA, AJUSTE)
     * Se valida contra el enum TipoMovimiento
     */
    #[Assert\NotBlank(message: 'El tipo de movimiento es obligatorio')]
    #[Assert\Choice(callback: [TipoMovimiento::class, 'cases'], message: 'Tipo de movimiento inválido')]
    #[ORM\Column(length: 20)]
    private ?string $tipo = null;

    /**
     * Descripción opcional del movimiento
     * Ejemplo: "Compra inicial", "Venta #123", "Ajuste por inventario"
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    /**
     * Fecha y hora del movimiento
     * Se establece automáticamente en el constructor
     */
    #[Assert\NotNull(message: 'La fecha es obligatoria')]
    #[ORM\Column]
    private ?\DateTimeImmutable $fecha = null;

    /**
     * Producto afectado por el movimiento
     * Relación obligatoria (nullable: false)
     */
    #[Assert\NotNull(message: 'El producto es obligatorio')]
    #[ORM\ManyToOne(inversedBy: 'stockMovements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    /**
     * Usuario que realizó el movimiento
     * Relación obligatoria para auditoría
     */
    #[Assert\NotNull(message: 'El usuario es obligatorio')]
    #[ORM\ManyToOne(inversedBy: 'stockMovements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createBy = null;

    public function __construct()
    {
        $this->fecha = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFecha(): ?\DateTimeImmutable
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeImmutable $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getCreateBy(): ?User
    {
        return $this->createBy;
    }

    public function setCreateBy(?User $createBy): static
    {
        $this->createBy = $createBy;

        return $this;
    }
}
