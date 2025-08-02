<?php

namespace App\Module\Stochastic\Feature\Uploads\DTO\Request;

class UploadCompanyFieldServiceSoftwareReportDTO
{
    public bool $isJobsOrInvoiceFile = false;
    public bool $isActiveClubMemberFile = false;
    public bool $isMemberFile = false;

    public ?string $trade = null;
    public ?string $software = null;
}
