<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Exception\FileDoesNotExist;
use League\Csv\Writer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ApiController extends AbstractController
{
    protected function createSuccessResponse(mixed $data, ?int $totalCount = null, ?bool $hasMore = null): JsonResponse
    {
        $response = [
            'data' => $data,
        ];

        $meta = [];

        if (null !== $totalCount) {
            $meta['totalCount'] = $totalCount;
        }

        if (null !== $hasMore) {
            $meta['hasMore'] = $hasMore;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return $this->json($response, Response::HTTP_OK);
    }

    protected function createCsvStreamedResponse(string $fileName, \Generator $generator): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
        ];

        $callback = function () use ($generator) {
            $csv = Writer::createFromStream(fopen('php://output', 'wb'));

            foreach ($generator as $row) {
                $csv->insertOne($row);
            }
        };

        return new StreamedResponse(
            $callback,
            Response::HTTP_OK,
            $headers
        );
    }

    /**
     * @throws FileDoesNotExist
     */
    protected function getUploadedFilePathForCompanyAndType(
        Request $request,
        Company $company,
        string $uploadType,
        string $tempDirectoryLocation,
    ): string {
        $intacctId = $company->getIntacctId();
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if (!$file || !$file->isReadable()) {
            throw new FileDoesNotExist($file ? $file->getRealPath() : '');
        }
        $timestamp = (new \DateTime())->format('Y-m-d-H-i-s');
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $newFilename = sprintf('%s/%s_%s.%s', $intacctId, $originalFilename, $timestamp, $extension);
        $uploadedFilePath = $tempDirectoryLocation.'/'.$uploadType.'/'.$newFilename;
        $file->move(dirname($uploadedFilePath), basename($uploadedFilePath));

        return $uploadedFilePath;
    }
}
