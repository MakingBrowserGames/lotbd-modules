<?php

function logins_getmoduleinfo()
{
    return [
        'name' => 'Login Data',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Account',
        'descriptions' => 'Allow players to see their recent logins.',
        'download' => 'nope',
        'prefs' => [
            'data' => "User's latest logins, viewonly| []",
        ],
    ];
}

function logins_install()
{
    module_addhook('prefs-format'); // Add a viewer in the 'Account' header to show past five logins.
    module_addhook('player-login'); // Track a player's login, time, their location, the ip, and the device type (mobile/desktop and browser type.)
    return true;
}
