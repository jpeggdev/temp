<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class UserFixture extends Fixture
{
    public const string USER_REFERENCE_1 = 'user-1';
    public const string USER_REFERENCE_2 = 'user-2';
    public const string USER_REFERENCE_3 = 'user-3';
    public const string USER_REFERENCE_4 = 'user-4';
    public const string USER_REFERENCE_5 = 'user-5';
    public const string USER_REFERENCE_6 = 'user-6';
    public const string USER_REFERENCE_7 = 'user-7';
    public const string USER_REFERENCE_8 = 'user-8';

    public const string USER_REFERENCE_9 = 'user-9';

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('crawmer@crc-inc.com');
        $user->setFirstName('Josh');
        $user->setLastName('Crawmer');
        $user->setSsoId('auth0|6318f9ed9942a8ee8e4aa7dd');
        $user->setUuid(Uuid::uuid4()->toString());
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_1, $user);

        $user = new User();
        $user->setEmail('joshcrawmer4@yahoo.com');
        $user->setFirstName('Josh');
        $user->setLastName('Crawmer');
        $user->setSsoId('auth0|63eebead44bfc55c7c6ec992');
        $user->setUuid(Uuid::uuid4()->toString());
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_2, $user);

        $user = new User();
        $user->setEmail('mswaim@mycertainpath.com');
        $user->setFirstName('Matthew');
        $user->setLastName('Swaim');
        $user->setSsoId('auth0|6319ebc3b49b0e07587e2a04');
        $user->setUuid(Uuid::uuid4()->toString());
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_3, $user);

        $user = new User();
        $user->setEmail('mpatten@mycertainpath.com');
        $user->setFirstName('Michael');
        $user->setLastName('Patten');
        $user->setSsoId('auth0|6451047d4fa433df0aa4c38b');

        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_4, $user);

        $user = new User();
        $user->setEmail('cholland@mycertainpath.com');
        $user->setFirstName('Chris');
        $user->setLastName('Holland');
        $user->setSsoId('auth0|64fc0a19202dfe7958f6469e');

        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_5, $user);

        $user = new User();
        $user->setEmail('lbalandin@mycertainpath.com');
        $user->setFirstName('Leo');
        $user->setLastName('Balandin');
        $user->setSsoId('auth0|650880f471aa8fef7a0647e1');

        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_6, $user);

        $user = new User();
        $user->setEmail('kharrington@mycertainpath.com');
        $user->setFirstName('Keeton');
        $user->setLastName('Harrington');
        $user->setSsoId('auth0|61fd47d771902e00702612c4');

        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_7, $user);

        $user = new User();
        $user->setEmail('rragusa@momentum3.biz');
        $user->setFirstName('Ryan');
        $user->setLastName('Ragusa');
        $user->setSsoId('auth0|673239fd3ba2d0439cec1ed4');
        $user->setUuid(Uuid::uuid4()->toString());
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_8, $user);

        $user = new User();
        $user->setEmail('jpegg@momentum3.biz');
        $user->setFirstName('Jeff');
        $user->setLastName('Pegg');
        $user->setSsoId('auth0|6813938de2ff401e1f9f5f8a');
        $user->setUuid(Uuid::uuid4()->toString());
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $this->addReference(self::USER_REFERENCE_9, $user);

        $manager->flush();
    }
}
