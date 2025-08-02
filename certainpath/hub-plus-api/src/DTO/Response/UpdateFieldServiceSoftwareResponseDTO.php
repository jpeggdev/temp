<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Company;

readonly class UpdateFieldServiceSoftwareResponseDTO
{
    public string $message;
    public ?int $fieldServiceSoftwareId;
    public ?string $fieldServiceSoftwareName;

    public function __construct(
        string $message,
        ?int $fieldServiceSoftwareId,
        ?string $fieldServiceSoftwareName,
    ) {
        $this->message = $message;
        $this->fieldServiceSoftwareId = $fieldServiceSoftwareId;
        $this->fieldServiceSoftwareName = $fieldServiceSoftwareName;
    }

    public static function fromEntity(Company $company): self
    {
        $fieldServiceSoftware = $company->getFieldServiceSoftware();

        return new self(
            'Field service software successfully updated.',
            $fieldServiceSoftware?->getId(),
            $fieldServiceSoftware?->getName()
        );
    }
}
