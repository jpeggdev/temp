<?php

namespace App\ValueObject;

class MemberRecordMap extends CustomerRecordMap
{
    use MemberFieldsTrait;

    public function __construct()
    {
        parent::__construct();
        $this->active_member = 'Active Member,active_member,Active,active';
        $this->membership_type = 'Membership Type,membership_type,membership type';
        $this->current_status = 'Current Status,current_status,Member Status,member status';
        $this->version = 'Version,version';
        $this->hub_plus_import_id = 'hub_plus_import_id';
    }
}
