<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploadController extends AbstractController
{
    #[Route('/admin/upload-image', name: 'admin_upload_image', methods: ['POST'])]
    public function upload(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], 400);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/activities',
                $newFilename
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to save file'], 500);
        }

        // getBasePath() prevents 404 errors if the app is hosted in a sub-directory
        $basePath = $request->getBasePath();
        
        return new JsonResponse([
            'location' => $basePath . '/uploads/activities/' . $newFilename
        ]);
    }
}