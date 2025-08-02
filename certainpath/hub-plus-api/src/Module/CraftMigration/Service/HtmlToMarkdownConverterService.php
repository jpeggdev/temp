<?php

declare(strict_types=1);

namespace App\Module\CraftMigration\Service;

use League\HTMLToMarkdown\HtmlConverter;

class HtmlToMarkdownConverterService
{
    public function convertHtmlToMarkdown(string $html): string
    {
        return (new HtmlConverter())->convert($html);
    }
}
