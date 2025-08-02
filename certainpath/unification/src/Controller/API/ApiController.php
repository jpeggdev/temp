<?php

declare(strict_types=1);

namespace App\Controller\API;

use League\Csv\Writer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ApiController extends AbstractController
{
    protected function createJsonSuccessResponse(mixed $data, array $pagination = []): JsonResponse
    {
        $response = [
            'data' => $data,
        ];

        if (!empty($pagination)) {
            $response['meta'] = [
                'total' => $pagination['total'],
                'currentPage' => $pagination['currentPage'],
                'perPage' => $pagination['perPage'],
            ];
        }

        return $this->json($response, Response::HTTP_OK);
    }

    protected function createCsvResponse(string $csv, string $fileName): Response
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
        ];

        return new Response($csv, Response::HTTP_OK, $headers);
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
}
