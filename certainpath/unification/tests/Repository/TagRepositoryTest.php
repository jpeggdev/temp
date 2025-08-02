<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Tag;
use App\Tests\FunctionalTestCase;

class TagRepositoryTest extends FunctionalTestCase
{
    public function testSaveTag(): void
    {
        $this->getTagRepository()->save($this->getTag());
        $this->assertCount(1, $this->getTagRepository()->findAll());
    }

    private function getCompany(): Company
    {
        return $this->getCompanyRepository()->findOneBy([
            'identifier' => 'UNI1'
        ]);
    }

    private function getTag(): Tag
    {
        return (new Tag())
            ->setName($this->faker->word())
            ->setDescription($this->faker->text())
            ->setCompany($this->getCompany());
    }
}
