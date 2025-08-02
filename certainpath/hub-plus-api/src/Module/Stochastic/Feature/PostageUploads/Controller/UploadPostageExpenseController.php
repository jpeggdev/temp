<?php

namespace App\Module\Stochastic\Feature\PostageUploads\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\FileDoesNotExist;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Exception\UnsupportedImportTypeException;
use App\Module\Stochastic\Feature\PostageUploads\DTO\UploadPostageExpenseDTO;
use App\Module\Stochastic\Feature\PostageUploads\Service\UploadPostageExpenseService;
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
class UploadPostageExpenseController extends ApiController
{
    public function __construct(
        private readonly UploadPostageExpenseService $uploadService,
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
        '/stochastic-postage-expense/upload',
        name: 'api_upload_postage_expense',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] UploadPostageExpenseDTO $dto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $this->uploadService->handleFromUpload(
            $dto,
            $loggedInUserDTO->getActiveCompany(),
            $request->files->get('file')
        );

        return $this->createSuccessResponse([
            'message' => 'File upload accepted.',
        ]);
    }
}
