<?php

namespace App\ValueObject\Roster;

use App\Entity\BusinessRole;

class RosterRole
{
    private const array CONTACT_TYPE_MAP = [
        'Finance/Back Office' => BusinessRole::ROLE_FINANCE_BACK_OFFICE,
        'Manager' => BusinessRole::ROLE_MANAGER,
        'Owner/GM' => BusinessRole::ROLE_OWNER_GM,
        'Marketing' => BusinessRole::ROLE_SALES,
        'HR/Recruiting' => BusinessRole::ROLE_HR_RECRUITING,
    ];

    private const array TITLE_MAP = [
        BusinessRole::ROLE_OWNER_GM => [
            'Owner', 'President', 'CFO', 'General Manager', 'GM/Co-Owner', 'CEO',
            'Owner/Master Plumber', 'President / partner', 'COO', 'Owner/Manager',
            'Co-Owner', 'Partner Owner', 'Founder/Owner', 'Owner/Chief Executive Officer',
            'Owner/CFO', 'Owner/Secretary', 'Owner/Board Member', 'Owner/Partner',
            'Vice President', 'President/Owner', 'GM', 'GM/Owner', 'Partner', 'OWNER',
            'COO/ President', 'Owner/CEO', 'Vice-President', 'Owner & Office Manager',
            'Member/Manager', 'Queen', 'Spouse', 'Wife', 'Executive Assistant',
        ],
        BusinessRole::ROLE_MANAGER => [
            'Operations Manager', 'Director of Operations', 'Service Manager',
            'Residential Service Dept Mgr/GM', 'Residential HVAC Svc Mgr', 'Manager',
            'Production Manager', 'Team Lead & Tech Mentor', 'Operations', 'Installation Manager',
            'Install Manager', 'Field Manager', 'Field Supervisor', 'Team Manager', 'Senior Manager',
            'Strategic Advisor', 'Operations Mgr', 'Ops Manager', 'Operation Manager',
            'VP of Operations', 'HVAC Manager', 'Plumbing Manager', 'Electrical Manager',
            'Project Manager', 'Repair Manager', 'Training Manager', 'Business Manager',
            'Warehouse Manager', 'MANAGER', 'TEAM LEAD', 'Team Lead', 'manager',
        ],
        BusinessRole::ROLE_HR_RECRUITING => [
            'HR Director', 'Human Resource Director', 'Recruiter', 'HR', 'HR Manager',
            'HR/Recruiting', 'Human Resources', 'Recruiting Director', 'People Resources Coordinator',
            'HR/Finance', 'HR / Corperate Treasurer', 'Recruiting Asst', 'Academy Coordinator',
        ],
        BusinessRole::ROLE_FINANCE_BACK_OFFICE => [
            'Controller', 'Bookkeeper', 'A/R Supervisor', 'Accounting', 'AP', 'AR',
            'Office Manager', 'Office Administrator', 'Accountant', 'Executive Bookkeeper',
            'Finance Manager', 'Chief Financial Officer', 'Accounting Manager', 'Staff Accountant',
            'Administrative & Finance Manager', 'Finance', 'Finance Executive', 'Comptroller',
            'Office Administration', 'Office Admin', 'Office Assistant', 'Office Leader/Operations',
            'Administrative Coordinator', 'Admin Manager', 'Office Manager/HR', 'Office/Finance Manager',
            'Office / Finance Manager', 'Administration Manager', 'A/P Clerk', 'Accounts Payable',
            'Administrative Assistant', 'Admin Asst', 'Accounting Team Lead', 'Office',
        ],
        BusinessRole::ROLE_TECHNICIAN => [
            'Service Technician', 'Tech', 'Technician', 'Service Tech/Manager', 'Plumbing Technician',
            'HVAC Technician', 'Lead Technician', 'Lead Technician/ Service Manager', 'Heart Technician',
            'Lead Service Technician', 'Lead Installer', 'tech', 'hvac teach', 'Electrician',
            'Plumber', 'Apprentice Electrician', 'Lead Electrician', 'HVAC Tech', 'Installer',
            'Septic Coach', 'Dig & Drain Captain', 'technician', 'Daughter/Tech', 'Roofing Specialist',
        ],
        BusinessRole::ROLE_CALL_CENTER => [
            'CSR', 'CSR/Administration', 'Customer Car Representative', 'Dispatch/CCR/Accounting Assistant',
            'CSR/DISPATCHER', 'Dispatcher', 'Customer Experience Coordinator', 'Receptionist', 'CCR',
            'Dispatch', 'Customer Service Representative', 'CSR Manager', 'Customer Service Manager',
            'CSR/Dispatch', 'CCR/Dispatcher', 'Dispatch manager', 'Client Care Manager', 'CCM',
            'Lobby Receptionist', 'Customer Service Mgr', 'Client Experience Manager', 'CSR MGR',
            'CSR Assistant MGR', 'Call Center Manager', 'Customer & Field Experience Specialist',
        ],
        BusinessRole::ROLE_SALES => [
            'Sales Manager', 'Roofing Advisor', 'Sales', 'Sales Tech',
            'Comfort Adviser', 'Comfort Advisor', 'VP of Sales', 'Sales/Training', 'sales',
            'HVAC Sales/Service', 'Sales and Marketing', 'Sales Trainer',
            'Sales & Marketing Manager',
            'Sales & Marketing',
            'Sales and Marketing',
        ],
        BusinessRole::ROLE_MARKETING => [
            // intentionally left blank
            // only Stochastic Employees
            // should be assigned this Role
            // it's an internal "Marketing Operations" Role
        ],
    ];

    private string $internalName;

    private function __construct(
        RosterEmployee $rosterEmployee,
    ) {
        $this->internalName = $this->getMappedInternalName(
            $rosterEmployee,
        );
    }

    public static function fromRosterEmployee(
        RosterEmployee $employee,
    ): self {
        return new self($employee);
    }

    private function getMappedInternalName(
        RosterEmployee $rosterEmployee,
    ): string {
        $employeeTitle = $rosterEmployee->getTitle() ?? 'NO TITLE';
        $employeeTitleLower = strtolower($employeeTitle);

        $foundInternalName = BusinessRole::ROLE_OWNER_GM;

        foreach (self::TITLE_MAP as $roleType => $titleList) {
            foreach ($titleList as $title) {
                if (strtolower($title) === $employeeTitleLower) {
                    return $roleType;
                }
            }
        }

        if (!$this->isEmpty($rosterEmployee->getContactType())) {
            return self::CONTACT_TYPE_MAP[$rosterEmployee->getContactType()];
        }

        return $foundInternalName;
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    private function isEmpty(string $string): bool
    {
        if ($string) {
            $string = trim($string);
        }

        return empty($string);
    }
}
