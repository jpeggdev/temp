<?php

namespace App\DTO;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\User;

class LoggedInUserDTO
{
    private User $user;
    private Employee $activeEmployee;
    private Company $activeCompany;

    public function __construct(User $user, Employee $activeEmployee, Company $activeCompany)
    {
        $this->user = $user;
        $this->activeEmployee = $activeEmployee;
        $this->activeCompany = $activeCompany;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getActiveEmployee(): Employee
    {
        return $this->activeEmployee;
    }

    public function getActiveCompany(): Company
    {
        return $this->activeCompany;
    }
}
