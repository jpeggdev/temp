<?php

namespace App\Module\Stochastic\Feature\Uploads\DTO\Request;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadDoNotMailListDTO
{
    public function __construct(
        #[Assert\NotNull(message: 'A file must be uploaded.')]
        #[Assert\File(
            maxSize: '100M',
            mimeTypes: [
                'text/csv',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            maxSizeMessage: 'The file is too large. Maximum size allowed is 100 MB.',
            mimeTypesMessage: 'Please upload a valid CSV or Excel file.'
        )]
        public ?UploadedFile $file = null,
    ) {
    }
}
