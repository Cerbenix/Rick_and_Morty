<?php declare(strict_types=1);

namespace App\Models;

class Episode
{
    private int $ID;
    private string $name;
    private string $episode;

    public function __construct(int $ID, string $name, string $episode)
    {
        $this->ID = $ID;
        $this->name = $name;
        $this->episode = $episode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getID(): int
    {
        return $this->ID;
    }

    public function getEpisode(): string
    {
        return $this->episode;
    }
}
