<?php

namespace App\Tests\Repository;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Customer;
use App\Tests\FunctionalTestCase;

class AddressRepositoryTest extends FunctionalTestCase
{
    public function testFindBySimilarExternalId(): void
    {
        $customer = $this->getCustomerRepository()->save($this->getCustomer());
        $address = $this->getAddressRepository()->save($this->getAddress());
    }

    private function getAddress(): Address
    {
        return (new Address())
            ->setExternalId('51664scenicctnewbaltimoremi48051')
            ->setAddress1('51664 SCENIC CT')
            ->setCity('NEW BALTIMORE')
            ->setStateCode('MI')
            ->setPostalCode('48051')
            ->setCountryCode('USA')
            ->setCompany($this->getCompany())
        ;
    }

    private function getCompany(): Company
    {
        return $this->getCompanyRepository()->findActiveByIdentifierOrCreate('UNI1');
    }

    private function getCustomer(): Customer
    {
        return (new Customer())
            ->setName('ANAND, ABHILASH')
            ->setCompany($this->getCompany())
        ;
    }
}
