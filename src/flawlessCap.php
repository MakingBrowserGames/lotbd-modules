<?php

function flawlessCap_getmoduleinfo()
{
    return [
        'name' => 'Flawless Fight Cap',
        'author' => 'Sixf00t4',
        'version' => '1.0.1',
        'category' => 'Gameplay',
        'description' => 'Limits the number of flawless fight rewards.',
        'settings' => [
            'max' => 'How many flawless wins are allowed per day?, int| 50',
        ],
        'prefs' => [
            'amount' => 'How many flawless wins today?, int| 0',
        ],
    ];
}

function flawlessCap_install()
{
    module_addhook('battle-victory');
    module_addhook('newday');
    return true;
}

function flawlessCap_uninstall()
{
    return true;
}

function flawlessCap_dohook($hook, $args)
{
    switch($hook) {
        case 'battle-victory';
            global $options;
            $runonce = false;
            if ($runonce !== false) {
                break;
            }
            if ($args['type'] == 'forest' &&
                (!isset($args['diddamage']) || $args['diddamage'] != 1)) {
                $runonce = true;
                if (get_module_pref('amount') >= get_module_setting('max')) {
                    $options['denyflawless'] =
                        '`nYou have received enough flawless fight rewards for today.`n`n`0';
                    break;
                }
                increment_module_pref('amount');
            }
            break;
        case 'newday':
            set_module_pref('amount', 0);
            break;
    }
    return $args;
}
