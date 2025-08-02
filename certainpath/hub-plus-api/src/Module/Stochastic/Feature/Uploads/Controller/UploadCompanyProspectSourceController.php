<?php

namespace App\Module\Stochastic\Feature\Uploads\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\FileDoesNotExist;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Exception\UnsupportedImportTypeException;
use App\Module\Stochastic\Feature\Uploads\DTO\Request\UploadCompanyProspectSourceDTO;
use App\Module\Stochastic\Feature\Uploads\Service\UploadCompanyProspectSourceService;
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
class UploadCompanyProspectSourceController extends ApiController
{
    public function __construct(
        private readonly UploadCompanyProspectSourceService $uploadService,
    ) {
    }

    /**
     * @throws IOException
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws UnsupportedImportTypeException
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws Exception
     * @throws FileDoesNotExist
     * @throws NoFilePathWasProvided
     */
    #[Route(
        '/stochastic-prospects-source/upload',
        name: 'api_company_upload_prospect_source',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] UploadCompanyProspectSourceDTO $dto,
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
