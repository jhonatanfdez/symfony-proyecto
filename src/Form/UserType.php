<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            //->add('roles')

            //aqui esta la mejora
            ->add('roles', ChoiceType::class, [
                'label' => 'Rol',
                'choices' => [
                    'Usuario' => 'ROLE_USER',
                    'Administrador' => 'ROLE_ADMIN',
                ],
                'multiple' => false,  // Solo permite seleccionar un rol
                'expanded' => true,   // Muestra como radio buttons en lugar de checkboxes
                'mapped' => false,    // No mapear directamente a la entidad
                'data' => $options['data']->getRoles()[0] ?? 'ROLE_USER', // Selecciona el primer rol del array
                'disabled' => $options['disable_roles'] ?? false, // Deshabilitar si viene la opción
            ])
            // antes
            //->add('password')

            //ahora
            ->add('password', PasswordType::class, [
                'label' => 'Contraseña',
                'required' => false,      // Opcional en edición (no obligar a cambiarla)
                'mapped' => false,        // No mapear directamente a la entidad
                'data' => '',             // Forzar que el campo se renderice vacío
                'attr' => [               // Sugerencias al navegador para evitar autofill
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Deja en blanco para no cambiar',
                ],
            ])
            ->add('name')
            ->add('fecha_cumpleanos')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'disable_roles' => false, // Por defecto, roles están habilitados
        ]);
    }
}
