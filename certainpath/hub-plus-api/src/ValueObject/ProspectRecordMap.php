<?php

namespace App\ValueObject;

class ProspectRecordMap extends AbstractRecordMap
{
    use ProspectFieldsTrait;

    public function __construct()
    {
        $this->tenant = 'tenant';
        $this->software = 'software';
        $this->version = 'version';
        $this->tag = 'tag';
        $this->prefixttl = 'PREFIXTTL';
        $this->individualname = 'INDIVIDUALNAME';
        $this->firstname = 'FIRSTNAME';
        $this->middlename = 'MIDDLENAME';
        $this->lastname = 'LASTNAME';
        $this->address = 'ADDRESS';
        $this->address2line = 'ADDRESS2LINE';
        $this->city = 'CITY';
        $this->state = 'STATE';
        $this->zip = 'ZIP';
        $this->zip4 = 'ZIP4';
        $this->dpbc = 'DPBC';
        $this->confidencecode = 'CONFIDENCECODE';
        $this->ageofindividual = 'AGEOFINDIVIDUAL';
        $this->addrtypeind = 'ADDRTYPEIND';
        $this->networthprem = 'NETWORTHPREM';
        $this->estincome = 'ESTINCOME';
        $this->homeownren = 'HOMEOWNREN';
        $this->hownrenfl = 'HOWNRENFL';
        $this->dsfwalksequence = 'DSFWALKSEQUENCE';
        $this->crrt = 'CRRT';
        $this->areacode = 'AREACODE';
        $this->phone = 'PHONE';
        $this->phone_nospace = 'PHONE_NOSPACE';
        $this->phoneflag = 'PHONEFLAG';
        $this->yearhomebuilt = 'YEARHOMEBUILT';
        $this->hub_plus_import_id = 'hub_plus_import_id';
    }
}
