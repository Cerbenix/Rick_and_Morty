<?php declare(strict_types=1);

namespace App\Models;

class Location
{
    private int $id;
    private string $name;
    private string $type;
    private string $dimension;
    private array $residentIdList;

    public function __construct(int $id, string $name, string $type, string $dimension, array $residentIdList)
    {

        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->dimension = $dimension;
        $this->residentIdList = $residentIdList;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDimension(): string
    {
        return $this->dimension;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getResidentIdList(): array
    {
        return $this->residentIdList;
    }

    public function getType(): string
    {
        return $this->type;
    }
}