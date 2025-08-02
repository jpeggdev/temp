<?php

namespace App\Service;

class ExcelAnalyzerService
{
    public function getDifferences(
        string $filePath1,
        string $filePath2,
        string $scratchDirectoryPath,
    ): array {
        // Ensure the scratch directory exists
        if (!is_dir($scratchDirectoryPath)) {
            if (!mkdir($scratchDirectoryPath, 0777, true) && !is_dir($scratchDirectoryPath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $scratchDirectoryPath));
            }
        }

        // Copy files to scratch directory with .zip extension
        $zipFilePath1 = $scratchDirectoryPath.'/'.basename($filePath1, '.xlsx').'.zip';
        $zipFilePath2 = $scratchDirectoryPath.'/'.basename($filePath2, '.xlsx').'.zip';
        copy($filePath1, $zipFilePath1);
        copy($filePath2, $zipFilePath2);

        // Unzip files into respective directories
        $dirPath1 = $scratchDirectoryPath.'/'.basename($filePath1, '.xlsx');
        $dirPath2 = $scratchDirectoryPath.'/'.basename($filePath2, '.xlsx');
        $this->unzipFile($zipFilePath1, $dirPath1);
        $this->unzipFile($zipFilePath2, $dirPath2);

        // Recursively compare directories
        return $this->compareDirectories(
            $dirPath1,
            $dirPath2,
            $scratchDirectoryPath
        );
    }

    private function unzipFile(string $zipFilePath, string $extractTo): void
    {
        $zip = new \ZipArchive();
        if (true === $zip->open($zipFilePath)) {
            $zip->extractTo($extractTo);
            $zip->close();
        } else {
            throw new \RuntimeException("Unable to open zip file: $zipFilePath");
        }
    }

    private function compareDirectories(
        string $dirPath1,
        string $dirPath2,
        string $scratchPath,
    ): array {
        $differences = [];
        $iterator1 = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath1));
        $iterator2 = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath2));

        $files1 = [];
        foreach ($iterator1 as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($dirPath1, '', $file->getPathname());
                $files1[$relativePath] = $file->getPathname();
            }
        }

        $files2 = [];
        foreach ($iterator2 as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($dirPath2, '', $file->getPathname());
                $files2[$relativePath] = $file->getPathname();
            }
        }

        $showDirPath1 = str_replace($scratchPath, '', $dirPath1); // $dirPath1;
        $showDirPath2 = str_replace($scratchPath, '', $dirPath2); // $dirPath2;
        // Compare files in both directories
        foreach ($files1 as $relativePath => $filePath1) {
            if (!isset($files2[$relativePath])) {
                $differences[] = "File $relativePath exists in $showDirPath1 but not in $showDirPath2";
            } elseif (md5_file($filePath1) !== md5_file($files2[$relativePath])) {
                $differences[] = "File $relativePath differs between $showDirPath1 and $showDirPath2";
            } else {
                $differences[] = "File $relativePath is identical in both directories";
            }
        }

        foreach ($files2 as $relativePath => $filePath2) {
            if (!isset($files1[$relativePath])) {
                $differences[] = "File $relativePath exists in $showDirPath2 but not in $showDirPath1";
            }
        }

        return $differences;
    }
}
