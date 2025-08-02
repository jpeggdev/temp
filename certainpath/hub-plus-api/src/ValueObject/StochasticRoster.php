<?php

namespace App\ValueObject;

use App\DTO\StochasticRosterDTO;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\XLSX\Reader;

class StochasticRoster
{
    private array $roster = [];

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function __construct(
        string $excelFile,
    ) {
        $reader = new Reader();
        $reader->open($excelFile);
        foreach ($reader->getSheetIterator() as $sheet) {
            $rowCounter = 1;
            foreach ($sheet->getRowIterator() as $row) {
                if (1 === $rowCounter) {
                    ++$rowCounter;
                    continue;
                }
                $cells = $row->getCells();
                $dto = new StochasticRosterDTO();
                $dto->fileName = (string) $cells[0]->getValue();
                $dto->owner = (string) $cells[1]->getValue();
                $dto->membershipNumber = (string) $cells[3]->getValue();
                $dto->status = (string) $cells[4]->getValue();
                $dto->account = (string) $cells[5]->getValue();
                $dto->intacctId = (string) $cells[6]->getValue();
                $dto->location = (string) $cells[7]->getValue();
                $dto->lastMailedYear = (string) $cells[8]->getValue();
                $dto->yearToDateSpend = (string) $cells[9]->getValue();
                $dto->yearToDateSpendDateStamp = (string) $cells[10]->getValue();
                $dto->clientId = (string) $cells[11]->getValue();
                $dto->agency = (string) $cells[12]->getValue();
                $this->roster[] = $dto;
            }
        }
    }

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public static function fromExcelFile(string $excelFile): self
    {
        return new self($excelFile);
    }

    /**
     * @return StochasticRosterDTO[]
     */
    public function getRoster(): array
    {
        return $this->roster;
    }
}
