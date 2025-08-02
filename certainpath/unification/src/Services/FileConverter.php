<?php

namespace App\Services;

use App\Entity\Company;
use App\Exceptions\FileConverterException;
use Exception;

use function App\Functions\{app_generateHashFromFile, app_lower};

class FileConverter
{
    private ?Company $company = null;

    public function __construct(
        public readonly FileWriter $fileWriter,
        public string $in2csvBinary
    ) {
    }

    public function setCompany(Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    private function getTemporaryFilePath(string $inputPath, string $extension): string
    {
        return $this->fileWriter->generateFilePath(
            $this->fileWriter->getTemporaryDataPath(),
            sprintf(
                '%s.%s',
                app_generateHashFromFile($inputPath),
                $extension
            ),
            'exports/' . $this->getCompany()?->getIdentifier()
        );
    }

    /**
     * @throws FileConverterException
     */
    public function convertToCsv(
        string $inputPath,
        ?int $limit = null
    ): ?string {
        $tempPath = $this->getTemporaryFilePath($inputPath, 'csv');
        if (file_exists($tempPath)) {
            return $tempPath;
        }

        if (
            self::isExcelFile($inputPath)
        ) {
            return $this->convertExcelToCsv(
                $inputPath
            );
        }

        if (
            self::isDbaseFile($inputPath)
        ) {
            return $this->convertDbaseToCsv(
                $inputPath,
                $limit
            );
        }

        throw new FileConverterException('Could not convert file.');
    }

    /**
     * @throws FileConverterException
     */
    private function convertDbaseToCsv(
        string $inputPath,
        ?int $limit = null
    ): ?string {
        $tempPath = $this->getTemporaryFilePath($inputPath, 'csv');
        $filePointer = fopen($tempPath, 'w');

        try {
            if (!$dbf = dbase_open($inputPath, DBASE_RDONLY)) {
                throw new FileConverterException(
                    $inputPath
                );
            }
        } catch (Exception $e) {
            throw new FileConverterException(
                $inputPath . ' ' . $e->getMessage()
            );
        }

        $numRecords = dbase_numrecords($dbf);
        //insert time stamp in human readable format down to the second

        for ($i = 1; $i <= $numRecords; $i++) {
            $record = array_map('trim', dbase_get_record_with_names($dbf, $i));
            $record = array_change_key_case($record);

            if ($i === 1) {
                fputcsv($filePointer, array_keys($record));
            }

            fputcsv($filePointer, $record);
            if (
                $limit &&
                $i >= $limit
            ) {
                break;
            }
        }

        fclose($filePointer);

        if (
            file_exists($tempPath) &&
            filesize($tempPath) > 0
        ) {
            return $tempPath;
        }

        return null;
    }

    private function convertExcelToCsv(
        string $inputPath
    ): ?string {
        $tempPath = $this->getTemporaryFilePath($inputPath, 'csv');
        $command = sprintf(
            '%s "%s" > "%s"',
            $this->in2csvBinary,
            $inputPath,
            $tempPath
        );

        exec($command);

        if (
            file_exists($tempPath) &&
            filesize($tempPath) > 0
        ) {
            return $tempPath;
        }

        return null;
    }

    /**
     * @throws FileConverterException
     */
    public function convertToDbase(string $inputPath): string
    {
        $tempPath = $this->getTemporaryFilePath($inputPath, 'dbf');

        if (file_exists($tempPath)) {
            return $tempPath;
        }

        if (
            self::isCsvFile($inputPath)
        ) {
            return $this->convertCsvToDbase(
                $inputPath
            );
        }

        throw new FileConverterException('Could not convert file.');
    }

    private function convertCsvToDbase(string $inputPath): ?string
    {
        $tempPath = $this->getTemporaryFilePath($inputPath, 'dbf');

        $dbf = null;
        if (($filePointer = fopen($inputPath, 'r')) !== false) {
            while (($data = fgetcsv($filePointer, null, ",")) !== false) {
                if (!$dbf) {
                    $fields = [ ];
                    foreach ($data as $header) {
                        $fields[] = [
                            $header,
                            'C',
                            100
                        ];
                    }

                    $dbf = dbase_create($tempPath, $fields);
                    continue;
                }


                dbase_add_record($dbf, array_values($data));
            }
        }

        dbase_close($dbf);
        fclose($filePointer);

        return $tempPath;
    }

    public static function isCsvFile(string $inputPath): bool
    {
        return (in_array(self::getFileType($inputPath), [
            'csv'
        ]));
    }

    private static function isDbaseFile(string $inputPath): bool
    {
        return (in_array(self::getFileType($inputPath), [
            'dbf'
        ]));
    }

    private static function isExcelFile(string $inputPath): bool
    {
        return (in_array(self::getFileType($inputPath), [
            'xls',
            'xlsx'
        ]));
    }

    private static function getFileType(string $inputPath)
    {
        $bits = explode('.', $inputPath);
        return app_lower(end($bits));
    }
}
