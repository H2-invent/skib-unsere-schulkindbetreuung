<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Service\UploadService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Util\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class UploadController extends AbstractController
{
    /**
     * @Route("/login/upload/{id}/file", name="upload_stadt",methods={"POST"})
     * @ParamConverter ("stadt", options={"mapping": {"id": "id"}})
     */
    public function index(Request $request, UploadService $uploadService, Stadt $stadt, EntityManagerInterface $entityManager)
    {
        set_time_limit(300);


        if ($this->getUser()->getStadt() !== $stadt && !$this->getUser()->hasRole('ROLE_ADMIN')) {
            throw new NotFoundHttpException("Idea not found");
        }
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $file = $uploadService->uploadFile($uploadedFile);
        $file->setStadt($stadt);
        $entityManager->persist($file);
        $entityManager->flush();
        return $file ? new JsonResponse(array('error' => 0)) : new JsonResponse(array('error' => 1));
    }

    /**
     * @Route("/login/download/{fileName}", name="login_download_file", methods={"GET"})
     * @ParamConverter("file", options={"mapping": {"fileName": "fileName"}})
     */
    public function downloadArticleReference(\App\Entity\File $file, FilesystemOperator $internFileSystem)
    {
        if (!$file) {
            throw new NotFoundHttpException("File not found");
        }

        $stream = $internFileSystem->read($file->getFileName());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $file->getType());
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x2D\x2F\x3A-\x40\x5B-\x60\x7B-\xFF]/', '', $file->getOriginalName())
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/login/removeFile/{fileName}", name="login_remove_file", methods={"GET"})
     * @ParamConverter("file", options={"mapping": {"fileName": "fileName"}})
     */
    public function removeFile(\App\Entity\File $file, FilesystemOperator $internFileSystem, Request $request)
    {

        $internFileSystem->delete($file->getFileName());
        $em = $this->getDoctrine()->getManager();
        $em->remove($file);
        $em->flush();
        return $this->redirect(
            $request
                ->headers
                ->get('referer')
        );
    }

//    /**
//     * @Route("/login/idea/markDeprected/{id}", name="login_idea_file_markDeprecated", methods={"POST"})
//     * @ParamConverter("file", class="App\Entity\File")
//     */
//    public function markDeprecatedFile(\App\Entity\File $file, IdeaService $ideaService)
//    {
//        if (!$ideaService->UserIsAdminOrOwner($file->getIdea(), $this->getUser()->getMyUser()) && !$file->getUser() == $this->getUser()->getMyUser()) {
//            return new JsonResponse(array('error' => true));
//        }
//        if ($file->getDeprecated()) {
//            $file->setDeprecated(false);
//        } else {
//            $file->setDeprecated(true);
//        }
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($file);
//        $em->flush();
//        return new JsonResponse(array('error' => false));
//
//    }
}
