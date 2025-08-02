<?php

namespace App\Services;

use App\Entity\MailPackage;
use App\Repository\MailPackageRepository;

readonly class MailPackageService
{
    public function __construct(
        private MailPackageRepository $mailPackageRepository,
    ) {
    }

    public function createMailPackage(
        string $name,
        int $series = 1
    ): MailPackage {
        $mailPackage = (new MailPackage())
            ->setName($name)
            ->setSeries($series); // currently not in use

        return $this->mailPackageRepository->saveMailPackage($mailPackage);
    }
}
