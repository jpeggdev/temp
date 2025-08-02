<?php

namespace App\Transformers;

use App\Entity\MailPackage;
use League\Fractal\TransformerAbstract;

class MailPackageTransformer extends TransformerAbstract
{
    public function transform(MailPackage $mailPackage): array
    {
        $data['id'] = $mailPackage->getId();
        $data['name'] = $mailPackage->getName();
        $data['series'] = $mailPackage->getSeries();
        $data['external_id'] = $mailPackage->getExternalId();
        $data['is_active'] = $mailPackage->isActive();
        $data['is_deleted'] = $mailPackage->isDeleted();

        return $data;
    }
}
