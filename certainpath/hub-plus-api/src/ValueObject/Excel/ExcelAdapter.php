<?php

namespace App\ValueObject\Excel;

interface ExcelAdapter
{
    public function getRowIterator(array $columns): \Generator;

    public function getHeaders(): array;
}
