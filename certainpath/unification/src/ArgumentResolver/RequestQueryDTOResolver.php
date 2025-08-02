<?php

namespace App\ArgumentResolver;

use App\DTO\Query\PaginationDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestQueryDTOResolver implements ValueResolverInterface
{
    private function supports(ArgumentMetadata $argument): bool
    {
        return $argument->getType() === PaginationDTO::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($this->supports($argument)) {
            yield new PaginationDTO(
                page: $request->query->getInt('page', PaginationDTO::DEFAULT_PAGE),
                perPage: $request->query->getInt('perPage', PaginationDTO::DEFAULT_PER_PAGE),
                includes: $request->query->all('includes'),
                sortOrder: $request->query->get('sortOrder', PaginationDTO::DEFAULT_SORT_ORDER)
            );
        }
    }
}
