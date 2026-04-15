<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadService
{
    public function __construct(private FilesystemOperator $internFileSystem, private ValidatorInterface $validator, private EntityManagerInterface $em)
    {
    }

    public function uploadFile(UploadedFile $uploadedFile, string $maxSize = '50M')
    {
        $violations = $this->validator->validate(
            $uploadedFile,
            [
                new NotBlank([
                    'message' => 'Please select a file to upload'
                ]),
                new File([
                    'maxSize' => $maxSize,
                    'mimeTypes' => [
                        'image/*',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain',
                        'application/zip'
                    ]
                ])
            ]
        );
        if ($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            $violation = $violations[0];
            return null;
        }

        $random = md5(uniqid());
        $stream = fopen($uploadedFile->getRealPath(), 'r+');
        $this->internFileSystem->writeStream($random, $stream);
        fclose($stream);
        $file = new \App\Entity\File();
        $file->setFileName($random);
        $file->setOriginalName($uploadedFile->getClientOriginalName());
        $file->setType($uploadedFile->getMimeType());
        $file->setCreatedAt(new \DateTime());
        $file->setSize($uploadedFile->getSize());
        $this->em->persist($file);
        $this->em->flush();
        return $file;

    }
}
