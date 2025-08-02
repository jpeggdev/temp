<?php

namespace App\Module\CraftMigration\DTO\Elements;

class TagDTO
{
    public int $id;
    public string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public static function fromArray(array $data): self
    {
        return new self($data['id'], $data['name']);
    }
}
