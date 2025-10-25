<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Categoria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Formulario para crear y editar productos
 *
 * CAMPOS INCLUIDOS:
 * - sku: código único (validado con unique=true en entidad)
 * - nombre: nombre del producto
 * - descripcion: descripción detallada (opcional)
 * - precio: precio de venta con validación min=0 y 2 decimales
 * - costo: costo de adquisición con validación min=0 y 2 decimales
 * - stock: cantidad en inventario con validación min=0
 * - activo: estado del producto (activo/inactivo)
 * - categoria: relación obligatoria con Categoria
 *
 * CAMPOS EXCLUIDOS INTENCIONALMENTE:
 * - createdAt: se setea automáticamente en el constructor de Product
 * - updatedAt: se actualiza automáticamente con el callback PreUpdate
 * - createBy: se setea en el controlador con $this->getUser(), NO debe ser editable por el usuario
 *
 * DOBLE CAPA DE VALIDACIÓN (Seguridad):
 * 1. HTML5 (navegador): Validaciones básicas con atributos min, step, required
 *    - Mejora UX con feedback inmediato
 *    - PUEDE SER BYPASSEADA por usuarios maliciosos (deshabilitar JS, editar DOM)
 *
 * 2. Server-Side (PHP): Constraints @Assert en la entidad Product.php
 *    - Validación DEFINITIVA que no puede evitarse
 *    - Protege contra envíos directos por API o herramientas como cURL/Postman
 *    - NUNCA confiar SOLO en validación del cliente
 *
 * FLUJO DE VALIDACIÓN:
 * Usuario llena formulario → HTML5 valida → Envía a servidor → Symfony valida constraints
 * Si falla cualquier validación del servidor, se rechazan los datos incluso si pasaron HTML5
 */
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // SKU: Código único de identificación del producto
            // La unicidad se valida en la entidad con unique=true
            // empty_data: convierte null a string vacío para que las validaciones funcionen
            ->add('sku', TextType::class, [
                'label' => 'SKU',
                'help' => 'Codigo unico de identificación del producto',
                'empty_data' => '',
            ])

            // Nombre del producto (campo obligatorio)
            ->add('nombre', TextType::class, [
                'label' => 'Nombre del producto',
                'empty_data' => '',
            ])

            // Descripción detallada (campo opcional)
            // rows=4 controla la altura del textarea
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
            ])

            // Precio de venta al público
            // NumberType: Maneja números decimales con precisión
            // scale=2: Permite hasta 2 decimales (centavos)
            // html5 comentado: Si se activa, genera <input type="number"> con validaciones HTML5
            //                  Si está desactivado, genera <input type="text"> (más flexible)
            //
            // ATRIBUTOS HTML:
            // - min="0": Previene precios negativos en navegador (validación HTML5)
            // - step="0.01": Permite incrementos de centavos (0.01, 0.02, etc.)
            // - placeholder: Muestra formato esperado cuando el campo está vacío
            //
            // VALIDACIÓN SERVIDOR: Ver constraints en Product::$precio
            ->add('precio', NumberType::class, [
                'label' => 'Precio de venta',
                'scale' => 2,
                //'html5' => true,  // Descomentarar para usar <input type="number">
                'attr' => [
                    'min' => 0,
                    'step' => '0.01',
                    'placeholder' => '0.00',
                ],
                'help' => 'Precio de venta al público en formato decimal con dos decimales',
            ])

            // Costo de adquisición o producción
            // Mismo comportamiento que precio
            // IMPORTANTE: Este campo es información INTERNA (no mostrar al público)
            // Se usa para calcular margen de ganancia en reportes administrativos
            //
            // VALIDACIÓN SERVIDOR: Ver constraints en Product::$costo
            ->add('costo', NumberType::class, [
                'label' => 'Costo',
                'scale' => 2,
                //'html5' => true,  // Descomentarar para usar <input type="number">
                'attr' => [
                    'min' => 0,
                    'step' => '0.01',
                    'placeholder' => '0.00',
                ],
                'help' => 'Costo de adquisición o producción en formato decimal con dos decimales',
            ])

            // Stock disponible en inventario
            // IntegerType: Solo acepta números enteros (no permite decimales como 2.5 unidades)
            // html5 comentado: Si se activa, genera <input type="number"> sin decimales
            // min="0": Previene stock negativo en navegador
            // invalid_message: mensaje cuando se ingresa texto en lugar de número
            //
            // NOTA: El constructor de Product inicializa stock=0 por defecto
            // VALIDACIÓN SERVIDOR: Ver constraints en Product::$stock
            // SUGERENCIA FUTURA: Agregar alertas cuando stock < umbral_minimo (ej: stock < 5)
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                //'html5' => true,  // Descomentarar para usar <input type="number">
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0',
                ],
                'help' => 'Cantidad disponible en inventario',
                'invalid_message' => 'El stock debe ser un número entero válido',
            ])

            // Estado del producto: activo o inactivo
            // required=false permite desmarcar el checkbox
            // Productos inactivos pueden ocultarse sin eliminarlos de la BD
            ->add('activo', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'help' => 'Desmarcar para ocultar el producto en la tienda sin eliminarlo',
            ])

            // Categoría del producto (relación ManyToOne obligatoria)
            // choice_label='nombre' muestra el nombre de la categoría en el select
            // placeholder obliga a seleccionar una opción explícitamente
            // required=true hace la selección obligatoria
            ->add('categoria', EntityType::class, [
                'class' => Categoria::class,
                'choice_label' => 'nombre',
                'placeholder' => '-- Selecciona una categoría --',
                'label' => 'Categoría',
                'required' => true,
                'attr' => [
                    'class' => 'categoria-select',
                ],
                'help' => 'Categoría a la que pertenece el producto',
            ])

            // NOTA IMPORTANTE: createBy NO está en este formulario
            // Se setea automáticamente en ProductController::new() con:
            // $product->setCreateBy($this->getUser())
            // Esto garantiza auditoría de quién creó cada producto
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
