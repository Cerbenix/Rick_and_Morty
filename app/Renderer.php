<?php declare(strict_types=1);

namespace App;

use App\Views\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Renderer
{
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader('../app/Views');
        $this->twig = new Environment($loader);
    }

    public function render(View $view): string
    {
        return $this->twig->render($view->getTemplate() . '.twig', $view->getVariables());
    }
}
