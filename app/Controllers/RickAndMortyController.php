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
        $characters = $this->apiClient->fetchMainCharacters();
        $contentList = $this->apiClient->associateEpisodeName($characters);
        return new View('main', ['characters' => $contentList]);
    }

    public function search(): View
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
        $episodeList = $this->apiClient->fetchEpisodes($character->getEpisodeIdList());
        return new View('singleton', ['character' => $character, 'episodes' => $episodeList]);
    }
}