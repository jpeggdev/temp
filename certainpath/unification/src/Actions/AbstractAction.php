<?php

namespace App\Actions;

use App\ValueObjects\{
    AbstractObject,
    CustomerObject,
};
use App\AbstractMigrator;

use function App\Functions\{app_nullify, app_getDecimal, app_upper};

abstract class AbstractAction extends AbstractMigrator
{
    protected array $objectClasses = [
        'C' => CustomerObject::class,
    ];
    protected int $updateCount = 0;
    private array $output = [];
    protected const DEFAULT_MONETIZE_SCALE = 2;

    public function execute(): self
    {
        $startTime = microtime(true);

        // Get Initial Record Count
        $this->startCount = $this->countRecords();

        $this->initialize([]);
        $this->output = [];

        $db = $this->entityManager->getConnection();
        $db->beginTransaction();

        $this->executeAction();

        if ($this->dryRun) {
            $db->rollBack();
        } else {
            $db->commit();
        }

        // Get Final Record Count
        $this->endCount = $this->countRecords();

        $this->runTime = app_getDecimal((
            microtime(true) - $startTime
        ));

        return $this;
    }

    /**
     * Get the output array
     *
     * @return array
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * Execute Action
     *
     * @return boolean
     */
    abstract protected function executeAction(): bool;

    /**
     * Append any additional output to print when the action is complete
     *
     * @param string $string
     * @return void
     */
    protected function appendOutput(string $string)
    {
        $this->output[] = $string;
    }

    protected function getObject($objectNumber): ?AbstractObject
    {
        if ($class = $this->getRecordClass($objectNumber)) {
            $record = new $class();
        }

        return null;
    }

    /**
     * Get RecordNumber
     *
     * @return string|null
     */
    private function getRecordNumber(string $objectNumber): ?string
    {
        return app_nullify(preg_replace('/[^0-9]/', '', $objectNumber));
    }

    /**
     * Get RecordClass
     *
     * @return string|null
     */
    private function getRecordClass(string $objectNumber = null): ?string
    {
        $prefix = app_upper(preg_replace('/[^A-z]/', '', $objectNumber));

        if (array_key_exists($prefix, $this->objectClasses)) {
            return $this->objectClasses[$prefix];
        }

        return null;
    }

    /**
     * Count records
     *
     * @return integer
     */
    abstract public function countRecords(): int;
}
