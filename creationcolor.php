<?php

function creationcolor_getmoduleinfo()
{
    $info = [
        'name' => 'Creation Color',
        'author' => '`&`bStephen Kise`b',
        'version' => '0.1b',
        'category' => 'Account',
        'description' =>
            'Allow players to choose their name color when they create a character.',
        'prefs' => [
            'color' => 'Color the player chose upon account creation, viewonly| `&',
        ]
    ];
}

function creationcolor_install()
{
    module_addhook('header-user'); // Check if 'op' == 'saved' and if the $_POST contains 'name'. If it has been changed, and doesn't contain color, add the user's chosen color.
    module_addhook('create-form'); // Ability to pick color. (Probably from a color picker, like on twitch?)
    module_addhook('process-create'); // Check the player's color, and set it to their account immediately in the prefs and $session['user']['name']
    return true;
}