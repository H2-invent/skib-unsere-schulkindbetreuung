<?php

namespace App\Controller;

use App\Form\Type\DatabaseDumpType;
use App\Service\Database\AnonymizedDatabaseDumper;
use App\Service\Database\DatabaseDumpZipper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/database')]
class DatabaseController extends AbstractController
{
    public function __construct(
        private readonly AnonymizedDatabaseDumper $dumper,
        private readonly DatabaseDumpZipper $zipper,
    ) {
    }

    #[Route('', name: 'admin_database_dump')]
    public function dump(Request $request): Response
    {
        $form = $this->createForm(DatabaseDumpType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            return $this->createEncryptedDumpResponse($password);
        }

        return $this->render('administrator/database.html.twig', [
            'title' => 'Datenbank',
            'form' => $form->createView(),
        ]);
    }

    private function createEncryptedDumpResponse(string $password): BinaryFileResponse
    {
        $timestamp = (new \DateTime())->format('Y-m-d_His');
        $sqlPath = tempnam(sys_get_temp_dir(), 'db_dump_');
        $zipPath = tempnam(sys_get_temp_dir(), 'db_dump_zip_');

        try {
            $this->dumper->dumpTo($sqlPath);
            $this->zipper->zip($sqlPath, $zipPath, sprintf('dump_%s.sql', $timestamp), $password);
        } catch (\Throwable $e) {
            @unlink($sqlPath);
            @unlink($zipPath);
            throw $e;
        }

        @unlink($sqlPath);

        $response = new BinaryFileResponse($zipPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('database_dump_%s.zip', $timestamp)
        );
        $response->headers->set('Content-Type', 'application/zip');
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
