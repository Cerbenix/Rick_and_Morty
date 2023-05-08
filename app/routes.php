<?php declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Controllers\RickAndMortyController;

return [
    ['GET', '/', [RickAndMortyController::class, 'index']],
    ['GET', '/page/{page:[1-9]|[1-3][0-9]|4[0-2]}', [RickAndMortyController::class, 'paginator']],
    ['POST', '/page', [RickAndMortyController::class, 'paginator']],
    ['GET', '/search', [RickAndMortyController::class, 'search']],
    ['POST', '/search', [RickAndMortyController::class, 'search']],
    ['GET', '/character/{id:[1-9]\d{0,1}|[1-7]\d{2}|8[0-2][0-6]}', [RickAndMortyController::class, 'singleton']],
    ['GET', '/error/{message}', [ErrorController::class, 'print']],
];
