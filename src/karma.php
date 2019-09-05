<?php

function karma_getmoduleinfo()
{
    return [
        'name' => 'Karma',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Account',
        'description' => 'Allow players to give and receive karma through bios and other actions.',
        'download' => 'nope',
        'prefs' => [
            'karma' => 'Karma count:, viewonly| 0',
            'given' => 'Players this user has given Karma to:, viewonly| []',
        ],
    ];
}

function karma_install()
{
    module_addhook('biostat'); // Provide a stat to read out the player's Karma count - make it red/white/green based on the amount of Karma that a user has.
    // Provide a button to click on the player's biography to give Karma. (Give a good twitter heart type animation.)
    // Message a user when they are -k'd or +k'd.
    // Block users from affecting each other's Karma if they have someone blocked.
    module_addhook('newday_runonce'); // Clear all user's 'given' userpref, allowing them to adjust player's Karma again.
}
