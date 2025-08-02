<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private function isDocker(): bool
    {
        return is_file('/.dockerenv');
    }

    public function getCacheDir(): string
    {
        if ('dev' === $this->getEnvironment() && $this->isDocker()) {
            return '/dev/shm/cache/'.$this->getEnvironment();
        }

        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        if ('dev' === $this->getEnvironment() && $this->isDocker()) {
            return '/dev/shm/log';
        }

        return parent::getLogDir();
    }
}
