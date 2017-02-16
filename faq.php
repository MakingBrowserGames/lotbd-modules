<?php

function faq_getmoduleinfo()
{
    return [
        'name' => 'Server FAQ',
        'author' => 'Stephen Kise',
        'version' => '1.0',
        'category' => 'Administrative',
        'description' => 'Replaces the current FAQ with a more modular system',
        'allowanonymous' => true,
        'override_forced_nav' => true,
        'settings' => [
            'default_message' =>
                'What should be the first message in the FAQ?, textarea|',
        ]
    ];
}

function faq_install()
{
    module_addhook('village');
    module_addhook('shades');
    return true;
}

function faq_uninstall()
{
    return true;
}

function faq_dohook($hook, $args)
{
    switch ($hook) {
        case 'village':
        case 'shades':
            blocknav('petition.php?op=faq');
            if ($hook == 'shades') {
                addnav('Other');
            }
            else {
                addnav($args['infonav']);
            }
            addnav('!?Read the FAQ!', 'runmodule.php?module=faq', false, true);
            break;
    }
    return $args;
}

function faq_run()
{
    echo "<link rel='stylesheet' href='modules/css/faq.css' />";
    $op = httpget('op');
    if (httpget('subop') != '') {
        $subOp = httpget('subop');
    }
    popup_header('FAQ');
    $args = [];
    $articles = modulehook('faq-list', $args);
    rawoutput(
        "<div class='faq-navigation'>
            <ul>
                <li class='faq-navigation-header'>
                    Articles
                </li>"
    );
    foreach ($articles as $title => $uri) {
        $title = appoencode($title);
        rawoutput(
            "<li>
                <a href='$uri'>$title</a>
            </li>"
        );
    }
    rawoutput(
            "</ul>
        </div>"
    );
    rawoutput("<div class='faq-information'>");
    if ($op != '') {
        $op($subOp);
    }
    else {
        $message = get_module_setting('default_message');
        $message = str_replace(PHP_EOL, '`n', $message);
        output($message);
    }
    rawoutput("</div>");
    popup_footer();
}
