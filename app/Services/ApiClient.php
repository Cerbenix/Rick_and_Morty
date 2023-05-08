<?php declare(strict_types=1);

namespace App\Services;

use App\Cache;
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

    public function fetchCharacters(int $pageNr): array
    {
        $start = ($pageNr - 1) * 20 + 1;
        $end = $pageNr * 20;
        $characterIdList = range($start, $end);
        $characterList = [];
        $allCached = true;
        foreach ($characterIdList as $characterId) {
            if (!Cache::has('character_' . $characterId)) {
                $allCached = false;
                break;
            }
        }
        if (!$allCached) {
            $response = $this->apiClient->get('api/character?page=' . $pageNr);
            $report = json_decode($response->getBody()->getContents());
            foreach ($report->results as $character) {
                Cache::save('character_' . $character->id, json_encode($character));
            }
            $characterList = $this->collectCharacters($report);
        } else {
            foreach ($characterIdList as $characterId) {
                $characterFromCache = json_decode(Cache::get('character_' . $characterId));
                $characterList[] = $this->createCharacter($characterFromCache);
            }
        }
        return $characterList;
    }

    public function fetchCharacter(string $characterId): Character
    {
        if (!Cache::has('character_' . $characterId)) {
            $response = $this->apiClient->get('api/character/' . $characterId);
            $report = json_decode($response->getBody()->getContents());
            Cache::save('character_' . $report->id, json_encode($report));
            return $this->createCharacter($report);
        } else {
            $characterFromCache = json_decode(Cache::get('character_' . $characterId));
            return $this->createCharacter($characterFromCache);
        }
    }

    public function fetchSearched(
        string $name = '',
        string $status = '',
        string $species = '',
        string $type = '',
        string $gender = ''
    ): array
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
            foreach ($report->results as $character) {
                Cache::save('character_' . $character->id, json_encode($character));
            }
            return $this->collectCharacters($report);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return [];
            }
        }
        return [];
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
        $unCachedEpisodeIdList = [];
        $episodeList = [];

        foreach ($episodeIdList as $episodeId) {
            if (!Cache::has('episode_' . $episodeId)) {
                $unCachedEpisodeIdList[] = $episodeId;
            } else {
                $episodeFromCache = json_decode(Cache::get('episode_' . $episodeId));
                $episodeList[] = $this->createEpisode($episodeFromCache);
            }
        }

        if (!empty($unCachedEpisodeIdList)) {
            $endpoint = 'api/episode/' . implode(',', array_unique($unCachedEpisodeIdList));
            $response = $this->apiClient->get($endpoint);
            $responseBody = $response->getBody()->getContents();

            if (strpos($responseBody, '[') === 0) {
                $report = json_decode($responseBody);

                foreach ($report as $episode) {
                    $episodeList[] = $this->createEpisode($episode);
                    $episodeEncode = json_encode($episode);
                    Cache::save('episode_' . $episode->id, $episodeEncode);
                }
            } else {
                $episode = json_decode($responseBody);
                $episodeList[] = $this->createEpisode($episode);
                $episodeEncode = json_encode($episode);
                Cache::save('episode_' . $episode->id, $episodeEncode);
            }
        }

        return $episodeList;
    }

    private function createEpisodeIdList(array $characterList): array
    {
        $episodeIdList = [];
        foreach ($characterList as $character) {
            $episodeIdList[] = $character->getEpisodeIdList()[0];
        }
        return $episodeIdList;
    }

    private function createEpisode(\stdClass $episodeReport): Episode
    {
        return new Episode($episodeReport->id, $episodeReport->name, $episodeReport->episode);
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