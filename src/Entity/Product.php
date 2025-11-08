<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


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

    #[Assert\NotBlank(message: 'El SKU es obligatorio')]
    #[Assert\Length(max: 50, maxMessage: 'El SKU no puede tener más de {{ limit }} caracteres')]
    #[ORM\Column(length: 50, unique:true)]
    private ?string $sku = null;

    #[Assert\NotBlank(message: 'El nombre del producto es obligatorio')]
    #[Assert\Length(max: 180, maxMessage: 'El nombre no puede tener más de {{ limit }} caracteres')]
    #[ORM\Column(length: 180)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    /**
     * Precio de venta al público
     * AJUSTE MANUAL: precision aumentada de 10 a 12 para mayor capacidad
     * Nota: DECIMAL se mapea como string en PHP para evitar problemas de precisión con floats
     *
     * VALIDACIONES APLICADAS:
     * - @Assert\NotBlank: Campo obligatorio, no puede estar vacío
     * - @Assert\GreaterThanOrEqual(0): No permite valores negativos (servidor)
     * - @Assert\Regex: Valida formato decimal con máximo 2 decimales (ej: 99.99, 1000, 15.5)
     * - HTML5 validations en el formulario: min="0" step="0.01" (navegador)
     */
    #[Assert\NotBlank(message: 'El precio es obligatorio')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'El precio no puede ser un número negativo')]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: 'El precio debe tener como máximo dos decimales'
    )]
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $precio = null;

    /**
     * Costo de adquisición o fabricación
     * AJUSTE MANUAL: precision aumentada de 10 a 12 para mayor capacidad
     *
     * VALIDACIONES APLICADAS:
     * - @Assert\NotBlank: Campo obligatorio, no puede estar vacío
     * - @Assert\GreaterThanOrEqual(0): No permite valores negativos (servidor)
     * - @Assert\Regex: Valida formato decimal con máximo 2 decimales (ej: 50.25, 100, 7.5)
     * - HTML5 validations en el formulario: min="0" step="0.01" (navegador)
     *
     * NOTA: El costo es información interna y no debería mostrarse al público.
     * Se usa para calcular margen de ganancia: (precio - costo) / precio * 100
     */
    #[Assert\NotBlank(message: 'El costo es obligatorio')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'El costo no puede ser un número negativo')]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: 'El costo debe tener como máximo dos decimales'
    )]
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $costo = null;

    /**
     * Cantidad disponible en inventario
     *
     * VALIDACIONES APLICADAS:
     * - @Assert\NotBlank: Campo obligatorio
     * - @Assert\Type('integer'): Debe ser un número entero (no permite decimales)
     * - @Assert\GreaterThanOrEqual(0): No permite stock negativo
     * - HTML5 validations en el formulario: type="number" min="0" (navegador)
     *
     * NOTA: El constructor inicializa stock=0 por defecto para nuevos productos.
     * Considera implementar un sistema de alertas cuando stock < umbral_minimo
     */
    #[Assert\NotBlank(message: 'El stock es obligatorio')]
    #[Assert\Type(
        type: 'integer',
        message: 'El stock debe ser un número entero'
    )]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'El stock no puede ser un número negativo')]
    #[ORM\Column]
    private ?int $stock = null;

    /**
     * Estado del producto (activo/inactivo)
     *
     * PROPÓSITO:
     * - true: Producto visible y disponible para la venta
     * - false: Producto oculto sin eliminarlo de la BD (soft delete lógico)
     *
     * VENTAJAS vs eliminar:
     * - Conserva historial de ventas y referencias
     * - Permite reactivar productos temporalmente agotados
     * - Mantiene integridad referencial con otras tablas
     *
     * NOTA: El constructor inicializa activo=true por defecto
     */
    #[ORM\Column]
    private ?bool $activo = null;

    /**
     * Fecha y hora de creación del producto (auditoría)
     *
     * CARACTERÍSTICAS:
     * - Se setea automáticamente en el constructor con la fecha/hora actual
     * - Usa DateTimeImmutable (inmutable) para evitar modificaciones accidentales
     * - NO debe estar en el formulario de producto (es automático)
     * - Útil para reportes: productos creados por fecha, análisis temporal, etc.
     */
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

    #[Assert\NotNull(message: 'Debe seleccionar una categoría')]
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
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $productImages;

    /**
     * @var Collection<int, StockMovement>
     */
    #[ORM\OneToMany(targetEntity: StockMovement::class, mappedBy: 'product')]
    private Collection $stockMovements;

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
        $this->productImages = new ArrayCollection();
        $this->stockMovements = new ArrayCollection();
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

    /**
     * @return Collection<int, ProductImage>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            // set the owning side to null (unless already changed)
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function getStockMovements(): Collection
    {
        return $this->stockMovements;
    }

    public function addStockMovement(StockMovement $stockMovement): static
    {
        if (!$this->stockMovements->contains($stockMovement)) {
            $this->stockMovements->add($stockMovement);
            $stockMovement->setProduct($this);
        }

        return $this;
    }

    public function removeStockMovement(StockMovement $stockMovement): static
    {
        if ($this->stockMovements->removeElement($stockMovement)) {
            // set the owning side to null (unless already changed)
            if ($stockMovement->getProduct() === $this) {
                $stockMovement->setProduct(null);
            }
        }

        return $this;
    }
}
