<?php declare(strict_types=1);

namespace App\Views;

class View
{
    private string $template;
    private array $variables;

    public function __construct(string $template, array $variables = [])
    {
        $this->template = $template;
        $this->variables = $variables;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}