<?php
function homepage_getmoduleinfo()
{
    return [
        'name' =>  'New Homepage',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Miscellaneous',
        'description' =>
            'A custom home page to make the theme more unique.',
        'download' => 'nope',
        'settings' => [
            'Amount of views:, viewonly| 0',
            'Registration total:, viewonly| 0',
            'Total clickthrus before account creation:, viewonly| 0',
            'Total clickthrus before signing in:, viewonly| 0',
            'Sign in count:, viewonly| 0',
            'Logout count:, viewonly| 0',
        ],
    ];
}

function homepage_install()
{
    module_addhook('index');
    // Count amount of views, determine if $_POST contains anything (error messages) and output those messages properly
    // block navigation to everything except account creation, list warriors, and link exchange (if it exists.)
    module_addhook('player-login'); // Count the total number of logins, clickthru the amount of hits (gencount?) for signing in.
    module_addhook('player-logout'); // Count amount of actual logouts that are initiated.
    module_addhook('process-create'); // Count the amount of account creations, total number of clickthrus before account creation.
    return true;
}
