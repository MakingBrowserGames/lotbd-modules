<?php

function rlage_getmoduleinfo()
{
    return [
        'name' => 'Real Life Age',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Account',
        'description' =>
            'Keep data of player\'s real age, to check for age appropriate content.',
        'download' => 'nope',
        'prefs' => [
            'month' => 'Month:, text|',
            'day' => 'Day:, int|',
            'year' => 'Year:, int|',
            'is_adult' => 'Is this player an adult?, bool| 0',
        ],
    ];
}

function rlage_install()
{
    module_addhook('create-form'); // Add option to add in the player's DoB.
    module_addhook('process-create'); // Check the DoB of the user and update their is_adult status if they are an adult.
    module_addhook('newday'); // Check if the player is now an adult, just in case their birthday rolls through.
    return true;
}
