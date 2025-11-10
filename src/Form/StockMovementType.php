<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\StockMovement;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\TipoMovimiento;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Formulario para la gestión de movimientos de stock
 *
 * FUNCIONAMIENTO:
 * - Para CREACIÓN: Muestra solo campos editables (producto, tipo, cantidad, descripción)
 * - Para EDICIÓN: Además muestra fecha y usuario creador como campos deshabilitados (solo lectura)
 *
 * CAMPOS EDITABLES:
 * - cantidad: IntegerType, número positivo
 * - tipo: ChoiceType con opciones ENTRADA/SALIDA/AJUSTE
 * - descripcion: TextareaType opcional
 * - product: EntityType, muestra nombre del producto
 *
 * CAMPOS DE SOLO LECTURA (solo en edición):
 * - fecha: Texto deshabilitado, muestra cuándo se creó el movimiento
 * - createBy: Texto deshabilitado, muestra quién creó el movimiento
 *
 * VALIDACIONES:
 * - Cantidad: Número entero positivo
 * - Producto: Selección obligatoria
 * - Tipo: Valor válido del enum TipoMovimiento
 *
 * @see StockMovementController para ver cómo se usa con 'is_edit' option
 */
class StockMovementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Obtener la opción 'is_edit' (por defecto false)
        $isEdit = $options['is_edit'] ?? false;

        // Campos siempre visibles y editables
        $builder
            ->add('cantidad', IntegerType::class, [
                'label' => 'Cantidad',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Ingrese la cantidad'
                ],
                'help' => 'Cantidad de unidades para el movimiento'
            ])
            ->add('tipo', ChoiceType::class, [
                'label' => 'Tipo de Movimiento',
                'choices' => [
                    'Entrada' => TipoMovimiento::ENTRADA,
                    'Salida' => TipoMovimiento::SALIDA,
                    'Ajuste' => TipoMovimiento::AJUSTE
                ],
                'attr' => ['class' => 'form-select'],
                'help' => 'Seleccione el tipo de movimiento'
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Ingrese una descripción del movimiento'
                ],
                'help' => 'Opcional: Detalles adicionales del movimiento'
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'nombre',
                'label' => 'Producto',
                'placeholder' => 'Seleccione un producto',
                'attr' => ['class' => 'form-select'],
                'help' => 'Producto afectado por el movimiento'
            ])
        ;

        // Si es edición, añadir campos de solo lectura (deshabilitados)
        if ($isEdit) {
            $stockMovement = $builder->getData();

            $builder
                ->add('fecha', TextType::class, [
                    'label' => 'Fecha del Movimiento',
                    'disabled' => true,  // ← Deshabilitado, no se puede editar
                    'data' => $stockMovement?->getFecha()?->format('d/m/Y H:i') ?? '',
                    'attr' => [
                        'class' => 'form-control',
                        'readonly' => 'readonly'  // ← También readonly en HTML
                    ],
                    'help' => 'Fecha de creación del movimiento (no editable)'
                ])
                ->add('createBy', TextType::class, [
                    'label' => 'Usuario que Creó',
                    'disabled' => true,  // ← Deshabilitado, no se puede editar
                    'data' => $stockMovement?->getCreateBy()?->getName() ?? $stockMovement?->getCreateBy()?->getEmail() ?? '',
                    'attr' => [
                        'class' => 'form-control',
                        'readonly' => 'readonly'  // ← También readonly en HTML
                    ],
                    'help' => 'Usuario que originalmente creó el movimiento (no editable)'
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockMovement::class,
            'is_edit' => false,  // ← Opción nueva: false para creación, true para edición
        ]);
    }
}
