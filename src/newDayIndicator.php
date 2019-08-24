<?php

function newDayIndicator_getmoduleinfo()
{
    return [
        'name' => 'New Day Indicator',
        'version' => '1.0.0',
        'author' => 'Joshua Ecklund, Stephen Kise',
        'category' => 'Quality of Life',
        'description' => 'Shows how close the new day is in the villages.',
    ];
}

function newDayIndicator_install()
{
    module_addhook('villagetext');
    return true;
}

function newDayIndicator_uninstall()
{
    return true;
}

function newDayIndicator_dohook($hook, $args)
{
    switch ($hook) {
        case 'villagetext':
            $clockMessage = str_replace('`n', ' ', $args['clock']);
            $details = gametimedetails();
            debug(date('i', secondstonextgameday()));
            $secsToNewday = secondstonextgameday($details); // - time();
            $args['clock'] = "$clockMessage `@That means a new day is in `^" .
                date("H\\h i\\m s\\s", $secsToNewday) . "!`n";
            break;
    }
    return $args;
}
