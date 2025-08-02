<?php

namespace App\Services;

use Knp\Component\Pager\PaginatorInterface;

class PaginatorService
{
    public function __construct(
        public PaginatorInterface $paginator
    ) {
    }

    public function paginate($query, int $page = 1, int $perPage = 10): array
    {
        $pagination = $this->paginator->paginate(
            $query,
            $page,
            $perPage
        );

        return [
            'items' => $pagination->getItems(),
            'total' => $pagination->getTotalItemCount(),
            'currentPage' => $pagination->getCurrentPageNumber(),
            'perPage' => $pagination->getItemNumberPerPage(),
        ];
    }
}
