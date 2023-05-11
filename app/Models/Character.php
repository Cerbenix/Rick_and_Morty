<?php declare(strict_types=1);

namespace App\Models;

class Character
{
    private int $id;
    private string $name;
    private string $status;
    private string $species;
    private string $locationName;
    private array $episodeIdList;
    private string $image;
    private ?string $episodeName = null;
    private int $locationId;

    public function __construct(
        int    $id,
        string $name,
        string $status,
        string $species,
        string $locationName,
        int    $locationId,
        array  $episodeIdList,
        string $image
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->species = $species;
        $this->locationName = $locationName;
        $this->episodeIdList = $episodeIdList;
        $this->image = $image;
        $this->locationId = $locationId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEpisodeIdList(): array
    {
        return $this->episodeIdList;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getLocationName(): string
    {
        return $this->locationName;
    }

    public function getSpecies(): string
    {
        return $this->species;
    }

    public function getStatus(): string
    {
        return $this->status;
    }


    public function setEpisodeName(string $episodeName): void
    {
        $this->episodeName = $episodeName;
    }

    public function getEpisodeName(): ?string
    {
        return $this->episodeName;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }
}