<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Geschwister;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Repository\StammdatenRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class UploadController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/login/upload/{id}/file', name: 'upload_stadt', methods: ['POST'])]
    public function index(Request $request, UploadService $uploadService, #[MapEntity(mapping: ['id' => 'id'])] Stadt $stadt, EntityManagerInterface $entityManager)
    {
        set_time_limit(300);

        if ($this->getUser()->getStadt() !== $stadt && !$this->getUser()->hasRole('ROLE_ADMIN')) {
            throw new NotFoundHttpException('Idea not found');
        }
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $file = $uploadService->uploadFile($uploadedFile);
        $file->setStadt($stadt);
        $entityManager->persist($file);
        $entityManager->flush();

        return $file ? new JsonResponse(['error' => 0]) : new JsonResponse(['error' => 1]);
    }

    #[Route(path: '/upload/kind/{uid}/file', name: 'upload_kind', methods: ['POST'])]
    public function geschwister(Request $request, UploadService $uploadService, #[MapEntity(mapping: ['uid' => 'uid'])] Geschwister $geschwister, EntityManagerInterface $entityManager)
    {
        set_time_limit(300);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $file = $uploadService->uploadFile($uploadedFile);
        $geschwister->addFile($file);
        $entityManager->persist($geschwister);
        $entityManager->flush();

        return $file ? new JsonResponse(['error' => 0]) : new JsonResponse(['error' => 1]);
    }

    #[Route(path: '/download/{fileName}', name: 'login_download_file', methods: ['GET'])]
    public function downloadArticleReference(#[MapEntity(mapping: ['fileName' => 'fileName'])] File $file, FilesystemOperator $internFileSystem)
    {
        if (!$file) {
            throw new NotFoundHttpException('File not found');
        }

        $stream = $internFileSystem->read($file->getFileName());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $file->getType());
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x2D\x2F\x3A-\x40\x5B-\x60\x7B-\xFF]/', '', (string) $file->getOriginalName())
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route(path: '/removeFile/{fileName}', name: 'login_remove_file', methods: ['GET'])]
    public function removeFile(#[MapEntity(mapping: ['fileName' => 'fileName'])] File $file, FilesystemOperator $internFileSystem, Request $request)
    {
        $internFileSystem->delete($file->getFileName());
        $em = $this->managerRegistry->getManager();
        $em->remove($file);
        $em->flush();

        return $this->redirect(
            $request
                ->headers
                ->get('referer')
        );
    }

    #[Route(path: '/upload/additional/{id}/file', name: 'upload_additional-documents', methods: ['POST'])]
    public function additionalDocuments(
        Request $request,
        UploadService $uploadService,
        #[MapEntity(mapping: ['id' => 'id'])]
        Stammdaten $stammdaten,
        EntityManagerInterface $entityManager,
        StammdatenRepository $stammdatenRepository,
    ): JsonResponse {
        set_time_limit(300);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $file = $uploadService->uploadFile($uploadedFile, '10M');
        if (!$file) {
            return new JsonResponse(['error' => 'Maximal 10 MB und übliche Dateiformate erlaubt.'], Response::HTTP_BAD_REQUEST);
        }
        // if we have a working copy, also write the file to the latest proper stammdaten since they are not subject to confirmation or abschluss workflow
        if ($stammdaten->getCreatedAt() === null) {
            $actualStammdaten = $stammdatenRepository->findlatestStammdatenfromStammdaten($stammdaten);
            if ($actualStammdaten) {
                $actualStammdaten->addFile($file);
                $entityManager->persist($actualStammdaten);
            }
        }
        $stammdaten->addFile($file);
        $entityManager->persist($stammdaten);
        $entityManager->flush();

        return new JsonResponse([], Response::HTTP_OK);
    }
}
