<?php declare(strict_types=1);

namespace App\Models;

class Character
{
    private int $id;
    private string $name;
    private string $status;
    private string $species;
    private string $location;
    private array $episodeIdList;
    private string $image;
    private string $url;
    private ?string $episodeName = null;

    public function __construct(
        int    $id,
        string $name,
        string $url,
        string $status,
        string $species,
        string $location,
        array  $episodeIdList,
        string $image
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->species = $species;
        $this->location = $location;
        $this->episodeIdList = $episodeIdList;
        $this->image = $image;
        $this->url = $url;
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

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getSpecies(): string
    {
        return $this->species;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setEpisodeName(string $episodeName): void
    {
        $this->episodeName = $episodeName;
    }

    public function getEpisodeName(): ?string
    {
        return $this->episodeName;
    }
}