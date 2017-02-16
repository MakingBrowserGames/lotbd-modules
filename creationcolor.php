<?php

function creationcolor_getmoduleinfo()
{
    $info = [
        'name' => 'Creation Color',
        'author' => '`&`bStephen Kise`b',
        'version' => '1.0',
        'category' => 'Account',
        'description' => 'Characters start with one selected color.',
        'prefs' => [
            'color' => 'Color the player chose upon account creation, viewonly| `&',
        ]
    ];
    return $info;
}

function creationcolor_install()
{
    module_addhook('everyhit-loggedin');
    module_addhook('create-form');
    module_addhook('check-create');
    module_addhook('process-create');
    return true;
}

function creationcolor_uninstall()
{
    return true;
}

function creationcolor_dohook($hook, $args)
{
    switch ($hook) {
        case 'everyhit-loggedin':
            require_once('lib/names.php');
            global $session;
            if (substr(get_player_basename(), 0, 1) != '`') {
                $session['user']['name'] = change_player_name(
                    $color = get_module_pref('color') . get_player_basename()
                );
                debuglog(sprintf("`2Default name color was missing. Automatically changed player base name to %s`2.", $color));
            }
            break;
        case 'create-form':
            $possibleColors = [
                '`!' => 'Blue',
                '`@' => 'Green',
                '`#' => 'Cyan',
                '`$' => 'Red',
                '`%' => 'Magenta',
                '`^' => 'Yellow',
                '`&' => 'White',
                '`)' => 'Gray'
            ];
            rawoutput(
                "<label for='color'>Choose your default color:</label>
                <select name='color'>"
            );
            foreach ($possibleColors as $code => $color) {
                rawoutput("<option value='$code'>$color</option>");
            }
            rawoutput("</select><br/><br/>");
            break;
        case 'check-create':
            $possibleColors = ['`!', '`@', '`#', '`$', '`%', '`^', '`&', '`)'];
            if (!in_array(httppost('color'), $possibleColors)) {
                $args['message'] = 'You must choose a proper color!';
                $args['blockaccount'] = true;
            }
            break;
        case 'process-create':
            set_module_pref(
                'color',
                httppost('color'),
                'creationcolor',
                $args['acctid']
            );
            break;
    }
    return $args;
}
