<?php

namespace App\Services\DMER;

use App\Entity\Company;
use Carbon\Carbon;

class DMERTemplateFactory
{
    public const DEFAULT_DMER_TEMPLATE = 'YTD-Simple-DMER_BUDGET-SL-2023.xlsm';

    public static function getCallWorksheetName(Company $company): string
    {
        return 'Call Data';
    }

    public static function getSalesWorksheetName(Company $company): string
    {
        return 'Sales Data';
    }

    public static function getWagesWorksheetName(Company $company): string
    {
        return 'Wages & Materials';
    }

    public static function getMembershipStartAndEndingPoints(Company $company): array
    {
        if (Carbon::now()->isLeapYear()) {
            return ['O4', 'P369'];
        }
        return ['O4', 'P368'];
    }

    public function getTemplate(Company $company): string
    {
        if (!$company->hasTrades()) {
            throw new \InvalidArgumentException("Company '{$company->getIdentifier()}' must have at least one trade");
        }

        return self::DEFAULT_DMER_TEMPLATE;
    }

    public static function generateFileName(string $tradeName, int $year): string
    {
        return 'DMER-' . $year . '.xlsm';
    }
}