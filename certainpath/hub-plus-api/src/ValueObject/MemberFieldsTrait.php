<?php

namespace App\ValueObject;

trait MemberFieldsTrait
{
    public ?string $active_member = null;
    public ?string $membership_type = null;
    public ?string $current_status = null;

    public ?string $version = null;
    public ?string $hub_plus_import_id = null;
}
