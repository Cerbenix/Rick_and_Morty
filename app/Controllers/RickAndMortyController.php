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

    public function index(): View
    {
        $allEpisodes = $this->apiClient->fetchAllEpisodes();
        $allLocations = $this->apiClient->fetchAllLocations();
        $characters = $this->apiClient->fetchCharactersByPage(1);
        $contentList = $this->apiClient->associateEpisodeName($characters, $allEpisodes);
        return new View('main', [
            'characters' => $contentList,
            'nextPage' => 2,
            'allEpisodes' => $allEpisodes,
            'allLocations' => $allLocations
        ]);
    }

    public function paginator(array $variables): View
    {
        $allEpisodes = $this->apiClient->fetchAllEpisodes();
        $allLocations = $this->apiClient->fetchAllLocations();
        if (!empty($_POST)) {
            if ($_POST['page'] == '') {
                $_POST['page'] = 1;
            }
            header('Location: /page/' . $_POST['page']);
        }
        $pageNr = isset($variables['page']) ? (int)$variables['page'] : 1;
        $characters = $this->apiClient->fetchCharactersByPage($pageNr);
        $contentList = $this->apiClient->associateEpisodeName($characters, $allEpisodes);
        $previousPage = ($pageNr >= 2) ? $pageNr - 1 : null;
        $nextPage = ($pageNr < 42) ? $pageNr + 1 : null;
        return new View('main', [
            'characters' => $contentList,
            'previousPage' => $previousPage,
            'nextPage' => $nextPage,
            'allEpisodes' => $allEpisodes,
            'allLocations' => $allLocations
        ]);
    }

    public function search(): View
    {
        $allEpisodes = $this->apiClient->fetchAllEpisodes();
        $contentList = [];
        if (!empty($_POST)) {
            $characters = $this->apiClient->fetchSearched(
                $_POST['name'],
                $_POST['status'],
                $_POST['species'],
                $_POST['type'],
                $_POST['gender'],
            );
            if (!empty($characters)) {
                $contentList = $this->apiClient->associateEpisodeName($characters, $allEpisodes);
            }
        }
        return new View('search', [
            'characters' => $contentList,
            'allEpisodes' => $allEpisodes
        ]);
    }

    public function singleton(array $variables): View
    {

        $characterId = $variables['id'];
        $character = $this->apiClient->fetchCharacter($characterId);
        $characterEpisodeList = $this->apiClient->fetchEpisodes($character->getEpisodeIdList());
        return new View('singleton', [
            'character' => $character,
            'characterEpisodes' => $characterEpisodeList,

        ]);
    }

    public function episode(array $variables): View
    {
        $episodeId = $variables['id'];
        $allEpisodes = $this->apiClient->fetchAllEpisodes();
        $episodeContent = null;
        foreach ($allEpisodes as $episode) {
            if ($episode->getId() == $episodeId) {
                $episodeContent = $episode;
                break;
            }
        }
        $characters = $this->apiClient->fetchCharactersById($episodeContent->getCharacterIdlist());
        $contentList = $this->apiClient->associateEpisodeName($characters, $allEpisodes);
        return new View('episode', [
            'episode' => $episodeContent,
            'characters' => $contentList,
            'allEpisodes' => $allEpisodes
        ]);
    }
    public function location(array $variables): View
    {
        $locationId = $variables['id'];
        $allLocations = $this->apiClient->fetchAllLocations();
        $locationContent = null;
        foreach ($allLocations as $location) {
            if ($location->getId() == $locationId) {
                $locationContent = $location;
                break;
            }
        }
        $characters = $this->apiClient->fetchCharactersById($locationContent->getResidentIdList());
        $episodeList = $this->apiClient->fetchEpisodes($this->apiClient->createEpisodeIdList($characters));
        $contentList = $this->apiClient->associateEpisodeName($characters, $episodeList);
        return new View('location', [
            'location' => $locationContent,
            'characters' => $contentList,
            'allLocations' => $allLocations
        ]);
    }
}