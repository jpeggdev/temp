<?php

namespace App\Module\Stochastic\Feature\Uploads\Service;

use App\Client\UnificationClient;
use App\Entity\Company;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\FileDoesNotExist;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Module\Stochastic\Feature\Uploads\Exception\UploadDoNotMailListException;
use App\Module\Stochastic\Feature\Uploads\ValueObject\DoNotMailListRecordMap;
use App\Service\AbstractUploadService;
use App\ValueObject\DoNotMailListRecord;
use App\ValueObject\TabularFile;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class UploadDoNotMailListService extends AbstractUploadService
{
    public function __construct(
        string $tempDirectory,
        LoggerInterface $logger,
        private UnificationClient $unificationClient,
    ) {
        parent::__construct($tempDirectory, $logger);
    }

    /**
     * @throws Exception
     * @throws SyntaxError
     * @throws IOException
     * @throws FieldsAreMissing
     * @throws FileDoesNotExist
     * @throws CouldNotReadSheet
     * @throws UnavailableStream
     * @throws ExcelFileIsCorrupted
     * @throws NoFilePathWasProvided
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws ReaderNotOpenedException
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UploadDoNotMailListException
     * @throws UnsupportedFileTypeException
     * @throws RedirectionExceptionInterface
     */
    public function handle(
        UploadedFile $uploadedFile,
        Company $company,
    ): array {
        $uploadedFilePath = $this->moveUploadedFile(
            $uploadedFile,
            $company,
            'restricted-address',
        );

        $tabularData = TabularFile::fromExcelOrCsvFile(
            new DoNotMailListRecordMap(),
            $uploadedFilePath
        );

        $doNotMailListData = [];

        $allRows = $tabularData->getRowIteratorForColumns($tabularData->getHeadersAsArray());
        foreach ($allRows as $row) {
            $doNotMailListData[] = DoNotMailListRecord::fromTabularRecord($row)->toArray();
        }

        $response = $this->unificationClient->sendPostRequest(
            $this->prepareUrl(),
            $this->preparePayload($doNotMailListData),
        );

        return $this->validateResponse($response);
    }

    private function prepareUrl(): string
    {
        // ?XDEBUG_SESSION_START=PHPSTORM
        return sprintf(
            '%s/api/addresses/matches',
            $this->unificationClient->getBaseUri()
        );
    }

    private function preparePayload(array $addresses): array
    {
        return [
            'addresses' => $addresses,
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UploadDoNotMailListException
     * @throws RedirectionExceptionInterface
     */
    private function validateResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        if (Response::HTTP_OK !== $statusCode) {
            $responseArray = $response->toArray(false);
            $errorMessage = $responseArray['errors']['detail'] ?? 'Unknown error uploading the do not mail list.';

            throw new UploadDoNotMailListException($errorMessage, $statusCode);
        }

        $responseData = $response->toArray();

        return $responseData['data'] ?? [];
    }
}
