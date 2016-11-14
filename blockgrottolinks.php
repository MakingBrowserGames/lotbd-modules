<?php
declare(strict_types=1);

function blockgrottolinks_getmoduleinfo(): array
{
    return [
        'name' => 'Block Grotto Links',
        'author' => '`&`bStephen Kise`b',
        'version' => '0.0.1',
        'category' => 'Administrative',
        'description' => 'Blocks the core functions that are not necessary',
        'hooks' => [
            'superuser'
        ],
    ];
}

function blockgrottolinks_install(): bool
{
    module_addhook('superuser');
    return true;
}

function blockgrottolinks_uninstall(): bool
{
    return true;
}

function blockgrottolinks_dohook($hook, $args): array
{
    blocknav('bios.php');
    blocknav('badword.php');
    blocknav('companions.php');
    blocknav('referers.php');
    blocknav('donators.php');
    blocknav('stats.php');
    return $args;
}