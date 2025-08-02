<?php

declare(strict_types=1);

namespace App\Service\Unification;

use App\DTO\Request\Prospect\UpdateStochasticProspectDoNotMailRequestDTO;
use App\Exception\APICommunicationException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

interface UpdateStochasticProspectDoNotMailServiceInterface
{
    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function updateProspectDoNotMail(
        int $prospectId,
        UpdateStochasticProspectDoNotMailRequestDTO $dto,
    ): array;
}
