<?php

function aboutThisServer_getmoduleinfo()
{
    return [
        'name' => 'About this Server',
        'author' => 'Stephen Kise',
        'version' => '1.0',
        'category' => 'Administrative',
        'description' => 'Changes the way the about page is displayed.',
        'allowanonymous' => true,

        'settings' => [
            'name' => 'Name of the server:, text| Legend of the Green Dragon',
            'short_name' => 'Short name of the server, text| LotGD',
            'message' => 'Describe your server:, textarea| This is an LotGD server!',
            'Make sure that you explain what your theme is!, note',
            'This message will be placed under the "About LotGD" section, note'
        ]
    ];
}

function aboutThisServer_install()
{
    module_addhook('about');
    return true;
}

function aboutThisServer_uninstall()
{
    return true;
}

function aboutThisServer_dohook($hook, $args)
{
    global $SCRIPT_NAME;
    if (httpget('op') == '' && $SCRIPT_NAME == 'about.php') {
        header('Location: runmodule.php?module=aboutThisServer');
    }
    return $args;
}

function aboutThisServer_run()
{
    $settings = get_all_module_settings();
    page_header($settings['name']);
    addnav('About ' . $settings['short_name']);
    addnav('Game Setup Info', 'about.php?op=setup');
    addnav('Module Info', 'about.php?op=listmodules');
    addnav('License Info', 'about.php?op=license');
    addnav('Login Page', 'home.php');
    modulehook('about');
    rawoutput("<h1 class='header-title'>About {$settings['name']}</h1>");
    output("`n{$settings['message']}");
    page_footer();
}