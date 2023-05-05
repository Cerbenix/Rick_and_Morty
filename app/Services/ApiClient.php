<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Models\Episode;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;

class ApiClient
{
    private Client $apiClient;

    public function __construct()
    {
        $this->apiClient = new Client([
            'base_uri' => 'https://rickandmortyapi.com'
        ]);
    }

    public function fetchCharacters(): array
    {
        $response = $this->apiClient->get('api/character');
        $report = json_decode($response->getBody()->getContents());
        return $this->collectCharacters($report);
    }

    public function fetchCharacter(string $id): Character
    {
        $response = $this->apiClient->get('api/character/' . $id);
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

    public function associateEpisodeName(array $characters): array
    {
        $episodes = $this->fetchEpisodes($this->createEpisodeIdList($characters));
        foreach ($characters as $character) {
            foreach ($episodes as $episode) {
                if ($character->getEpisodeIdList()[0] == $episode->getID()) {
                    $character->setEpisodeName($episode->getName());
                }
            }
        }
        return $characters;
    }

    public function createSingletonEpisodeIdList(Character $character): array
    {
        $episodeIdList = [];
        $episodeCount = count($character->getEpisodeIdList());
        for ($index = 0; $index < $episodeCount; $index++) {
            $episodeIdList[] = $character->getEpisodeIdList()[$index];
        }
        return $episodeIdList;
    }

    public function fetchEpisodes(array $episodeIdList): array
    {
        $endpoint = 'api/episode/' . implode(',', array_unique($episodeIdList));
        $response = $this->apiClient->get($endpoint);
        $report = (object)json_decode($response->getBody()->getContents());
        return $this->collectEpisodes($report);
    }

    private function createEpisodeIdList(array $characters): array
    {
        $episodeIdList = [];
        foreach ($characters as $character) {
            $episodeIdList[] = $character->getEpisodeIdList()[0];
        }
        return $episodeIdList;
    }

    private function collectEpisodes(\stdClass $report): array
    {
        $episodeCollection = [];
        if (!isset($report->id)) {
            foreach ($report as $episode) {
                $episodeCollection[] = new Episode($episode->id, $episode->name, $episode->episode);
            }
        } else {
            $episodeCollection[] = new Episode($report->id, $report->name, $report->episode);
        }
        return $episodeCollection;
    }

    private function collectCharacters(\stdClass $report): array
    {
        $characterCollection = [];
        foreach ($report->results as $character) {
            $characterCollection[] = $this->createCharacter($character);
        }
        return $characterCollection;
    }

    private function createCharacter(\stdClass $character): Character
    {
        $episodeIdList = [];
        foreach ($character->episode as $episode) {
            $episodeIdList[] = preg_replace('/[^0-9]/', '', $episode);
        }
        return new Character(
            $character->id,
            $character->name,
            $character->url,
            $character->status,
            $character->species,
            $character->location->name,
            $episodeIdList,
            $character->image
        );
    }
}