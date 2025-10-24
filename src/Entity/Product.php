<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entidad Product (Producto)
 * Representa un producto en el catálogo de la empresa.
 *
 * AJUSTES MANUALES REALIZADOS:
 * 1. Añadido #[ORM\HasLifecycleCallbacks] para poder usar eventos PreUpdate
 * 2. SKU marcado como unique=true para evitar duplicados
 * 3. Precisión de precio/costo ajustada a DECIMAL(12,2) para mayor capacidad
 * 4. Relación con Categoria: nullable=false, onDelete='RESTRICT' (no permitir borrar categoría con productos)
 * 5. Relación con User (createBy): nullable=false, onDelete='RESTRICT' (auditoría de quién creó el producto)
 * 6. Corregido typo: updateAt → updatedAt (propiedad y métodos)
 * 7. Añadido constructor con valores por defecto (createdAt, activo=true, stock=0)
 * 8. Añadido callback PreUpdate para actualizar automáticamente updatedAt en cada edición
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * SKU del producto (código único de identificación)
     * AJUSTE MANUAL: añadido unique=true para garantizar que no haya duplicados
     */
    #[ORM\Column(length: 50, unique:true)]
    private ?string $sku = null;

    #[ORM\Column(length: 180)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    /**
     * Precio de venta al público
     * AJUSTE MANUAL: precision aumentada de 10 a 12 para mayor capacidad
     * Nota: DECIMAL se mapea como string en PHP para evitar problemas de precisión con floats
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $precio = null;

    /**
     * Costo de adquisición o fabricación
     * AJUSTE MANUAL: precision aumentada de 10 a 12 para mayor capacidad
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $costo = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column]
    private ?bool $activo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Fecha de última actualización
     * AJUSTE MANUAL: corregido typo de updateAt a updatedAt
     * Se actualiza automáticamente en cada edición gracias al callback PreUpdate
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Categoría del producto (relación obligatoria)
     * AJUSTE MANUAL:
     * - Añadido nullable=false para hacer la relación obligatoria
     * - Añadido onDelete='RESTRICT' para no permitir borrar una categoría que tiene productos asociados
     */
    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Categoria $categoria = null;

    /**
     * Usuario que creó este producto (auditoría)
     * AJUSTE MANUAL:
     * - Añadido nullable=false para hacer la relación obligatoria (siempre debe haber un creador)
     * - Añadido onDelete='RESTRICT' para no permitir borrar un usuario que creó productos
     * - Este campo NO se expone en el formulario; se setea automáticamente en el controlador
     *   con $product->setCreateBy($this->getUser())
     *
     * Nota: El nombre es 'createBy' (no 'createdBy') porque así se generó con make:entity
     * y coincide con el mappedBy='createBy' en User.php
     */
    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?User $createBy = null;

    /**
     * Constructor: establece valores por defecto al crear un producto nuevo
     * AJUSTE MANUAL: añadido para inicializar campos automáticamente
     * - createdAt: fecha/hora actual
     * - activo: true (productos activos por defecto)
     * - stock: 0 (sin stock inicial)
     */
    public function __construct()
    {
        // Valores por defecto al crear un producto
        $this->createdAt = new \DateTimeImmutable();
        $this->activo = true;
        $this->stock = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): static
    {
        $this->sku = $sku;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

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

    public function getPrecio(): ?string
    {
        return $this->precio;
    }

    public function setPrecio(string $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getCosto(): ?string
    {
        return $this->costo;
    }

    public function setCosto(string $costo): static
    {
        $this->costo = $costo;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Getter para updatedAt
     * AJUSTE MANUAL: renombrado de getUpdateAt a getUpdatedAt (corrección de typo)
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter para updatedAt
     * AJUSTE MANUAL: renombrado de setUpdateAt a setUpdatedAt (corrección de typo)
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Lifecycle callback: se ejecuta automáticamente antes de actualizar el producto en la BD
     * AJUSTE MANUAL: añadido para actualizar automáticamente updatedAt en cada edición
     * Requiere que la clase tenga el atributo #[ORM\HasLifecycleCallbacks]
     */
    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Obtiene el usuario que creó este producto
     * Nota: útil para mostrar quién registró el producto en listados y vistas de detalle
     */
    public function getCreateBy(): ?User
    {
        return $this->createBy;
    }

    /**
     * Establece el usuario que creó este producto
     * IMPORTANTE: Este campo NO debe estar en el formulario de producto
     * Se setea automáticamente en el controlador al crear:
     *   $product->setCreateBy($this->getUser());
     */
    public function setCreateBy(?User $createBy): static
    {
        $this->createBy = $createBy;

        return $this;
    }
}
