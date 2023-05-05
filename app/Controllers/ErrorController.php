<?php declare(strict_types=1);

namespace App\Controllers;

use App\Views\View;

class ErrorController
{
    public function print(array $vars): View
    {
        $message = $vars['message'];
        header('refresh:5 url=/');
        return new View('error', ['message' => $message]);
    }
}
