<?php

namespace App\Exporters\MailManager;

use App\Entity\Prospect;
use App\Exceptions\FileConverterException;
use App\Exporters\AbstractExporter;
use App\ValueObjects\TempFile;
use JsonException;

class ProspectExporter extends AbstractExporter
{
    /**
     * @throws FileConverterException
     * @throws JsonException
     */
    protected function exportRecords(array $data): ?string
    {
        $results = array_map(function (Prospect $prospect) {
            //$result = json_decode($prospect->getJson(), true, 512, JSON_THROW_ON_ERROR);
            $result = [
                'full_name' => $prospect->getFullName(),
                'first_name' => $prospect->getFirstName(),
                'last_name' => $prospect->getLastName(),
                'dlvryaddrs' => $prospect->getAddress1(),
                'city' => $prospect->getCity(),
                'state' => $prospect->getState(),
                'zip4' => $prospect->getPostalCode(),
                'deleted' => $prospect->isDeleted(),
            ];

            $result = array_merge(
                array_fill_keys($this->getHeader(), null),
                array_change_key_case($result, CASE_UPPER)
            );

            return array_intersect_key(
                $result,
                array_flip($this->getHeader())
            );
        }, $data);

        $tempPath = $this->fileWriter->generateFilePath(
            $this->fileWriter->getTemporaryDataPath(),
            md5(serialize($results)) . '.csv',
            'exports/' . $this->getCompanyObject()?->identifier
        );

        $filePointer = $this->getFileResource($tempPath, 'w');
        fputcsv($filePointer, $this->getHeader());

        foreach ($results as $key => $record) {
            ++$this->exportCount;
            fputcsv($filePointer, $record);
        }

        $tempCsvFile = TempFile::fromFullPath(
            $tempPath
        );
        $this->fileClient->upload(
            'stochastic-files',
            $tempCsvFile
        );

        if (
            file_exists($tempPath) &&
            filesize($tempPath) > 0
        ) {
            return $this->fileConverter->convertToDbase($tempPath);
        }

        return null;
    }

    protected function getExportType(): string
    {
        return 'Prospects';
    }

    protected function getHeader(): array
    {
        return [
            'FULL_NAME',
            'DLVRYADDRS',
            'ALTRNT1ADD',
            'CITY',
            'STATE',
            'ZIP4',
            'HOMEOWNER',
            'REGION',
            'VERIFIED',
            'PHONEFLAG',
            'INFOBASE',
            'MAILORDER',
            'AREACODE',
            'TELEPHONE',
            'TRCKNGCD',
            'ADDRSSTYP',
            'YEAR_BUILT',
            'AGE',
            'PHNNSPC',
            'DTFLLD',
            'CRRT',
            'DLVRYPNT',
            'LOT',
            'FOOTNOTE',
            'RTRNCD',
            'ANKLNKRTRN',
            'CMTCHFLG',
            'CMVDT',
            'CMVTYP',
            'LCSLNKRTRN',
            'NCLNKRTRNC',
            'STLNKRTRNC',
            'DPV_CMRA',
            'DPVCNFRMTN',
            'DPVNSTT',
            'DPV_VACANT',
            'PREFIX',
            'FIRST_NAME',
            'LAST_NAME',
            'MIDDLE',
            'LATITUDE',
            'LONGITUDE',
            'PKGMLDT1',
            'PKGMLDT2',
            'PKGMLDT3',
            'PKGMLDT4',
            'PKGMLDT5',
            'PKGMLDT6',
            'PKGMLDT7',
            'PKGMLDT8',
            'PKGMLDT9',
            'PKGMLDT10',
            'PKGMLDT11',
            'PKGMLDT12',
            'WLKSQNC',
            'BSNSSRSDNT',
            'CHCKDGT',
            'CDSFPRCSSN',
            'PHONE1',
            'INVCDT',
            'INVCNMBR',
            'RVNTYP',
            'SLAMNT',
            'SALES_REP',
            'CLBMMBR',
            'PKGMLDT13',
            'PKGMLDT15',
            'PKGMLDT14',
            'PURGE',
            'PKGMLDTA1',
            'PKGMLDTA2',
            'PKGMLDTA3',
            'PKGMLDTA4',
            'PKGMLDTA5',
            'PKGMLDTA6',
            'PKGMLDTA7',
            'PKGMLDTA8',
            'PKGMLDTA9',
            'PKGMLDTA10',
            'PKGMLDTA11',
            'PKGMLDTA12',
            'LIFETRAN',
            'LIFEVAL',
            'PKGMLDTB1',
            'PKGMLDTB2',
            'PKGMLDTB3',
            'PKGMLDTB4',
            'PKGMLDTB5',
            'PKGMLDTB6',
            'PKGMLDTB7',
            'PKGMLDTB8',
            'PKGMLDTB9',
            'PKGMLDTB10',
            'PKGMLDTB11',
            'PKGMLDTB12',
            'COMPANY',
        ];
    }
}
