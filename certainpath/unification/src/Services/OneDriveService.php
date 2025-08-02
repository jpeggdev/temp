<?php

namespace App\Services;

use App\Entity\Company;
use App\Exceptions\OneDriveException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Promise;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\File;
use Microsoft\Graph\Model\SharingLink;

class OneDriveService
{
    private const DRIVE_ROOT_PATH = '/drive/root';
    private const UNIFICATION_DIRECTORY_PATH = "UNIFICATION";

    public function __construct(
        private readonly Graph $graphClient,
        private readonly string $env,
    ) {
    }

    public function getEnvironment(): string
    {
        return $this->env;
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     * @throws OneDriveException
     */
    public function populateBaseDirectories(): void
    {
        $resource = sprintf(
            '%s:/%s',
            self::DRIVE_ROOT_PATH,
            self::UNIFICATION_DIRECTORY_PATH
        );
        if (!$this->dirExists($resource)) {
            throw new OneDriveException(sprintf(
                '%s does not exist.',
                $resource
            ));
        }

        foreach (['dev', 'prod', 'test'] as $env) {
            $path = sprintf(
                '%s/%s/%s',
                self::DRIVE_ROOT_PATH,
                self::UNIFICATION_DIRECTORY_PATH,
                $env
            );
            if (!$this->dirExists($path)) {
                $this->createDir($resource, $env);
            }
        }
    }

    public function dirExists(string $resource): bool
    {
        try {
            $this->graphClient
                ->createRequest('GET', sprintf(
                    '%s',
                    $resource
                ))
                ->setReturnType(DriveItem::class)
                ->execute();
            return true;
        } catch (ClientException $exception) {
            if ($exception->getCode() === 404) {
                return false;
            }
        }
        return false;
    }

    /**
     * @throws GuzzleException
     * @throws GraphException
     */
    private function createDir(string $resource, string $name): void
    {
        $name = trim($name);
        $target = sprintf(
            '%s/%s',
            $resource,
            $name
        );

        if (!$this->dirExists($target)) {
            $endpoint = sprintf(
                '%s:/children',
                $resource,
            );

            $this->graphClient->createRequest('POST', $endpoint)
            ->attachBody(
                [
                    "name" => sprintf('%s', $name),
                    "folder" => new \stdClass(),
                    "@microsoft.graph.conflictBehavior" => "fail",
                ]
            )
            ->setReturnType(DriveItem::class)
            ->execute();
        }
    }

    public function getDirectoryResourceForCompany(Company $company): string
    {
        return sprintf(
            '%s:/%s/%s/%s',
            static::DRIVE_ROOT_PATH,
            static::UNIFICATION_DIRECTORY_PATH,
            $this->env,
            $company->getIdentifier()
        );
    }

    public function directoryResourceExistsForCompany(Company $company): bool
    {
        return $this->dirExists(
            $this->getDirectoryResourceForCompany($company)
        );
    }

    /**
     * @throws GuzzleException
     * @throws GraphException
     */
    public function createDirectoryResourceForCompany(Company $company): void
    {
        $resource = sprintf(
            '%s:/%s/%s',
            self::DRIVE_ROOT_PATH,
            self::UNIFICATION_DIRECTORY_PATH,
            $this->env
        );

        $this->createDir($resource, $company->getIdentifier());
    }

    /**
    * @return File[]
    * @throws \GuzzleHttp\Exception\GuzzleException
    * @throws \Microsoft\Graph\Exception\GraphException
    */
    public function getAllDocumentsForCompany(Company $company): array
    {
        $endpoint = sprintf(
            '%s:/children',
            $this->getDirectoryResourceForCompany($company)
        );
        $request = $this->graphClient->createCollectionRequest(
            'GET',
            $endpoint
        )
            ->setReturnType(File::class)
            ->setPageSize(100);
        return $request->getPage();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function getFilePermissions(Company $company, string $fileName)
    {

        $endpoint = sprintf(
            '%s/%s:/permissions',
            $this->getDirectoryResourceForCompany($company),
            $fileName
        );

        $request = $this->graphClient->createCollectionRequest(
            'GET',
            $endpoint
        )
            ->setReturnType(\Microsoft\Graph\Model\Permission::class);
        return $request->execute();
    }

    public function getShareLink(Company $company, string $fileName): SharingLink
    {
        $endpoint = sprintf(
            '%s/%s:/createLink',
            $this->getDirectoryResourceForCompany($company),
            $fileName
        );
        return $this->graphClient->createCollectionRequest(
            'POST',
            $endpoint
        )
            ->attachBody(
                [
                    "type" => "edit",
                    "scope" => "anonymous",
                ]
            )
            ->setReturnType(\Microsoft\Graph\Model\SharingLink::class)
            ->execute();
    }

    public function removeDir(string $resource)
    {
        return $this->graphClient->createRequest(
            'DELETE',
            "{$resource}:/$"
        )
            ->execute();
    }

    public function fileExists(Company $company, string $fileName): bool
    {
        $endpoint = sprintf(
            '%s/%s',
            $this->getDirectoryResourceForCompany($company),
            $fileName
        );

        /**
         * @var $driveItem DriveItem
         */
        try {
            $driveItem = $this->graphClient->createRequest(
                'GET',
                $endpoint
            )
                ->setReturnType(DriveItem::class)
                ->execute();
            return $driveItem->getName() === $fileName ? true : false;
        } catch (ClientException $exception) {
            if ($exception->getCode() === 404) {
                return false;
            }
        }
        return false;
    }

    public function copyFile(Company $company, string $fromFile, string $toFile): Promise
    {
        $drive = $this->getDrive($company);
        $endpoint = sprintf(
            '%s:/dmer_templates/%s:/copy?@microsoft.graph.conflictBehavior=rename',
            self::DRIVE_ROOT_PATH,
            $fromFile
        );

        return $this->graphClient->createRequest(
            'POST',
            $endpoint
        )
            ->addHeaders(
                [
                    "Prefer" => "bypass-shared-lock"
                ]
            )
            ->attachBody(
                [
                    "parentReference" => [
                        "id" => $drive->getId(),
                    ],
                    "name" => $toFile,
                    "@microsoft.graph.conflictBehavior" => "rename",
                ]
            )
            ->setReturnType(\Microsoft\Graph\Model\DriveItem::class)
            ->executeAsync();
    }

    public function removeFile(Company $company, string $fileName)
    {
        $endpoint = sprintf(
            '%s/%s',
            $this->getDirectoryResourceForCompany($company),
            $fileName
        );
        return $this->graphClient->createRequest(
            'DELETE',
            $endpoint
        )
            ->execute();
    }

    private function getDrive(Company $company): DriveItem
    {
        $endpoint = sprintf(
            '%s:/%s/%s/%s/',
            self::DRIVE_ROOT_PATH,
            self::UNIFICATION_DIRECTORY_PATH,
            $this->env,
            $company->getIdentifier()
        );

        return $this->graphClient->createRequest(
            'GET',
            $endpoint
        )
            ->setReturnType(DriveItem::class)
            ->execute();
    }
}
