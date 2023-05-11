<?php declare(strict_types=1);

namespace App\Models;

class Episode
{
    private int $ID;
    private string $name;
    private string $episode;
    private array $characterIdList;

    public function __construct(int $ID, string $name, string $episode, array $characterIdList)
    {
        $this->ID = $ID;
        $this->name = $name;
        $this->episode = $episode;
        $this->characterIdList = $characterIdList;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->ID;
    }

    public function getEpisode(): string
    {
        return $this->episode;
    }

    public function getCharacterIdList(): array
    {
        return $this->characterIdList;
    }
}
