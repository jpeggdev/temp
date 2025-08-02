<?php

namespace App\Parsers;

use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;
use ReflectionException;

use function App\Functions\app_getSanitizedHeaderValue;
use function App\Functions\app_nullify;

class CsvRecordParser extends RecordParser
{
    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function parseRecords(int $limit = null): self
    {
        // Read the original CSV file
        $csvReader = Reader::createFromStream(
            fopen($this->filePath, 'rb+')
        );
        $csvReader->setHeaderOffset(0);
        $headers = $csvReader->getHeader();
        $sanitizedHeaders = array_map(static function ($header) {
            return app_getSanitizedHeaderValue($header);
        }, $headers);

        // Read all rows
        $records = iterator_to_array($csvReader->getRecords(), false);

        // Write the sanitized headers and the rest of the CSV content back to the original file
        $csvWriter = Writer::createFromStream(
            fopen($this->filePath, 'wb')
        );
        $csvWriter->insertOne($sanitizedHeaders);
        $csvWriter->insertAll($records);

        // Re-read from the original file
        $csvReader = Reader::createFromStream(
            fopen($this->filePath, 'rb')
        );
        $csvReader->setHeaderOffset(0);

        $csvReader->addFormatter(function (array $row) {
            return array_map(static function ($field) {
                return app_nullify($field);
            }, $row);
        });
        $statement = Statement::create();
        if ($limit) {
            $statement = $statement->limit($limit);
        }
        $this->records = iterator_to_array(
            $statement->process($csvReader),
            false
        );
        $this->headers = $sanitizedHeaders;
        return $this;
    }
}
