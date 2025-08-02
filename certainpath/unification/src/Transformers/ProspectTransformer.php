<?php

namespace App\Transformers;

use App\Entity\Prospect;
use League\Fractal\TransformerAbstract;

class ProspectTransformer extends TransformerAbstract
{
    public function transform(Prospect $prospect): array
    {
        return [
            'id' => $prospect->getId(),
            'full_name' => $prospect->getFullName(),
            'first_name' => $prospect->getFirstName(),
            'last_name' => $prospect->getLastName(),
            'address1' => $prospect->getAddress1(),
            'address2' => $prospect->getAddress2(),
            'city' => $prospect->getCity(),
            'state' => $prospect->getState(),
            'postal_code' => $prospect->getPostalCode(),
            'do_not_mail' => $prospect->isDoNotMail(),
            'do_not_contact' => $prospect->isDoNotContact(),
            'external_id' => $prospect->getExternalId(),
            'is_preferred' => $prospect->isPreferred(),
            'is_active' => $prospect->isActive(),
            'is_deleted' => $prospect->isDeleted(),
            'company_id' => $prospect->getCompany()?->getId(),
            'customer_id' => $prospect->getCustomer()?->getId()
        ];
    }
}
