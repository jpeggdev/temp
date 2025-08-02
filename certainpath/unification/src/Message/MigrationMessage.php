<?php

namespace App\Message;

readonly class MigrationMessage
{
    public function __construct(
        public string $intacctId,
        public string $downloadedFilePath,
        public string $dataSource,
        public ?string $dataType,
        public ?string $trade,
        public array $options,
        public ?int $limit = null,
    ) {
    }
}
