<?php

function logins_getmoduleinfo()
{
    $info = [
        'name' => 'Login Data',
        'author' => '`&`bStephen Kise`b',
        'version' => '0.1b',
        'category' => 'Account',
        'descriptions' => 'Allow players to see their recent logins.',
        'download' => 'nope',
        'prefs' => [
            'data' => 'Current data collection of this user\'s latest logins, viewonly| []',
        ],
    ];
    return $info;
}

function logins_install()
{
    module_addhook('prefs-format'); // Add a viewer in the 'Account' header to show past five logins.
    module_addhook('player-login'); // Track a player's login, time, their location, the ip, and the device type (mobile/desktop and browser type.)
    return true;
}