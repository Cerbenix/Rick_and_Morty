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
        $characters = $this->apiClient->fetchCharacters(1);
        $contentList = $this->apiClient->associateEpisodeName($characters);
        return new View('main', [
            'characters' => $contentList,
            'nextPage' => 2
        ]);
    }

    public function paginator(array $variables): View
    {

        if (!empty($_POST)) {
            if ($_POST['page'] == '') {
                $_POST['page'] = 1;
            }
            header('Location: /page/' . $_POST['page']);
        }
        $pageNr = isset($variables['page']) ? (int)$variables['page'] : 1;
        $characters = $this->apiClient->fetchCharacters($pageNr);
        $contentList = $this->apiClient->associateEpisodeName($characters);
        $previousPage = ($pageNr >= 2) ? $pageNr - 1 : null;
        $nextPage = ($pageNr < 42) ? $pageNr + 1 : null;
        return new View('main', [
            'characters' => $contentList,
            'previousPage' => $previousPage,
            'nextPage' => $nextPage
        ]);
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
            if (!empty($characters)) {
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