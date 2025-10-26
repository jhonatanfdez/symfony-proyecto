<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulario para subir múltiples imágenes a un producto
 *
 * PROPÓSITO:
 * Este formulario NO está vinculado directamente a la entidad ProductImage.
 * Su única función es recibir múltiples archivos de imagen desde el navegador
 * y validarlos antes de procesarlos en el controlador.
 *
 * FUNCIONAMIENTO:
 * 1. El usuario selecciona una o más imágenes desde su dispositivo
 * 2. Las validaciones se ejecutan en el servidor (tipo, tamaño, cantidad)
 * 3. El controlador procesa cada archivo:
 *    - Genera un nombre único para evitar colisiones
 *    - Mueve el archivo a public/uploads/products/
 *    - Crea una entidad ProductImage por cada archivo
 *    - Asocia la imagen al producto correspondiente
 *
 * SEGURIDAD:
 * - Solo acepta JPEG, PNG y WEBP (validación server-side con Assert\Image)
 * - Máximo 5MB por imagen individual
 * - Máximo 10 imágenes en una sola carga
 * - El atributo 'accept' del input mejora UX pero NO es seguro (se valida en servidor)
 */
class ProductImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('files', FileType::class, [
                'label' => 'Imágenes del producto',

                // NO mapear a la entidad: procesamos manualmente en el controlador
                'mapped' => false,

                // Permitir seleccionar múltiples archivos a la vez
                'multiple' => true,

                // Hacer el campo opcional (útil si solo queremos añadir imágenes a un producto existente)
                'required' => false,

                // Atributos HTML para mejorar la experiencia del usuario
                'attr' => [
                    'accept' => 'image/*',  // Sugerencia al navegador: solo mostrar imágenes en el selector
                    'class' => 'form-control', // Clase Bootstrap para estilo consistente
                ],

                // Texto de ayuda que se muestra debajo del campo
                'help' => 'Selecciona hasta 10 imágenes (JPG, PNG o WEBP). Máximo 5MB por imagen.',

                // VALIDACIONES SERVER-SIDE (las definitivas e infranqueables)
                'constraints' => [
                    // Validar que no se suban más de 10 imágenes de una sola vez
                    new Assert\Count([
                        'max' => 10,
                        'maxMessage' => 'No puedes subir más de {{ limit }} imágenes a la vez. Seleccionaste {{ count }}.',
                    ]),

                    // Assert\All aplica las validaciones a cada archivo individualmente
                    new Assert\All([
                        // Validar que cada archivo sea una imagen válida
                        new Assert\Image([
                            // Tamaño máximo: 10 megabytes por imagen
                            'maxSize' => '10M',
                            'maxSizeMessage' => 'La imagen es demasiado grande ({{ size }} {{ suffix }}). El tamaño máximo permitido es {{ limit }} {{ suffix }}.',

                            // MIME types permitidos (validación a nivel de archivo, no extensión)
                            // Esto protege contra archivos renombrados maliciosamente (ej: virus.exe → virus.jpg)
                            'mimeTypes' => [
                                'image/jpeg',  // .jpg, .jpeg
                                'image/png',   // .png
                                'image/webp',  // .webp (formato moderno, menor peso)
                            ],
                            'mimeTypesMessage' => 'Solo se permiten imágenes en formato JPG, PNG o WEBP. El archivo que intentaste subir es: {{ type }}.',

                            // Validaciones adicionales opcionales (descomentarlas si las necesitas)
                            // 'minWidth' => 200,
                            // 'maxWidth' => 4000,
                            // 'minHeight' => 200,
                            // 'maxHeight' => 4000,
                        ]),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // NO vincular este formulario a ninguna entidad (data_class = null)
            // Razón: recibimos múltiples archivos y creamos múltiples entidades ProductImage en el controlador
            // Si vinculáramos a ProductImage, solo podríamos crear una a la vez
            'data_class' => null,
        ]);
    }
}
