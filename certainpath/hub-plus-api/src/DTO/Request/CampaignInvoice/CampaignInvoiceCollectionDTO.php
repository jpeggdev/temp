<?php

namespace App\DTO\Request\CampaignInvoice;

use Symfony\Component\Validator\Constraints as Assert;

class CampaignInvoiceCollectionDTO
{
    /**
     * @var CampaignInvoiceCollectionDTO[]
     */
    #[Assert\Valid]
    public array $campaignInvoiceDTOs = [];

    public function addCampaignInvoiceDTO(CampaignInvoiceDTO $campaignInvoice): self
    {
        $this->campaignInvoiceDTOs[] = $campaignInvoice;

        return $this;
    }

    public function getCampaignInvoiceDTOs(): array
    {
        return $this->campaignInvoiceDTOs;
    }
}
