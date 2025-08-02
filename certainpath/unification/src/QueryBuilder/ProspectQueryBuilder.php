<?php

namespace App\QueryBuilder;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\ProspectFilterRuleRepository;
use App\Services\DetailsMetadata\CampaignDetailsMetadataService;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

readonly class ProspectQueryBuilder extends AbstractQueryBuilder
{
    public function __construct(
        EntityManagerInterface $em,
        private ProspectFilterRuleRepository $prospectFilterRuleRepository,
        private CampaignDetailsMetadataService $campaignDetailsMetadataService
    ) {
        parent::__construct($em);
    }

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Prospect::class, 'p');
    }

    protected function createAggregatedProspectsQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select(
                'pAddr.postalCodeShort AS postalCode',
                'COUNT(p.id) AS households',
            )
            ->from(Prospect::class, 'p');
    }

    private function createQueryBuilderWithRelations(): QueryBuilder
    {
        return $this->createBaseQueryBuilder()
            ->addSelect('a', 'c', 'co')
            ->leftJoin('p.addresses', 'a')
            ->leftJoin('p.customer', 'c')
            ->leftJoin('p.company', 'co');
    }

    public function createFetchAllByBatchIdQueryBuilder(
        int $batchId,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilderWithRelations();
        $queryBuilder = $this->applyBatchIdFilter($queryBuilder, $batchId);
        $queryBuilder->orderBy('p.postalCodeShort', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchProspectCitiesQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select("
                DISTINCT CONCAT(
                    UPPER(SUBSTRING(
                        CASE 
                            WHEN a.city IS NOT NULL THEN LOWER(a.city) 
                            ELSE LOWER(p.city) 
                        END, 1, 1
                    )), 
                    SUBSTRING(
                        CASE 
                            WHEN a.city IS NOT NULL THEN LOWER(a.city) 
                            ELSE LOWER(p.city) 
                        END, 2
                    )
                ) AS city
            ")
            ->from(Prospect::class, 'p')
            ->leftJoin('p.addresses', 'a');
    }

    public function createFetchAllByCompanyIdQueryBuilder(
        int $companyId,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilderWithRelations();
        $queryBuilder = $this->applyCompanyIdFilter($queryBuilder, $companyId);
        $queryBuilder->orderBy('p.postalCodeShort', $sortOrder);

        return $queryBuilder;
    }

    public function createGetProspectsForPostProcessingQueryBuilder(
        int $limit = 10,
        string $sortOrder = 'ASC',
        Company $contextCompany = null
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder()
            ->leftJoin('p.customer', 'c')
            ->setMaxResults($limit)
            ->addOrderBy('CASE WHEN p.processedAt IS NULL THEN 0 ELSE 1 END', $sortOrder)
            ->addOrderBy('CASE WHEN p.customer IS NULL THEN 0 ELSE 1 END', $sortOrder)
            ->addOrderBy('p.processedAt', $sortOrder);

        if ($contextCompany) {
            $queryBuilder
                ->andWhere('p.company = :company')
                ->setParameter('company', $contextCompany);
        }

        return $queryBuilder;
    }

    public function prepareFetchProspectCitiesQueryBuilder(string $intacctId): QueryBuilder
    {
        $queryBuilder = $this->createFetchProspectCitiesQueryBuilder();
        return $this->applyCompanyIdentifierFilter($queryBuilder, $intacctId);
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function createFetchAllByProspectFilterRulesDTOQueryBuilder(
        ProspectFilterRulesDTO $dto,
        string $sortOrder = 'ASC',
        int $limit = null,
        int $offset = null
    ): QueryBuilder {
        $sortOrder = $this->sanitizeSortOrder($sortOrder);

        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->innerJoinValidPreferredAddress($queryBuilder, $dto);
        $queryBuilder = $this->applyMostRecentHouseholdMemberFilter($queryBuilder, $dto);
        $queryBuilder = $this->applyValidProspectFilterRules($queryBuilder, $dto);
        $queryBuilder = $this->applyDynamicProspectFilterRules($queryBuilder, $dto);
        $queryBuilder = $this->setLimit($limit, $queryBuilder);
        $queryBuilder = $this->setOffset($offset, $queryBuilder);
        $queryBuilder->orderBy('pAddr.postalCodeShort', $sortOrder);

        return $queryBuilder;
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function createGetProspectsAggregatedDataQueryBuilder(
        ProspectFilterRulesDTO $dto,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $sortOrder = $this->sanitizeSortOrder($sortOrder);

        $queryBuilder = $this->createAggregatedProspectsQueryBuilder();
        $queryBuilder = $this->innerJoinValidPreferredAddress($queryBuilder, $dto);
        $queryBuilder = $this->applyMostRecentHouseholdMemberFilter($queryBuilder, $dto);
        $queryBuilder = $this->applyValidProspectFilterRules($queryBuilder, $dto);
        $queryBuilder = $this->applyDynamicProspectFilterRules($queryBuilder, $dto);

        /**
         * Calculate average sales only if the following rules are applied, otherwise set it to 0:
         * - customers_only
         * - prospects_and_customers
         */
        $this->aliasExists($queryBuilder, 'c')
            ? $queryBuilder->addSelect('AVG(COALESCE(CAST(c.invoiceTotal AS NUMERIC), 0.00)) AS avgSales')
            : $queryBuilder->addSelect('0 AS avgSales');

        $queryBuilder->groupBy('pAddr.postalCodeShort');
        $queryBuilder->orderBy('pAddr.postalCodeShort', $sortOrder);

        return $queryBuilder;
    }

    /**
     * !Important: This method does not return the correct count when the following filters are applied:
     *      - ProspectFilterRulesDTO::postalCodes
     *
     * This is limited by the fact that ProspectFilterRulesDTO::postalCodes requires filtering and limiting
     * by multiple zip codes. One possible solution is to use:
     *      - ProspectRepository::fetchAllByProspectFilterRulesDTO()->count();
     *
     * However, keep in mind that the approach mentioned above may be significantly slower than the current one.
     *
     * @throws ProspectFilterRuleNotFoundException
     */
    public function createGetCountByProspectFilterRulesDTOQueryBuilder(
        ProspectFilterRulesDTO $filterRulesDTO
    ): QueryBuilder {
        $queryBuilder = $this->createFetchAllByProspectFilterRulesDTOQueryBuilder($filterRulesDTO);
        $queryBuilder->resetDQLPart('orderBy');
        $queryBuilder->select('COUNT(p.id)');

        return $queryBuilder;
    }

    /**
     * These filter rules are designed to determine if a prospect qualifies for mailings.
     */
    private function applyValidProspectFilterRules(
        QueryBuilder $queryBuilder,
        ProspectFilterRulesDTO $dto,
    ): QueryBuilder {
        $queryBuilder = $this->applyIsActiveFilter($queryBuilder);
        $queryBuilder = $this->applyIsDeletedFilter($queryBuilder);
        $queryBuilder = $this->applyIsDoNotContactFilter($queryBuilder);
        $queryBuilder = $this->applyIsDoNotMailFilter($queryBuilder);

        /**
         *  When tags are provided, the is_preferred check should not be applied,
         *  as it's intended for global searches and should be skipped when
         *  performing a search by specific import file(s) - tags.
         */
//        if (!$dto->tags) {
//            $queryBuilder = $this->applyIsPreferredFilter($queryBuilder);
//        }

        $queryBuilder->andWhere(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->isNotNull('p.city'),
                $queryBuilder->expr()->isNotNull('p.state'),
                $queryBuilder->expr()->isNotNull('p.postalCode'),
                $queryBuilder->expr()->isNotNull('p.postalCodeShort'),
                $queryBuilder->expr()->gte('LENGTH(p.postalCodeShort)', 5)
            )
        );

        return $queryBuilder;
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    private function applyDynamicProspectFilterRules(
        QueryBuilder $queryBuilder,
        ProspectFilterRulesDTO $filterRulesDTO
    ): QueryBuilder {

        if (!empty($filterRulesDTO->tags)) {
            $queryBuilder = $this->applyTagsFilter(
                $queryBuilder,
                $filterRulesDTO->tags
            );
        }

        if (!empty($filterRulesDTO->locations)) {
            $queryBuilder = $this->applyLocationsFilter(
                $queryBuilder,
                $filterRulesDTO->locations
            );
        }

        if ($filterRulesDTO->intacctId) {
            $queryBuilder = $this->applyCompanyIdentifierFilter(
                $queryBuilder,
                $filterRulesDTO->intacctId
            );
        }

        if ($filterRulesDTO->customerInclusionRule) {
            $queryBuilder = $this->applyCustomerInclusionFilterRule(
                $queryBuilder,
                $filterRulesDTO->customerInclusionRule
            );
        }

        if ($filterRulesDTO->lifetimeValueRule) {
            $queryBuilder = $this->applyCustomerMaxLifetimeValueFilterRule(
                $queryBuilder,
                $filterRulesDTO->lifetimeValueRule,
                $filterRulesDTO->customerInclusionRule
            );
        }

        if ($filterRulesDTO->clubMembersRule) {
            $queryBuilder = $this->applyClubMembersInclusionFilterRule(
                $queryBuilder,
                $filterRulesDTO->clubMembersRule,
                $filterRulesDTO->customerInclusionRule
            );
        }

        if ($filterRulesDTO->installationsRule) {
            $queryBuilder = $this->applyCustomerInstallationsInclusionFilterRule(
                $queryBuilder,
                $filterRulesDTO->installationsRule,
                $filterRulesDTO->customerInclusionRule
            );
        }

        if ($filterRulesDTO->prospectMinAge) {
            $queryBuilder = $this->applyProspectMinAgeFilter(
                $queryBuilder,
                $filterRulesDTO->prospectMinAge,
                $filterRulesDTO->customerInclusionRule
            );
        }

        if ($filterRulesDTO->prospectMaxAge) {
            $queryBuilder = $this->applyProspectMaxAgeFilter(
                $queryBuilder,
                $filterRulesDTO->prospectMaxAge,
                $filterRulesDTO->customerInclusionRule
            );
        }

        if ($filterRulesDTO->minHomeAge) {
            $queryBuilder = $this->applyMinHomeAgeFilter(
                $queryBuilder,
                $filterRulesDTO->minHomeAge,
                $filterRulesDTO->customerInclusionRule
            );
        }

        if ($filterRulesDTO->minEstimatedIncome) {
            $queryBuilder = $this->applyMinEstimatedIncomeFilter(
                $queryBuilder,
                $filterRulesDTO->minEstimatedIncome,
                $filterRulesDTO->customerInclusionRule
            );
        }

        if ($filterRulesDTO->postalCodes) {
            $queryBuilder = $this->applyPostalCodeShortFilter(
                $queryBuilder,
                $filterRulesDTO
            );
        }

        if ($filterRulesDTO->locationPostalCodes) {
            $queryBuilder = $this->applyLocationPostalCodesFilter(
                $queryBuilder,
                $filterRulesDTO->locationPostalCodes
            );
        }

        if (
            in_array($filterRulesDTO->customerInclusionRule, [
                ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE,
                ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE
            ], true)
        ) {
            $queryBuilder = $this->applyIsActiveCustomerFilter(
                $queryBuilder,
                $filterRulesDTO->customerInclusionRule
            );
        }

        return $queryBuilder;
    }

    private function applyIsActiveFilter(
        QueryBuilder $queryBuilder,
        bool $isActive = true
    ): QueryBuilder {
        return $queryBuilder
                ->andWhere('p.isActive = :isActive')
                ->setParameter('isActive', $isActive);
    }

    private function applyIsActiveCustomerFilter(
        QueryBuilder $queryBuilder,
        string $customerInclusionRule,
        bool $isActive = true,
    ): QueryBuilder {
        $condition = 'c.isActive = :isActive';
        $parameters = ['isActive' => $isActive];

        return $this->applyCustomerFilterRule(
            $queryBuilder,
            $customerInclusionRule,
            $condition,
            $parameters
        );
    }

    private function applyIsDeletedFilter(
        QueryBuilder $queryBuilder,
        bool $isDeleted = false
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('p.isDeleted = :isDeleted')
            ->setParameter('isDeleted', $isDeleted);
    }

    private function applyBatchIdFilter(
        QueryBuilder $queryBuilder,
        int $batchId
    ): QueryBuilder {
        return $queryBuilder
            ->innerJoin('p.batches', 'b')
            ->andWhere('b.id = :batchId')
            ->setParameter('batchId', $batchId);
    }

    private function applyCompanyIdFilter(
        QueryBuilder $queryBuilder,
        int $companyId
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('p.company = :companyId')
            ->setParameter('companyId', $companyId);
    }

    private function applyTagsFilter(
        QueryBuilder $queryBuilder,
        array $tags
    ): QueryBuilder {
        return $queryBuilder
            ->innerJoin('p.tags', 't')
            ->andWhere('t.name in (:tags)')
            ->setParameter('tags', $tags);
    }

    private function applyLocationsFilter(
        QueryBuilder $queryBuilder,
        array $locations
    ): QueryBuilder {
        if (!$this->aliasExists($queryBuilder, 'co')) {
            $queryBuilder
                ->innerJoin('p.company', 'co');
        }

        if (!$this->aliasExists($queryBuilder, 'l')) {
            $queryBuilder
                ->innerJoin('co.locations', 'l');
        }

        return $queryBuilder
            ->andWhere('l.id IN (:locations)')
            ->setParameter('locations', $locations);
    }

    private function applyCompanyIdentifierFilter(
        QueryBuilder $queryBuilder,
        string $companyIdentifier
    ): QueryBuilder {
        if (!$this->aliasExists($queryBuilder, 'co')) {
            $queryBuilder
                ->innerJoin('p.company', 'co');
        }

        return $queryBuilder
            ->andWhere('co.identifier = :identifier')
            ->setParameter('identifier', $companyIdentifier);
    }

    private function applyIsPreferredFilter(
        QueryBuilder $queryBuilder,
        bool $isPreferred = true
    ): QueryBuilder {
        $queryBuilder
            ->andWhere('p.isPreferred = :isPreferred')
            ->setParameter('isPreferred', $isPreferred);

        return $queryBuilder;
    }

    private function applyIsDoNotContactFilter(
        QueryBuilder $queryBuilder,
        bool $isDoNotContact = false
    ): QueryBuilder {
        $queryBuilder
            ->andWhere('p.doNotContact = :isDoNotContact')
            ->setParameter('isDoNotContact', $isDoNotContact);

        return $queryBuilder;
    }

    private function applyIsDoNotMailFilter(
        QueryBuilder $queryBuilder,
        bool $isDoNotMail = false
    ): QueryBuilder {
        $queryBuilder
            ->andWhere('p.doNotMail = :isDoNotMail')
            ->setParameter('isDoNotMail', $isDoNotMail);

        return $queryBuilder;
    }

    private function innerJoinValidPreferredAddress(
        QueryBuilder $queryBuilder,
        ProspectFilterRulesDTO $dto,
    ): QueryBuilder {
        $poBoxRegex = '^p\.?\s*o\.?\s*box';

        $expr = $queryBuilder->expr();

        $conditions = [
            $expr->isNotNull('pAddr.city'),
            $expr->isNotNull('pAddr.stateCode'),
            $expr->isNotNull('pAddr.postalCode'),
            $expr->isNotNull('pAddr.postalCodeShort'),
            $expr->isNotNull('pAddr.verifiedAt'),
            $expr->eq('pAddr.isActive', ':isActive'),
            $expr->eq('pAddr.isVacant', ':isVacant'),
            $expr->eq('pAddr.isDoNotMail', ':isDoNotMail'),
            $expr->eq('pAddr.isGlobalDoNotMail', ':isGlobalDoNotMail'),
            $expr->eq('REGEX(pAddr.address1, :poBoxRegex)', ':isPOBox'),
            $expr->gte('LENGTH(pAddr.postalCodeShort)', 5),
        ];

        $addressType = $dto->addressTypeRule;
        if ($addressType === ProspectFilterRuleRegistry::INCLUDE_RESIDENTIAL_ONLY_RULE_VALUE) {
            $conditions[] = $expr->eq('pAddr.isBusiness', ':isBusiness');
            $queryBuilder->setParameter('isBusiness', false);
        } elseif ($addressType === ProspectFilterRuleRegistry::INCLUDE_COMMERCIAL_ONLY_RULE_VALUE) {
            $conditions[] = $expr->eq('pAddr.isBusiness', ':isBusiness');
            $queryBuilder->setParameter('isBusiness', true);
        }

        $queryBuilder
            ->innerJoin(
                'p.preferredAddress',
                'pAddr',
                Join::WITH,
                $expr->andX(...$conditions)
            )
            ->setParameter('isActive', true)
            ->setParameter('isVacant', false)
            ->setParameter('isDoNotMail', false)
            ->setParameter('isGlobalDoNotMail', false)
            ->setParameter('isPOBox', false)
            ->setParameter('poBoxRegex', $poBoxRegex);

        return $queryBuilder;
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    private function applyCustomerInclusionFilterRule(
        QueryBuilder $queryBuilder,
        string $value
    ): QueryBuilder {
        $customerInclusionRule = $this->prospectFilterRuleRepository->findByNameAndValueOrFail(
            ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME,
            $value
        );

        switch ($customerInclusionRule->getValue()) {
            case ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE:
                $queryBuilder = $this->applyIncludeProspectsOnlyFilter($queryBuilder);
                break;
            case ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE:
                $queryBuilder = $this->applyIncludeCustomersOnlyFilter($queryBuilder);
                break;
            case ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE:
                $queryBuilder = $this->applyIncludeProspectsAndCustomersFilter($queryBuilder);
                break;
            default:
                break;
        }

        return $queryBuilder;
    }

    public function applyIncludeProspectsAndCustomersFilter(QueryBuilder $queryBuilder): QueryBuilder
    {
        if (!$this->aliasExists($queryBuilder, 'c')) {
            $queryBuilder->leftJoin('p.customer', 'c');
        }

        return $queryBuilder;
    }

    public function applyIncludeCustomersOnlyFilter(QueryBuilder $queryBuilder): QueryBuilder
    {
        if (!$this->aliasExists($queryBuilder, 'c')) {
            $queryBuilder->innerJoin('p.customer', 'c');
        }

        return $queryBuilder;
    }

    public function applyIncludeProspectsOnlyFilter(QueryBuilder $queryBuilder): QueryBuilder
    {
        return $queryBuilder->andWhere('p.customer IS NULL');
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    private function applyCustomerMaxLifetimeValueFilterRule(
        QueryBuilder $queryBuilder,
        string $ruleName,
        string $customerInclusionRule
    ): QueryBuilder {
        $rule = $this->prospectFilterRuleRepository->findByNameAndValueOrFail(
            ProspectFilterRuleRegistry::CUSTOMER_MAX_LTV_RULE_NAME,
            $ruleName
        );

        $customerMaxLtvFilterCondition = "CAST(c.lifetimeValue AS NUMERIC) < :lifetimeValue";
        $customerMaxLtvFilterParameters = ['lifetimeValue' => (float) $rule->getValue()];

        return $this->applyCustomerFilterRule(
            $queryBuilder,
            $customerInclusionRule,
            $customerMaxLtvFilterCondition,
            $customerMaxLtvFilterParameters
        );
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    private function applyClubMembersInclusionFilterRule(
        QueryBuilder $queryBuilder,
        string $ruleName,
        string $customerInclusionRule
    ): QueryBuilder {
        $rule = $this->prospectFilterRuleRepository->findByNameAndValueOrFail(
            ProspectFilterRuleRegistry::CLUB_MEMBERS_INCLUSION_RULE_NAME,
            $ruleName
        );

        $isHasSubscription = $rule->getValue() === ProspectFilterRuleRegistry::INCLUDE_CLUB_MEMBERS_ONLY_VALUE;
        $subscriptionCondition = 'c.hasSubscription = :isHasSubscription';
        $subscriptionParameters = ['isHasSubscription' => $isHasSubscription];

        return $this->applyCustomerFilterRule(
            $queryBuilder,
            $customerInclusionRule,
            $subscriptionCondition,
            $subscriptionParameters
        );
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    private function applyCustomerInstallationsInclusionFilterRule(
        QueryBuilder $queryBuilder,
        string $ruleValue,
        string $customerInclusionRule
    ): QueryBuilder {
        $this->prospectFilterRuleRepository->findByNameAndValueOrFail(
            ProspectFilterRuleRegistry::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME,
            $ruleValue
        );

        $isHasInstallation = $ruleValue === ProspectFilterRuleRegistry::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_VALUE;
        $customerInstallationCondition = 'c.hasInstallation = :isHasInstallation';
        $customerInstallationParameters = ['isHasInstallation' => $isHasInstallation];

        return $this->applyCustomerFilterRule(
            $queryBuilder,
            $customerInclusionRule,
            $customerInstallationCondition,
            $customerInstallationParameters
        );
    }

    private function applyProspectMinAgeFilter(
        QueryBuilder $queryBuilder,
        int $prospectMinAge,
        string $customerInclusionRule
    ): QueryBuilder {
        $prospectMinAgeCondition = 'pd.age IS NULL OR pd.age >= :minAge';
        $prospectMinAgeParameters = ['minAge' => $prospectMinAge];

        return $this->applyProspectDetailsFilterRule(
            $queryBuilder,
            $customerInclusionRule,
            $prospectMinAgeCondition,
            $prospectMinAgeParameters
        );
    }

    private function applyProspectMaxAgeFilter(
        QueryBuilder $queryBuilder,
        int $prospectMaxAge,
        string $customerInclusionRule
    ): QueryBuilder {
        $prospectMaxAgeCondition = 'pd.age IS NULL OR pd.age <= :maxAge';
        $prospectMaxAgeParameters = ['maxAge' => $prospectMaxAge];

        return $this->applyProspectDetailsFilterRule(
            $queryBuilder,
            $customerInclusionRule,
            $prospectMaxAgeCondition,
            $prospectMaxAgeParameters
        );
    }

    private function applyMinHomeAgeFilter(
        QueryBuilder $queryBuilder,
        int $minHomeAge,
        string $customerInclusionRule
    ): QueryBuilder {
        $currentYear = (int) date('Y');
        $minYearBuilt = $currentYear - $minHomeAge;
        $minHomeAgeCondition = 'pd.yearBuilt IS NULL OR pd.yearBuilt <= :minYearBuilt';
        $minHomeAgeParameters = ['minYearBuilt' => $minYearBuilt];

        return $this->applyProspectDetailsFilterRule(
            $queryBuilder,
            $customerInclusionRule,
            $minHomeAgeCondition,
            $minHomeAgeParameters
        );
    }

    private function applyMinEstimatedIncomeFilter(
        QueryBuilder $queryBuilder,
        ?string $minEstimatedIncome,
        string $customerInclusionRule
    ): QueryBuilder {
        if ($minEstimatedIncome === null) {
            return $queryBuilder;
        }

        $estimatedIncomeValues = array_column(
            CampaignDetailsMetadataService::getMinEstimatedIncomeOptions(),
            'value'
        );

        $index = array_search(
            (int) $minEstimatedIncome,
            $estimatedIncomeValues,
            true
        );

        if ($index === false) {
            return $queryBuilder;
        }

        $estimatedIncomeValues = array_slice($estimatedIncomeValues, $index);
        $minEstimatedIncomeCondition = '
            pd.estimatedIncome IS NULL OR pd.estimatedIncome IN (:estimatedIncomeValues)
        ';
        $minEstimatedIncomeParameters = ['estimatedIncomeValues' => $estimatedIncomeValues];

        return $this->applyProspectDetailsFilterRule(
            $queryBuilder,
            $customerInclusionRule,
            $minEstimatedIncomeCondition,
            $minEstimatedIncomeParameters
        );
    }

    private function applyPostalCodeShortFilter(
        QueryBuilder $queryBuilder,
        ProspectFilterRulesDTO $dto
    ): QueryBuilder {
        if (!$this->aliasExists($queryBuilder, 'pAddr')) {
            $queryBuilder = $this->innerJoinValidPreferredAddress($queryBuilder, $dto);
        }

        $queryBuilder
            ->andWhere('pAddr.postalCodeShort IN (:postalCodesShort)')
            ->setParameter('postalCodesShort', $dto->postalCodes);

        return $queryBuilder;
    }

    private function applyCustomerFilterRule(
        QueryBuilder $queryBuilder,
        string $customerInclusionRule,
        string $condition,
        array $parameters = []
    ): QueryBuilder {
        $joinType = match ($customerInclusionRule) {
            ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE => 'innerJoin',
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE => 'leftJoin',
            default => null,
        };

        if (!$joinType) {
            return $queryBuilder;
        }

        if (!$this->aliasExists($queryBuilder, 'c')) {
            $queryBuilder->$joinType('p.customer', 'c');
        }

        $queryBuilder->andWhere(
            $customerInclusionRule === ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE
                ? $condition
                : $queryBuilder->expr()->orX('c.id IS NULL', $condition)
        );

        foreach ($parameters as $parameter => $value) {
            $queryBuilder->setParameter($parameter, $value);
        }

        return $queryBuilder;
    }

    private function applyProspectDetailsFilterRule(
        QueryBuilder $queryBuilder,
        string $customerInclusionRule,
        string $condition,
        array $parameters = []
    ): QueryBuilder {
        $joinType = match ($customerInclusionRule) {
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE => 'innerJoin',
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE => 'leftJoin',
            default => null,
        };

        if (!$joinType) {
            return $queryBuilder;
        }

        if (!$this->aliasExists($queryBuilder, 'pd')) {
            $queryBuilder->$joinType('p.prospectDetails', 'pd');
        }

        $queryBuilder->andWhere(
            $customerInclusionRule === ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE
                ? $condition
                : $queryBuilder->expr()->orX('pd IS NULL', $condition)
        );

        foreach ($parameters as $parameter => $value) {
            $queryBuilder->setParameter($parameter, $value);
        }

        return $queryBuilder;
    }

    /**
     * This method ensures that only the most recent prospect is included
     * when multiple prospects share the same address.
     */
    private function applyMostRecentHouseholdMemberFilter(QueryBuilder $qb, ProspectFilterRulesDTO $dto): QueryBuilder
    {
        $expr = $qb->expr();
        $em = $qb->getEntityManager();

        $joinCondition = $expr->andX(
            $expr->eq('p2.preferredAddress', 'p.preferredAddress'),
            $expr->gt('p2.id', 'p.id')
        );

        /**
         * If tags are provided, restrict the comparison to prospects that have matching tags
        */
        if (!empty($dto->tags)) {
            $subQb = $em->createQueryBuilder();
            $subQb
                ->select('1')
                ->from(Prospect::class, 'p2_sub')
                ->innerJoin('p2_sub.tags', 'pt')
                ->where('p2_sub = p2')
                ->andWhere($subQb->expr()->in('pt.name', ':tags'));

            $joinCondition->add($expr->exists($subQb->getDQL()));
            $qb->setParameter('tags', $dto->tags);
        }

        $qb->leftJoin(Prospect::class, 'p2', Join::WITH, $joinCondition);
        $qb->andWhere('p2.id IS NULL');

        return $qb;
    }
}
