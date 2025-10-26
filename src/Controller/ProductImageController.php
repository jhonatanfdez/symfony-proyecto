<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductImageType;
use App\Security\AdminAccessGuard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para la gestión de imágenes de productos.
 *
 * Endpoints:
 * - POST /admin/product/{id}/images        → Subir múltiples imágenes
 * - POST /admin/product/image/{id}/delete  → Eliminar una imagen específica
 *
 * Comportamiento ante errores:
 * - Recolecta y muestra errores específicos del formulario (límite de cantidad, tipo/tamaño, CSRF).
 */
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

        // Procesamos el formulario de archivos
        $form = $this->createForm(ProductImageType::class);
        $form->handleRequest($request);

        // Si el formulario es inválido (incluye error CSRF), mostramos mensajes específicos
        if (!$form->isSubmitted() || !$form->isValid()) {
            $shown = [];
            foreach ($form->getErrors(true, true) as $error) {
                $message = trim((string) $error->getMessage());
                if ($message === '' || in_array($message, $shown, true)) { continue; }
                $shown[] = $message;
                $this->addFlash('error', $message);
            }
            if (!$form->isSubmitted()) {
                $this->addFlash('error', 'No se pudo procesar la solicitud de subida. Intenta nuevamente.');
            }
            return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $files */
        $files = (array) $form->get('files')->getData();
        if (count($files) === 0) {
            $this->addFlash('info', 'No seleccionaste ningún archivo para subir.');
            return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
        }

        // Directorio absoluto de uploads (configurado en services.yaml)
        $publicDir = (string) ($this->getParameter('kernel.project_dir') . '/public');
        $relativeDir = 'uploads/products';
        $absoluteDir = $publicDir . '/' . $relativeDir;
        if (!is_dir($absoluteDir)) {
            @mkdir($absoluteDir, 0775, true);
        }

        // Calcular posición inicial (si se usa orden)
        $maxPosition = -1;
        foreach ($product->getProductImages() as $img) {
            $pos = $img->getPosition();
            if ($pos !== null && $pos > $maxPosition) {
                $maxPosition = $pos;
            }
        }

        $saved = 0;
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $ext = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
            $uniqueName = sprintf('producto-%d-%s.%s', $product->getId(), bin2hex(random_bytes(6)), $ext);

            try {
                $file->move($absoluteDir, $uniqueName);
            } catch (FileException) {
                $this->addFlash('error', 'No se pudo guardar el archivo "' . $originalName . '".');
                continue;
            }

            $image = new ProductImage();
            $image->setProduct($product);
            $image->setImageName($originalName);
            $image->setImagePath($relativeDir . '/' . $uniqueName); // relativo a public/
            $image->setCreatedAt(new \DateTimeImmutable());
            $image->setPosition(++$maxPosition);

            $em->persist($image);
            $saved++;
        }

        $em->flush();

        if ($saved > 0) {
            $this->addFlash('success', sprintf('Se subieron %d imagen(es) correctamente.', $saved));
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

        $token = (string) $request->getPayload()->get('_token');
        if (!$this->isCsrfTokenValid('delete_image' . $image->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF inválido. Por favor, recarga la página e inténtalo de nuevo.');
            return $this->redirectToRoute('app_product_edit', ['id' => $image->getProduct()->getId()]);
        }

        $productId = $image->getProduct()->getId();

        // Ruta absoluta del archivo físico
        $publicDir = (string) ($this->getParameter('kernel.project_dir') . '/public');
        $absoluteFile = $publicDir . '/' . ltrim((string) $image->getImagePath(), '/');

        // Eliminar registro en BD primero
        $em->remove($image);
        $em->flush();

        // Luego intentamos borrar el archivo (si existe)
        if (is_file($absoluteFile)) {
            @unlink($absoluteFile);
        }

        $this->addFlash('success', 'Imagen eliminada correctamente.');
        return $this->redirectToRoute('app_product_edit', ['id' => $productId]);
    }
}
