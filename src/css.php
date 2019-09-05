<?php
declare(strict_types=1);

/**
 * Introduces a new module hook 'css'. Usage is ['moduleName' => 'cssFile'].
 * This module will automatically implement the CSS file in the head of the page.
 */
function css_getmoduleinfo(): array
{
    return [
        'name' => 'CSS',
        'author' => 'Stephen Kise',
        'version' => '1.0.0',
        'category' => 'Administrative',
        'description' => 'Adds a module hook to implement CSS seemlessly.'
    ];
}

function css_install(): bool
{
    module_addhook('everyheader');
    module_addhook('header-popup');
    return true;
}

function css_uninstall(): bool
{
    return true;
}

function css_dohook(string $hook, array $args): array
{
    global $template;
    $cssFiles = modulehook('css', []);
    $header = 'header';
    if ($hook == 'header-popup') {
        $header = 'popuphead';
    }
    if (!empty($cssFiles)) {
        foreach ($cssFiles as $moduleName => $fileName) {
            $cssString = "<link rel='stylesheet' type='text/css' href='modules/css/$fileName.css' />";
            $template[$header] = str_replace(
                '{script}',
                '{script}' . PHP_EOL . $cssString,
                $template[$header]
            );
        }
    }
    return $args;
}