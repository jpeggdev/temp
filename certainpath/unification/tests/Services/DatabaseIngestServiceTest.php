<?php

namespace App\Tests\Services;

use App\Entity\Prospect;
use App\Repository\ProspectRepository;
use App\Tests\FunctionalTestCase;
use Exception;

class DatabaseIngestServiceTest extends FunctionalTestCase
{
    /**
     * @group remoteResources
     */
    public function testPollGenericIngestDatabase(): void
    {
        $repository = $this->getGenericIngestRepository();
        $remoteTables = $repository->getDatabaseTables();
        $this->assertSameSize($repository::DATABASE_TABLES, $remoteTables);

        foreach ($remoteTables as $table) {
            $this->assertContains($table, $repository::DATABASE_TABLES);

            $count = $this->getGenericIngestRepository()->countTable($table);
            $this->assertIsInt($count);
        }
    }

    /**
     * @throws Exception
     * @group remoteResources
     */
    public function testSyncGenericIngestDatabase(): void
    {
        $prospectCount = $this->getProspectRepository()->count();
        $this->assertSame(0, $prospectCount);

        $consumer = $this->getDatabaseConsumer();
        $consumer->setLimit(1);
        $consumer->setDeleteRemote(false);
        $result = $consumer->syncGenericIngestDatabase(true);
        $this->assertTrue($result);

        if ($this->getGenericIngestRepository()->isLocalDatabase()) {
            $prospectCount = $this->getProspectRepository()->count();
            $this->assertSame(2, $prospectCount);

            $tagCount = $this->getTagRepository()->count();
            $this->assertSame(2, $tagCount);

            $prospect = $this->getProspectRepository()->findOneBy([
                'externalId' => 'id.susanmksenich1939eclairedrphoenixaz85022'
            ]);
            $this->assertInstanceOf(Prospect::class, $prospect);

            $prospectTags = $prospect->getTags();
            $this->assertSame(2, $prospectTags->count());

            $prospectDetails = $prospect->getProspectDetails();
            $this->assertSame(
                78,
                $prospectDetails->getAge()
            );
            $this->assertSame(
                'B',
                $prospectDetails->getInfoBase()
            );
            $this->assertSame(
                1995,
                $prospectDetails->getYearBuilt()
            );

            $prospectSource = $prospect->getProspectSources()->first();
            $this->assertSame(
                'app_parsers_genericingest_prospectsstreamprospectparser',
                $prospectSource->getName()
            );
        }
    }

    protected function getProspectRepository(): ProspectRepository
    {
        return $this->getService(
            ProspectRepository::class
        );
    }
}
