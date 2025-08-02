<?php

namespace App\Module\Stochastic\Feature\Uploads\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\FileDoesNotExist;
use App\Exception\IntentWasUnclear;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Exception\UnsupportedSoftware;
use App\Exception\UnsupportedTrade;
use App\Module\Stochastic\Feature\Uploads\DTO\Request\UploadCompanyFieldServiceSoftwareReportDTO;
use App\Module\Stochastic\Feature\Uploads\Service\UploadCompanyFieldServiceSoftwareReportService;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UploadCompanyFieldServiceSoftwareReportController extends ApiController
{
    public function __construct(
        private readonly UploadCompanyFieldServiceSoftwareReportService $uploadService,
    ) {
    }

    /**
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws UnsupportedSoftware
     * @throws FileDoesNotExist
     * @throws Exception
     * @throws NoFilePathWasProvided
     * @throws IOException
     * @throws UnsupportedTrade
     * @throws IntentWasUnclear
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     */
    #[Route(
        '/stochastic-field-service/upload',
        name: 'api_company_upload_field_service_software_report',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] UploadCompanyFieldServiceSoftwareReportDTO $dto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $importId = $this->uploadService->handle(
            $dto,
            $loggedInUserDTO->getActiveCompany(),
            $request->files->get('file')
        );

        return $this->createSuccessResponse([
            'message' => 'File upload accepted. Processing will happen asynchronously.',
            'importId' => $importId,
        ]);
    }
}
