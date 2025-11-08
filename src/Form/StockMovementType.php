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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Formulario para la gestión de movimientos de stock
 *
 * Este formulario maneja la creación y edición de movimientos de inventario con:
 * - Selección de producto usando el nombre en lugar del ID
 * - Tipo de movimiento (ENTRADA/SALIDA/AJUSTE)
 * - Cantidad con validación de números positivos
 * - Campo de descripción opcional para detalles
 * - Fecha del movimiento
 *
 * VALIDACIONES:
 * - Cantidad: Número entero positivo
 * - Producto: Selección obligatoria
 * - Tipo: Valor del enum TipoMovimiento
 */
class StockMovementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad',  IntegerType::class,
                [
                    'label' => 'Cantidad',
                    'attr' => [
                        'class' => 'form-control',
                        'min' => 0,
                        'placeholder' => 'Ingrese la cantidad'
                    ],
                    'help' => 'Cantidad de unidades para el movimiento'
                ]
                )
            ->add('tipo',
                ChoiceType::class,
                [
                    'label' => 'Tipo de Movimiento',
                    'choices' => [
                        'Entrada' => TipoMovimiento::ENTRADA,
                        'Salida' => TipoMovimiento::SALIDA,
                        'Ajuste' => TipoMovimiento::AJUSTE
                    ],
                    'attr' => ['class' => 'form-select'],
                    'help' => 'Seleccione el tipo de movimiento'
                ]
            )
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
            ->add('fecha', null, [
                'widget' => 'single_text',
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'nombre',
                'label' => 'Producto',
                'placeholder' => 'Seleccione un producto',
                'attr' => ['class' => 'form-select'],
                'help' => 'Producto afectado por el movimiento'
            ])
            ->add('createBy', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockMovement::class,
        ]);
    }
}
