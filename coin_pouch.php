<?php

function coin_pouch_getmoduleinfo()
{
    $info = [
        'name' => 'Coin Pouch',
        'author' => 'Stephen Kise',
        'version' => '0.1b',
        'category' => 'Gameplay',
        'description' => 'Adds ability to create a coin pouch.',
        'settings' => [
            'costs' => "Cost values of pouch sizes - separate with line breaks! (amt&comma; gemcost&comma; dpcost), textarea| 100&comma; 0&comma; 0",
        ],
        'prefs' => [
            'stage' => "Stage of player's coin pouch, viewonly| 100",
        ]
    ];
    return $info;
}

function coin_pouch_install()
{
    module_addhook('charstats');
    module_addhook('footer-bank');
    return true;
}

function coin_pouch_uninstall()
{
    return true;
}

function coin_pouch_dohook($hook, $args)
{
    switch ($hook) {
        case 'charstats':
            global $charstat_info, $session;
            unset($charstat_info['Personal Info']['Gold']);
            $stage = get_module_pref('stage');
            if ($session['user']['gold'] > $stage) {
                $session['user']['gold'] = $stage;
                $color = '`@';
            }
            setcharstat('Equipment Info', 'Gold', "`^$color{$session['user']['gold']}");
            break;
        case 'footer-bank':
            $costs = get_module_pref('costs');
            if (count($explode = explode(PHP_EOL, $costs)) > 1 && httpget('op') != 'purchase_pouch') {
                foreach ($explode as $key) {
                    $values = explode(',', $key);
                    if (get_module_pref('stage') < $values[0] && $finished != true) {
                        addnav(
                            sprintf_translate("Upgrade Pouch `%(%s gems)", $values[1]),
                            "bank.php?op=purchase_pouch&amt={$values[0]}&price={$values[1]}"
                        );
                        $finished = true;
                    }
                }
            }
            if (httpget('op') == 'purchase_pouch') {
                set_module_pref('stage', httpget('amt'));
                output(
                    "`^You trade in your coin pouch and `%%s gems `^in exchange for a larger pouch! You can now hold `%%s gems`^!",
                    httpget('amt'),
                    httpget('price')
                );
            }
            break;
    }
    return $args;
}
