<?php

namespace App\ValueObjects;

use App\Exceptions\StochasticFilePathWasInvalid;

class StochasticFile
{
    private string $pathFromSheet;
    private string $fileName;
    private string $s3Uri;

    /**
     * @throws StochasticFilePathWasInvalid
     */
    public function __construct(
        string $pathFromSheet,
        ?string $lifeFolder = null
    ) {
        $isLife = $lifeFolder !== null;
        $pathFromSheet = str_replace('"', '', $pathFromSheet);
        $this->pathFromSheet = $pathFromSheet;
        $pathFromSheet = str_replace('C:\DBF Files S3\\', '', $pathFromSheet);
        $pathFromSheet = trim($pathFromSheet);
        if (!$isLife && !str_ends_with($pathFromSheet, '.dbf')) {
            throw new StochasticFilePathWasInvalid(
                'The path provided does not end with .dbf: ' . $pathFromSheet
            );
        }
        if ($isLife) {
            //only if path doesn't already end with .DBF
            if (!str_ends_with($pathFromSheet, '.DBF')) {
                $pathFromSheet .= '.DBF';
            }
        }
        $this->fileName = $pathFromSheet;
        if (!$isLife) {
            $this->s3Uri = 's3://stochastic-files/sync/lists/' . $this->fileName;
        } else {
            $this->s3Uri =
                's3://stochastic-files/sync/customer-data/'
                . $lifeFolder
                . '/4 Power Data/'
                . $this->fileName;
        }
    }

    /**
     * @throws StochasticFilePathWasInvalid
     */
    public static function fromMasterSpreadsheet(
        string $pathFromSheet
    ): self {
        return new self(
            $pathFromSheet
        );
    }

    /**
     * @throws StochasticFilePathWasInvalid
     */
    public static function fromMasterSpreadsheetLife(?string $lifeFileName, ?string $lifeFolder): self
    {
        return new self(
            $lifeFileName,
            $lifeFolder
        );
    }

    public function getPathFromSheet(): string
    {
        return $this->pathFromSheet;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getS3Uri(): string
    {
        return $this->s3Uri;
    }
}
