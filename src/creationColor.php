<?php

function creationColor_getmoduleinfo()
{
    return [
        'name' => 'Creation Color',
        'author' => 'Stephen Kise',
        'version' => '1.0.0',
        'category' => 'Account',
        'description' => 'Characters start with one selected color.',
        'prefs' => [
            'color' => 'Color the player chose:, viewonly| `&',
        ]
    ];
}

function creationColor_install()
{
    module_addhook('everyhit-loggedin');
    module_addhook('create-form');
    module_addhook('check-create');
    module_addhook('process-create');
    return true;
}

function creationColor_uninstall()
{
    return true;
}

function creationColor_dohook($hook, $args)
{
    switch ($hook) {
        case 'everyhit-loggedin':
            require_once('lib/names.php');
            global $session;
            if (substr(get_player_basename(), 0, 1) != '`') {
                $session['user']['name'] = change_player_name(
                    $color = get_module_pref('color') . get_player_basename()
                );
                debuglog("`2Default name color was missing. Automatically changed player base name to $color`2.");
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
                'creationColor',
                $args['acctid']
            );
            break;
    }
    return $args;
}
