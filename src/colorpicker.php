<?php

function colorpicker_getmoduleinfo()
{
    return [
        'name' => 'Color Picker',
        'author' => 'Stephen Kise',
        'version' => '0.1.0',
        'category' => 'Administrative',
        'description' => 'Create colors on the fly!',
        'download' => 'nope',
        'settings' => [
            'colors' => 'List of colors:, viewonly| []'
        ]
    ];
}

function colorpicker_install()
{
    module_addhook('color-codes');
    module_addhook('superuser');
    return true;
}

function colorpicker_uninstall()
{
    return true;
}

function colorpicker_dohook($hookName, $args)
{
    switch ($hookName) {
        case 'color-codes':
            global $colorPickerInjectedCSS;
            $settings = json_decode(get_module_setting('colors'), true);
            if (!empty($settings) && $colorPickerInjectedCSS < 1) {
                $colorCSS = '';
                foreach ($settings as $code => $data) {
                    $colorCSS .= ".{$data[0]} { color = '{$data[1]}'; }\n\r";
                    $args[$code] = $data[0];
                }
                $colorPickerInjectedCSS++;
                debug($colorCSS);
                debug($args);
                rawoutput("CSS HERE<style>$colorCSS</style>");
            }
            break;
        case 'superuser':
            addnav('Editors');
            addnav('Edit Colors', 'runmodule.php?module=colorpicker&op=su');
            break;
    }
    return $args;
}

function colorpicker_run()
{
    page_header('Color Creator');
    $op = httpget('op');
    switch ($op) {
        case 'su':
            rawoutput(
                "
                <form action='runmodule.php?module=colorpicker&op=save'
                    method='POST'>
                <input type='color' name='color'>
                <input type='text' name='key' maxlength=1 size=3
                    placeholder='Color Key'>
                <input type='text' name='class' placeholder='colClassName'>
                <br>
                <div class='testcolors'>
                    Your colored text will appear here.
                </div>
                <input type='submit' value='Save'>
                </form>"
            );
            addnav('', 'runmodule.php?module=colorpicker&op=save');
            addnav("Superuser Grotto", "superuser.php");
            break;
        case 'save':
            $settings = json_decode(get_module_setting('colors'), true);
            $post = httpAllPost();
            $post['key'] = substr($post['key'], 0, 1);
            $settings[$post['key']] = [$post['class'], $post['color']];
            debug($settings);
            set_module_setting('colors', json_encode($settings));
            addnav('Go back', 'runmodule.php?module=colorpicker&op=su');
            addnav('Return to the Grotto', 'superuser.php');
            break;
    }
    page_footer();
}
