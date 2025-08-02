<?php

namespace App\Controller;

use App\Entity\SavedQuery;
use App\Repository\SavedQueryRepository;
use http\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use function App\Functions\app_lower;

class ExportController extends AbstractController
{
    public const ALLOWED_DATA_TYPES = [
        'prospects',
    ];

    public const ALLOWED_DATA_TARGETS = [
        'mailmanager',
    ];

    public const ALLOWED_FORMATS = [
        'dbf',
    ];

    #[Route(
        '/app/export/{dataType}/{dataTarget}/{savedQueryId}',
        name: 'app_export_saved_query'
    )]
    public function exportSavedQuery(
        KernelInterface $kernel,
        SavedQueryRepository $savedQueryRepository,
        int $savedQueryId,
        string $dataType,
        string $dataTarget
    ): Response {
        $savedQuery = $savedQueryRepository->find($savedQueryId);
        if (!$savedQuery instanceof SavedQuery) {
            throw new NotFoundHttpException();
        }

        $company = $savedQuery->getCompany();
        $this->validateCompanyAccess($company);

        $dataType = app_lower($dataType);
        if (!in_array($dataType, self::ALLOWED_DATA_TYPES)) {
            throw new InvalidArgumentException();
        }

        $dataTarget = app_lower($dataTarget);
        if (!in_array($dataTarget, self::ALLOWED_DATA_TARGETS)) {
            throw new InvalidArgumentException();
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'unification:data:export',
            'company' => $company->getIdentifier(),
            'data-type' => $dataType,
            'data-target' => $dataTarget,
            'savedQuery' => $savedQuery->getId(),
            '--outputTableFormat' => false,
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $filePath = trim($output->fetch());
        if (empty($filePath)) {
            throw new InvalidArgumentException();
        }
        $filePathBits = explode('.', $filePath);
        $format = $filePathBits[1] ?? null;
        if (
            empty($format) ||
            !in_array($format, self::ALLOWED_FORMATS)
        ) {
            throw new InvalidArgumentException();
        }

        $response = new BinaryFileResponse($filePath);

        $fileName = sprintf(
            '%s_%s_%s-%s.%s',
            $company->getIdentifier(),
            $dataType,
            $dataTarget,
            $savedQuery->getId(),
            $format
        );

        // Set the Content-Disposition header to prompt the user to download the file
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );

        return $response;
    }
}
