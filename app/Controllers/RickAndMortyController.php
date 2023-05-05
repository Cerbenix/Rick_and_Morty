<?php declare(strict_types=1);

namespace App\Controllers;

use App\Services\ApiClient;
use App\Views\View;

class RickAndMortyController
{
    private ApiClient $apiClient;

    public function __construct()
    {
        $this->apiClient = new ApiClient();
    }

    public function main(): View
    {
        $characters = $this->apiClient->fetchCharacters();
        $contentList = $this->apiClient->associateEpisodeName($characters);
        return new View('main', ['characters' => $contentList]);
    }

    public function searched(): View
    {
        $contentList = [];
        if (!empty($_POST)) {
            $characters = $this->apiClient->fetchSearched(
                $_POST['name'],
                $_POST['status'],
                $_POST['species'],
                $_POST['type'],
                $_POST['gender'],
            );
            if ($characters != null) {
                $contentList = $this->apiClient->associateEpisodeName($characters);
            }
        }
        return new View('search', ['characters' => $contentList]);
    }

    public function singleton(array $variables): View
    {
        $characterId = $variables['id'];
        $character = $this->apiClient->fetchCharacter($characterId);
        $episodeIdList = $this->apiClient->createSingletonEpisodeIdList($character);
        $episodeList = $this->apiClient->fetchEpisodes($episodeIdList);
        return new View('singleton', ['character' => $character, 'episodes' => $episodeList]);
    }
}