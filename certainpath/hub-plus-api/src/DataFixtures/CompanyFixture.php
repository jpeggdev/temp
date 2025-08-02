<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

class CompanyFixture extends Fixture
{
    public const string COMPANY_REFERENCE = 'company';
    public const string COMPANY_REFERENCE_1 = 'company-1';
    public const string COMPANY_REFERENCE_2 = 'company-2';

    public function load(ObjectManager $manager): void
    {
        // Create a Faker instance
        $faker = Factory::create();

        // Create and persist the initial companies as before
        $company = new Company();
        $company->setCompanyName('CertainPath');
        $company->setIntacctId('CPA1');
        $company->setSalesforceId('0012E00001jiLhKQAU');
        $company->setCertainPath(true);
        $company->setUuid(Uuid::uuid4()->toString());
        $manager->persist($company);
        $this->addReference(self::COMPANY_REFERENCE, $company);

        $company = new Company();
        $company->setCompanyName('1-800 Plumber + Air & Electric of Amarillo');
        $company->setIntacctId('default2');
        $company->setSalesforceId('0018000001G3LuEAAV');
        $company->setUuid(Uuid::uuid4()->toString());
        $manager->persist($company);
        $this->addReference(self::COMPANY_REFERENCE_1, $company);

        $company = new Company();
        $company->setCompanyName('4 Star Electric Ltd.');
        $company->setIntacctId('ES37438');
        $company->setSalesforceId('0018000001G3LMuAAN');
        $company->setUuid(Uuid::uuid4()->toString());
        $manager->persist($company);
        $this->addReference(self::COMPANY_REFERENCE_2, $company);

        $manager->flush();
    }
}
