<?php

namespace App\ValueObject;

class MemberRecord extends CustomerRecord
{
    use MemberFieldsTrait;

    public function __construct()
    {
        $this->map = new MemberRecordMap();
    }

    public static function getRecordInstance(): AbstractRecord
    {
        return new self();
    }

    public static function getOptionalFields(): array
    {
        return [
            'active_member' => true,
            'country' => true,
            'membership_type' => true,
            'current_status' => true,
            'unit' => true,
            'customer_first_name' => true,
            'customer_last_name' => true,
            'customer_name' => true,
            'customer_phone_numbers' => true,
            'customer_phone_number_primary' => true,
            'customer_id' => true,
        ];
    }

    public function processMembershipType(): void
    {
        if (
            // !$this->isEmpty($this->membership_type)
            // &&
            !$this->isEmpty($this->current_status)
            && $this->isMembershipStatusActive()
        ) {
            $this->active_member = 'Yes';
        } else {
            $this->active_member = null;
        }
    }

    private function isMembershipStatusActive(): bool
    {
        return 0 === strcasecmp('Active', $this->current_status);
    }
}
