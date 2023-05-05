<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Models\Episode;
use GuzzleHttp\Client;

class ApiClient
{
    private Client $apiClient;

    public function __construct()
    {
        $this->apiClient = new Client([
            'base_uri' => 'https://rickandmortyapi.com'
        ]);
    }

    public function fetchMainCharacters(): array
    {
        $response = $this->apiClient->get('api/character');
        $report = json_decode($response->getBody()->getContents());
        return $this->collectCharacters($report);
    }

    public function fetchCharacter(string $characterId): Character
    {
        $response = $this->apiClient->get('api/character/' . $characterId);
        $report = json_decode($response->getBody()->getContents());
        return $this->createCharacter($report);
    }

    public function fetchSearched(
        string $name = '',
        string $status = '',
        string $species = '',
        string $type = '',
        string $gender = ''
    ): ?array
    {
        $query = [
            !empty($name) ? 'name' : '' => $name,
            !empty($status) ? 'status' : '' => $status,
            !empty($species) ? 'species' : '' => $species,
            !empty($type) ? 'type' : '' => $type,
            !empty($gender) ? 'gender' : '' => $gender,
        ];

        $params = [
            'query' => array_filter($query)
        ];
        try {
            $response = $this->apiClient->get('api/character', $params);
            $report = json_decode($response->getBody()->getContents());
            return $this->collectCharacters($report);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return null;
            }
        }
        return null;
    }

    public function associateEpisodeName(array $characterList): array
    {
        $episodes = $this->fetchEpisodes($this->createEpisodeIdList($characterList));
        foreach ($characterList as $character) {
            foreach ($episodes as $episode) {
                if ($character->getEpisodeIdList()[0] == $episode->getID()) {
                    $character->setEpisodeName($episode->getName());
                }
            }
        }
        return $characterList;
    }

    public function fetchEpisodes(array $episodeIdList): array
    {
        $endpoint = 'api/episode/' . implode(',', array_unique($episodeIdList));
        $response = $this->apiClient->get($endpoint);
        $report = (object)json_decode($response->getBody()->getContents());
        return $this->collectEpisodes($report);
    }

    private function createEpisodeIdList(array $characterList): array
    {
        $episodeIdList = [];
        foreach ($characterList as $character) {
            $episodeIdList[] = $character->getEpisodeIdList()[0];
        }
        return $episodeIdList;
    }

    private function collectEpisodes(\stdClass $episodeReport): array
    {
        $episodeCollection = [];
        if (!isset($episodeReport->id)) {
            foreach ($episodeReport as $episode) {
                $episodeCollection[] = new Episode($episode->id, $episode->name, $episode->episode);
            }
        } else {
            $episodeCollection[] = new Episode($episodeReport->id, $episodeReport->name, $episodeReport->episode);
        }
        return $episodeCollection;
    }

    private function collectCharacters(\stdClass $characterListReport): array
    {
        $characterCollection = [];
        foreach ($characterListReport->results as $character) {
            $characterCollection[] = $this->createCharacter($character);
        }
        return $characterCollection;
    }

    private function createCharacter(\stdClass $characterReport): Character
    {
        $episodeIdList = [];
        foreach ($characterReport->episode as $episode) {
            $episodeIdList[] = preg_replace('/[^0-9]/', '', $episode);
        }
        return new Character(
            $characterReport->id,
            $characterReport->name,
            $characterReport->url,
            $characterReport->status,
            $characterReport->species,
            $characterReport->location->name,
            $episodeIdList,
            $characterReport->image
        );
    }
}