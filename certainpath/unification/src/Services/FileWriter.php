<?php

namespace App\Services;

use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use RuntimeException;
use function App\Functions\{
    app_formatFilename
};

class FileWriter
{
    private Filesystem $filesystem;

    public function __construct(
        public readonly LoggerInterface $logger,
        private readonly string $temporaryDataPath,
        private readonly string $persistentDataPath
    )
    {
        $this->filesystem = new Filesystem;
    }

    public function writeFile(string $filePath, string $contents = null) : ?string
    {
        try {
            $this->filesystem->dumpFile(
                $filePath,
                $contents
            );
        }
        catch (RuntimeException $e) {
            return null;
        }

        return $filePath;
    }

    public function generateFilePath(
        string $baseDirectory,
        string $fileName,
        string $companyIdentifier = null
    ) : ?string
    {
        $fileName = app_formatFilename(
            $fileName
        );

        if (!is_dir($baseDirectory)) {
            $this->filesystem->mkdir(
                $baseDirectory
            );
        }

        if (empty($fileName)) {
            return null;
        }

        $directory = implode(DIRECTORY_SEPARATOR, array_filter([
            $baseDirectory,
            $companyIdentifier
        ]));

        $this->filesystem->mkdir(
            $directory
        );

        return implode(DIRECTORY_SEPARATOR, [
            $directory,
            $fileName
        ]);
    }

    public function getTemporaryDataPath(): string
    {
        return $this->temporaryDataPath;
    }

    public function getPersistentDataPath(): string
    {
        return $this->persistentDataPath;
    }
}
