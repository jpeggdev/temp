<?php

namespace App\Repository;

use App\Entity\CampaignProduct;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\ValueObject\CampaignProductTaxonomy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

/**
 * @extends ServiceEntityRepository<CampaignProduct>
 */
class CampaignProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampaignProduct::class);
    }

    /**
     * @return CampaignProduct[]
     */
    public function fetchCampaignProducts(): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('cp')
            ->from(CampaignProduct::class, 'cp')
            ->where('cp.isActive = true')
            ->orderBy('cp.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function saveCampaignProduct(CampaignProduct $campaignProduct): CampaignProduct
    {
        $this->persistCampaignProduct($campaignProduct);
        $this->flushEntityManager();

        return $campaignProduct;
    }

    public function deactivateCampaignProduct(CampaignProduct $campaignProduct): CampaignProduct
    {
        $campaignProduct->setIsActive(false);

        return $this->saveCampaignProduct($campaignProduct);
    }

    public function removeCampaignProduct(CampaignProduct $campaignProduct): void
    {
        $this->getEntityManager()->remove($campaignProduct);
        $this->flushEntityManager();
    }

    public function persistCampaignProduct(CampaignProduct $campaignProduct): void
    {
        $this->getEntityManager()->persist($campaignProduct);
    }

    public function flushEntityManager(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findOneById(int $id): ?CampaignProduct
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('cp')
            ->from(CampaignProduct::class, 'cp')
            ->where('cp.id = :id')
            ->orderBy('cp.name', 'ASC')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneActiveById(int $id): ?CampaignProduct
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('cp')
            ->from(CampaignProduct::class, 'cp')
            ->where('cp.id = :id')
            ->andWhere('cp.isActive = true')
            ->orderBy('cp.name', 'ASC')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCampaignProduct(CampaignProduct $campaignProduct): ?CampaignProduct
    {
        return $this->findOneBy(
            [
                'name' => $campaignProduct->getName(),
            ]
        );
    }

    /**
     * @throws IOException
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws Exception
     * @throws NoFilePathWasProvided
     */
    public function initializeCampaignProducts(
        ?string $csvFile = null
    ): void {
        $taxonomy =
            $csvFile
            ?
            CampaignProductTaxonomy::fromCsv($csvFile)
            :
            new CampaignProductTaxonomy();
        foreach ($taxonomy->getTaxonomyForInitialization() as $product) {
            $campaignProduct = new CampaignProduct();
            $campaignProduct->setName($product['name']);

            if ($found = $this->getCampaignProduct($campaignProduct)) {
                $campaignProduct = $found;
            }

            $campaignProduct->setType($product['type']);
            $campaignProduct->setDescription($product['description']);
            $campaignProduct->setCategory($product['category']);
            $campaignProduct->setSubCategory($product['subCategory']);
            $campaignProduct->setFormat($product['format']);
            $campaignProduct->setProspectPrice($product['prospectPrice']);
            $campaignProduct->setCustomerPrice($product['customerPrice']);
            $campaignProduct->setMailerDescription($product['mailerDescription']);
            $campaignProduct->setCode($product['code']);
            $campaignProduct->setHasColoredStock($product['hasColoredStock']);
            $campaignProduct->setBrand($product['brand']);
            $campaignProduct->setSize($product['size']);
            $campaignProduct->setDistributionMethod($product['distributionMethod']);
            $campaignProduct->setTargetAudience($product['targetAudience']);

            $this->persistCampaignProduct($campaignProduct);
        }

        $this->flushEntityManager();
    }
}
