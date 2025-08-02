<?php

namespace App\Services;

class ConfigurationAccessor
{
    public function get(string $name): string
    {
        return (string) $_ENV[$name];
    }
}