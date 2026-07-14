<?php

namespace App\Service\Database;

/**
 * Packs a file into a password protected, AES-256 encrypted zip archive.
 */
class DatabaseDumpZipper
{
    /**
     * @param string $sourcePath path of the file that should be zipped
     * @param string $zipPath    path of the zip archive that will be created
     * @param string $entryName  name the file gets inside the archive
     * @param string $password   password used to encrypt the archive
     */
    public function zip(string $sourcePath, string $zipPath, string $entryName, string $password): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException(sprintf('Could not create zip archive "%s".', $zipPath));
        }

        $zip->setPassword($password);
        $zip->addFile($sourcePath, $entryName);
        $zip->setEncryptionName($entryName, \ZipArchive::EM_AES_256);

        if ($zip->close() !== true) {
            throw new \RuntimeException('Could not write the encrypted zip archive.');
        }
    }
}
