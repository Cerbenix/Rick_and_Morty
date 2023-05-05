<?php declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Controllers\RickAndMortyController;

return [
    ['GET', '/', [RickAndMortyController::class, 'main']],
    ['GET', '/search', [RickAndMortyController::class, 'searched']],
    ['POST', '/search', [RickAndMortyController::class, 'searched']],
    ['GET', '/character/{id}', [RickAndMortyController::class, 'singleton']],
    ['GET', '/error/{message}', [ErrorController::class, 'print']],
];
