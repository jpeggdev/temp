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
use App\Module\Stochastic\Feature\Uploads\Exception\UploadDoNotMailListException;
use App\Module\Stochastic\Feature\Uploads\Service\UploadDoNotMailListService;
use App\Module\Stochastic\Feature\Uploads\Voter\DoNotMailListVoter;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class UploadDoNotMailListController extends ApiController
{
    public function __construct(
        private readonly UploadDoNotMailListService $uploadService,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws FileDoesNotExist
     * @throws NoFilePathWasProvided
     * @throws RedirectionExceptionInterface
     * @throws UploadDoNotMailListException
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws Exception
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    #[Route(
        '/stochastic/do-not-mail-list/upload',
        name: 'api_do_not_mail_list_upload',
        methods: ['POST']
    )]
    public function __invoke(
        Request $request,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->denyAccessUnlessGranted(DoNotMailListVoter::UPLOAD);

        $company = $loggedInUserDTO->getActiveCompany();
        $responseData = $this->uploadService->handle(
            $request->files->get('file'),
            $company
        );

        return $this->createSuccessResponse($responseData);
    }
}
