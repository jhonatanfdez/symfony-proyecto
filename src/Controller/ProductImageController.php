<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductImageType;
use App\Security\AdminAccessGuard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/product')]
final class ProductImageController extends AbstractController
{
    public function __construct(
        private readonly AdminAccessGuard $guard
    ) {}

    // Sube múltiples imágenes para un producto
    #[Route('/{id}/images', name: 'app_product_upload_images', methods: ['POST'])]
    public function uploadImages(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        // Carpeta absoluta donde guardaremos los archivos
        // Recomendado: agrega este parámetro en config/services.yaml:
        // parameters:
        //     uploads_products_dir: '%kernel.project_dir%/public/uploads/products'
        $uploadDir = (string) ($this->getParameter('uploads_products_dir')
            ?? ($this->getParameter('kernel.project_dir') . '/public/uploads/products'));

        // Procesamos el formulario de archivos
        $form = $this->createForm(ProductImageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $files */
            $files = (array) $form->get('files')->getData();

            // Obtener posición máxima actual para continuar el orden
            $maxPosition = -1;
            foreach ($product->getProductImages() as $img) {
                $pos = $img->getPosition();
                if ($pos !== null && $pos > $maxPosition) {
                    $maxPosition = $pos;
                }
            }

            $count = 0;
            foreach ($files as $file) {
                $originalName = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';

                $uniqueName = sprintf(
                    'producto-%d-%d-%s.%s',
                    $product->getId(),
                    time(),
                    bin2hex(random_bytes(4)),
                    $ext
                );

                // Mover archivo al directorio de uploads
                $file->move($uploadDir, $uniqueName);

                // Crear entidad ProductImage
                $image = new ProductImage();
                $image->setProduct($product);
                $image->setImageName($originalName . '.' . $ext);
                $image->setImagePath('uploads/products/' . $uniqueName); // relativo a public/
                $image->setCreatedAt(new \DateTimeImmutable());
                $image->setPosition(++$maxPosition);

                $em->persist($image);
                $count++;
            }

            $em->flush();

            $this->addFlash('success', sprintf('Se subieron %d imagen(es) correctamente.', $count));
            return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
        }

        // Si el formulario es inválido, recogemos y mostramos errores específicos.
        // Esto incluye mensajes de:
        // - Límite de cantidad (máx 10 imágenes)
        // - Tipo de archivo no permitido (solo JPG/PNG/WEBP)
        // - Tamaño excedido por imagen
        // - Token CSRF inválido (si aplica)
        if ($form->isSubmitted() && !$form->isValid()) {
            $shown = [];
            foreach ($form->getErrors(true, true) as $error) {
                $message = trim((string) $error->getMessage());
                if ($message === '') { continue; }
                // Evitar duplicados exactos
                if (in_array($message, $shown, true)) { continue; }
                $shown[] = $message;
                $this->addFlash('error', $message);
            }
        } else {
            // Formulario no enviado correctamente (edge case)
            $this->addFlash('error', 'No se pudo procesar la solicitud de subida. Intenta nuevamente.');
        }
        return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
    }

    // Elimina una imagen individual (registro + archivo físico)
    #[Route('/image/{id}/delete', name: 'app_product_image_delete', methods: ['POST'])]
    public function deleteImage(Request $request, ProductImage $image, EntityManagerInterface $em): Response
    {
        if ($redirect = $this->guard->maybeRedirect($request, $this->getUser())) {
            return $redirect;
        }

        $token = $request->getPayload()->getString('_token');
        if (!$this->isCsrfTokenValid('delete_image' . $image->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('app_product_edit', ['id' => $image->getProduct()->getId()]);
        }

        $publicPath = $this->getParameter('kernel.project_dir') . '/public/';
        $absoluteFile = $publicPath . ltrim((string) $image->getImagePath(), '/');

        $productId = $image->getProduct()->getId();

        $em->remove($image);
        $em->flush();

        if (is_file($absoluteFile)) {
            @unlink($absoluteFile);
        }

        $this->addFlash('success', 'Imagen eliminada exitosamente.');
        return $this->redirectToRoute('app_product_edit', ['id' => $productId]);
    }
}
