<?php

function blockCoreNavs_getmoduleinfo()
{
    return [
        'name' => 'Core Blocknavs',
        'author' => 'Stephen Kise',
        'version' => '1.0.0',
        'category' => 'Administrative',
        'description' => 'Removes some of core navs that are not necessary',
        'settings' => [
            'block' =>
                'Which links should we block?, textarea| Separate with commas!'
        ]
    ];
}

function blockCoreNavs_install()
{
    module_addhook('everyhit');
    return true;
}

function blockCoreNavs_uninstall()
{
    return true;
}

function blockCoreNavs_dohook($hook, $args)
{
    $settings = get_module_setting('block');
    $list = explode(',', $settings);
    if (count($list) <= 1) {
        blocknav($list[0]);
        return $args;
    }
    foreach ($list as $key => $nav) {
        blocknav(trim($nav));
    }
    return $args;
}
