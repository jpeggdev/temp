<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\File;
use App\Entity\Folder;
use App\Module\Hub\Feature\FileManagement\Exception\FolderOperationException;
use App\Repository\FilesystemNodeRepository;
use App\Service\AmazonS3Service;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

readonly class BulkDownloadFilesystemNodesService
{
    public function __construct(
        private AmazonS3Service $amazonS3Service,
        private FilesystemNodeRepository $filesystemNodeRepository,
    ) {
    }

    /**
     * Download multiple filesystem nodes as a ZIP archive or single file if applicable.
     */
    public function downloadNodes(array $uuids): StreamedResponse
    {
        return $this->createZipArchive($uuids);
    }

    /**
     * Create a ZIP archive containing all selected nodes.
     */
    private function createZipArchive(array $uuids): StreamedResponse
    {
        $tempZipFile = tempnam(sys_get_temp_dir(), 'download_').'.zip';
        $zip = new ZipArchive();

        if (true !== $zip->open($tempZipFile, \ZipArchive::CREATE)) {
            throw new FolderOperationException('Failed to create ZIP archive');
        }

        $fileCount = 0;
        $nodeName = null;

        foreach ($uuids as $uuid) {
            $node = $this->filesystemNodeRepository->findOneBy(['uuid' => $uuid]);
            if (!$node) {
                continue;
            }

            $nodeName = $node->getName();

            if ($node instanceof File) {
                $this->addFileToZip($zip, $node, '', $fileCount);
            } elseif ($node instanceof Folder) {
                $this->addFolderToZip($zip, $node, '', $fileCount);
            }
        }

        $zip->close();

        $zipFilename = 'download_'.date('Y-m-d_H-i-s').'.zip';

        return new StreamedResponse(function () use ($tempZipFile) {
            readfile($tempZipFile);
            unlink($tempZipFile);
        }, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$zipFilename.'"',
            'Content-Length' => filesize($tempZipFile),
        ]);
    }

    private function addFileToZip(\ZipArchive $zip, File $file, string $relativePath, int &$fileCount): void
    {
        $presignedUrl = $this->amazonS3Service->generatePresignedUrl(
            $file->getBucketName(),
            $file->getObjectKey()
        );

        $filename = $relativePath.($relativePath ? '/' : '').$file->getName();

        $fileContent = file_get_contents($presignedUrl);

        if (false !== $fileContent) {
            $zip->addFromString($filename, $fileContent);
            ++$fileCount;
        }
    }

    private function addFolderToZip(\ZipArchive $zip, Folder $folder, string $relativePath, int &$fileCount): void
    {
        $folderPath = $relativePath.($relativePath ? '/' : '').$folder->getName();
        $zip->addEmptyDir($folderPath);

        foreach ($folder->getChildren() as $child) {
            if ($child instanceof File) {
                $this->addFileToZip($zip, $child, $folderPath, $fileCount);
            } elseif ($child instanceof Folder) {
                $this->addFolderToZip($zip, $child, $folderPath, $fileCount);
            }
        }
    }
}
