<?php

function aboutThisServer_getmoduleinfo()
{
    return [
        'name' => 'About this Server',
        'author' => 'Stephen Kise',
        'version' => '1.0.1',
        'category' => 'Administrative',
        'description' => 'Changes the way the about page is displayed.',
        'allowanonymous' => true,
        'override_forced_nav' => true,
        'settings' => [
            'name' => 'Name of the server:, text| Legend of the Green Dragon',
            'short_name' => 'Short name of the server, text| LotGD',
            'message' =>
                'Describe your server:, textarea| This is an LotGD server!',
            'Make sure that you explain what your theme is!, note',
            'This message will be placed under the "About LotGD" section, note'
        ]
    ];
}

function aboutThisServer_install()
{
    module_addhook('footer-about');
    module_addhook_priority('about', 99);
    module_addhook('index');
    module_addhook('footer-news');
    return true;
}

function aboutThisServer_uninstall()
{
    return true;
}

function aboutThisServer_dohook($hook, $args)
{
    global $SCRIPT_NAME, $navbysection;
    $shortName = get_module_setting('short_name');
    switch ($hook) {
        case 'about':
        case 'footer-about':
            addnav("About $shortName");
            if ($SCRIPT_NAME == 'about.php') {
                foreach ($navbysection['About LoGD'] as $item => $data) {
                    blocknav($data[1]);
                    if (strpos($data[1], '?') !== false) {
                        addnav($data[0], $data[1] . "&x=x");
                    }
                    else {
                        addnav($data[0], $data[1] . "?x=x");
                    }
                }
                if (httpget('op') == '') {
                    header('Location: runmodule.php?module=aboutThisServer');
                }
            }
            break;
        case 'index':
        case 'footer-news':
            if ($hook == 'footer-news') {
                addnav('News');
            }
            else {
                addnav('Other Info');
            }
            blocknav('about.php');
            blocknav('about.php?op=setup');
            addnav("About $shortName", 'runmodule.php?module=aboutThisServer');
            break;
    }
    return $args;
}

function aboutThisServer_run()
{
    global $session;
    $settings = get_all_module_settings();
    page_header($settings['name']);
    modulehook('about');
    addnav('About ' . $settings['short_name']);
    addnav('Game Setup Info', 'about.php?op=setup');
    addnav('Module Info', 'about.php?op=listmodules');
    addnav('License Info', 'about.php?op=license');
    if (!$session['user']['loggedin']) {
        addnav('Login Page', 'home.php');
    }
    else {
        addnav('Return to the news', 'news.php');
    }
    rawoutput("<h1 class='header-title'>About {$settings['name']}</h1>");
    output("`n{$settings['message']}");
    page_footer();
}