<?php

namespace App\Importers;

use Doctrine\DBAL\Exception;

class MailPackageImporter extends AbstractImporter
{
    /**
     * @throws Exception
     */
    protected function importRecords(): bool
    {
        // Initialize Queries
        $prospectQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('prospect.id')
            ->from('prospect', 'prospect')
            ->where('prospect.company_id = :companyId')
            ->andWhere('prospect.external_id = :prospectExternalId')
            ->orderBy('prospect.id', 'ASC')
            ->setMaxResults(1);

        $mailPackageQuery = $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('mailPackage.id')
            ->from('mail_package', 'mailPackage')
            ->where('mailPackage.prospect_id = :prospectId')
            ->andWhere('mailPackage.name = :name')
            ->orderBy('mailPackage.id', 'ASC')
            ->setMaxResults(1);

        foreach ($this->records as $record) {
            if (empty($record->prospectId)) {
                $record->prospectId = $prospectQuery->setParameters([
                    'companyId' => $record->companyId,
                    'prospectExternalId' => $record->prospect?->externalId
                ])->fetchOne();
            }

            if (
                empty($record->_id) &&
                $record->isValid()
            ) {
                $record->_id = $mailPackageQuery->setParameters([
                    'prospectId' => $record->prospectId,
                    'name' => $record->name
                ])->fetchOne();
            }

            $this->saveRecord($record);
        }

        return true;
    }

    protected function countRecords(): int
    {
        return $this->entityManager->getConnection()
            ->createQueryBuilder()
            ->select('COUNT(mailPackage.id)')
            ->from('mail_package', 'mailPackage')
            ->fetchOne();
    }
}
