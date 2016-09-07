<?php

function items_getmoduleinfo()
{
    $info = [
        'name' => 'Items System',
        'author' => '`&`bStephen Kise`b',
        'version' => '0.1b',
        'category' => 'Gameplay',
        'description' =>
            'Creates the central core for the items system.',
        'download' => 'nope',
        'prefs' => [
            'items' => 'Array of items and amounts on the user currently, viewonly| []',
            'in_use' => 'Array of items and amounts in use, viewonly| []'
        ]
    ];
    return $info;
}

function items_install()
{
    module_addhook('newday');
    module_addhook('superuser');
    return true;
}

function items_uninstall()
{
    return true;
}

function items_dohook($hook, $args)
{
    switch ($hook) {
        case 'newday':
            $items = json_decode(get_module_pref('items'), true);
            $inUse = json_decode(get_module_pref('in_use'), true);
            foreach ($inUse as $item => $amt) {
                if (!in_array($item, $items)) {
                    unset($items[$item]);
                }
                else if ($items[$item] > $amt) {
                    $items[$item] = $amt;
                }
            }
            set_module_pref('items', json_encode($items, true));
            break;
    }
    return $args;
}