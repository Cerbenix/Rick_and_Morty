<?php declare(strict_types=1);

namespace App\Services;

use App\Cache;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
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

    public function fetchCharactersByPage(int $pageNr): array
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


    public function fetchCharactersById(array $characterIdList): array
    {
        $allCached = true;
        $characterList = [];
        foreach ($characterIdList as $characterId) {
            if (!Cache::has('character_' . $characterId)) {
                $allCached = false;
                break;
            }
        }
        if (!$allCached) {
            $endpoint = 'api/character/' . implode(',', array_unique($characterIdList));
            $response = $this->apiClient->get($endpoint);
            $responseBody = $response->getBody()->getContents();

            if (strpos($responseBody, '[') === 0) {
                $report = json_decode($responseBody);

                foreach ($report as $character) {
                    $characterList[] = $this->createCharacter($character);
                    $characterEncode = json_encode($character);
                    Cache::save('$character_' . $character->id, $characterEncode);
                }
            } else {
                $character = json_decode($responseBody);
                $characterList[] = $this->createCharacter($character);
                $characterEncode = json_encode($character);
                Cache::save('$character_' . $character->id, $characterEncode);
            }

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
        $cacheKey = md5(serialize(func_get_args()));
        if (Cache::has($cacheKey)) {
            $cachedResult = Cache::get($cacheKey);
            return $this->collectCharacters(json_decode($cachedResult));
        }
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
            Cache::save($cacheKey, json_encode($report));
            return $this->collectCharacters($report);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return [];
            }
        }
        return [];
    }

    public function associateEpisodeName(array $characterList, array $episodes): array
    {
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
        $allCached = true;
        $episodeList = [];
        foreach ($episodeIdList as $episodeId) {
            if (!Cache::has('episode_' . $episodeId)) {
                $allCached = false;
                break;
            }
        }

        if (!$allCached) {
            $endpoint = 'api/episode/' . implode(',', array_unique($episodeIdList));
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
        } else {
            foreach ($episodeIdList as $episodeId) {
                $episodeFromCache = json_decode(Cache::get('episode_' . $episodeId));
                $episodeList[] = $this->createEpisode($episodeFromCache);
            }
        }
        return $episodeList;
    }

    public function fetchLocations(array $locationIdList): array
    {
        $allCached = true;
        $locationList = [];
        foreach ($locationIdList as $locationId) {
            if (!Cache::has('location_' . $locationId)) {
                $allCached = false;
                break;
            }
        }

        if (!$allCached) {
            $endpoint = 'api/location/' . implode(',', array_unique($locationIdList));
            $response = $this->apiClient->get($endpoint);
            $responseBody = $response->getBody()->getContents();

            if (strpos($responseBody, '[') === 0) {
                $report = json_decode($responseBody);

                foreach ($report as $location) {
                    $locationList[] = $this->createLocation($location);
                    $locationEncode = json_encode($location);
                    Cache::save('location_' . $location->id, $locationEncode);
                }
            } else {
                $location = json_decode($responseBody);
                $locationList[] = $this->createlocation($location);
                $locationEncode = json_encode($location);
                Cache::save('location_' . $location->id, $locationEncode);
            }
        } else {
            foreach ($locationIdList as $locationId) {
                $episodeFromCache = json_decode(Cache::get('location_' . $locationId));
                $locationList[] = $this->createlocation($episodeFromCache);
            }
        }
        return $locationList;
    }

    public function fetchAllEpisodes(): array
    {
        if (!Cache::has('totalEpisodes')) {
            $response = $this->apiClient->get('api/episode/');
            $report = json_decode($response->getBody()->getContents());
            Cache::save('totalEpisodes', json_encode($report));
            $latestEpisodeId = $report->info->count;
        } else {
            $cachedReport = json_decode(Cache::get('totalEpisodes'));
            $latestEpisodeId = $cachedReport->info->count;
        }
        $allEpisodeIdList = range(1, $latestEpisodeId);
        return $this->fetchEpisodes($allEpisodeIdList);
    }

    public function fetchAllLocations(): array
    {
        if (!Cache::has('totalLocations')) {
            $response = $this->apiClient->get('api/location');
            $report = json_decode($response->getBody()->getContents());
            Cache::save('totalLocations', json_encode($report));
            $latestLocationId = $report->info->count;
        } else {
            $cachedReport = json_decode(Cache::get('totalLocations'));
            $latestLocationId = $cachedReport->info->count;
        }
        $allLocationIdList = range(1, $latestLocationId);
        return $this->fetchLocations($allLocationIdList);
    }
    public function createEpisodeIdList(array $characterList): array
    {
        $episodeIdList = [];
        foreach ($characterList as $character) {
            $episodeIdList[] = $character->getEpisodeIdList()[0];
        }
        return $episodeIdList;
    }
    private function createLocation(\stdClass $locationReport): Location
    {
        $characterIdList = [];
        foreach ($locationReport->residents as $character) {
            $characterIdList[] = preg_replace('/[^0-9]/', '', $character);
        }
        return new Location(
            $locationReport->id,
            $locationReport->name,
            $locationReport->type,
            $locationReport->dimension,
            $characterIdList);
    }

    private function createEpisode(\stdClass $episodeReport): Episode
    {
        $characterIdList = [];
        foreach ($episodeReport->characters as $character) {
            $characterIdList[] = preg_replace('/[^0-9]/', '', $character);
        }
        return new Episode(
            $episodeReport->id,
            $episodeReport->name,
            $episodeReport->episode,
            $characterIdList);
    }

    private
    function collectCharacters(\stdClass $characterListReport): array
    {
        $characterCollection = [];
        foreach ($characterListReport->results as $character) {
            $characterCollection[] = $this->createCharacter($character);
        }
        return $characterCollection;
    }

    private
    function createCharacter(\stdClass $characterReport): Character
    {
        $episodeIdList = [];
        foreach ($characterReport->episode as $episode) {
            $episodeIdList[] = preg_replace('/[^0-9]/', '', $episode);
        }
        $locationId = (int) preg_replace('/[^0-9]/', '', $characterReport->location->url);
        return new Character(
            $characterReport->id,
            $characterReport->name,
            $characterReport->status,
            $characterReport->species,
            $characterReport->location->name,
            $locationId,
            $episodeIdList,
            $characterReport->image
        );
    }
}
