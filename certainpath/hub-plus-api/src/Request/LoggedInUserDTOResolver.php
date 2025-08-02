<?php

namespace App\Request;

use App\DTO\LoggedInUserDTO;
use App\Service\GetLoggedInUserDTOService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class LoggedInUserDTOResolver implements ValueResolverInterface
{
    private GetLoggedInUserDTOService $getLoggedInUserDTOService;

    public function __construct(GetLoggedInUserDTOService $getLoggedInUserDTOService)
    {
        $this->getLoggedInUserDTOService = $getLoggedInUserDTOService;
    }

    /**
     * @return iterable<LoggedInUserDTO>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (LoggedInUserDTO::class !== $argument->getType()) {
            return [];
        }

        yield $this->getLoggedInUserDTOService->getLoggedInUserDTO();
    }
}
