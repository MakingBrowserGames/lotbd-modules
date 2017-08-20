<?php

/**
 * Introduces new module hook 'faq-list'.
 * Arguments work like ['Name' => 'runmodule.php?module=api&op=file&subop=func'].
 * Modules that hook into faq-list should use their file name for 'file' and
 * function name for 'func'. This file and specific function are later called
 * for automatic execution.
 */
function faq_getmoduleinfo()
{
    return [
        'name' => 'Server FAQ',
        'author' => 'Stephen Kise',
        'version' => '1.0.0',
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
    module_addhook('css');
    return true;
}

function faq_uninstall()
{
    return true;
}

function faq_dohook($hook, $args)
{
    switch ($hook) {
        case 'css':
            $args['FAQ'] = 'faq';
            break;
        case 'village':
        case 'shades':
            blocknav('petition.php?op=faq');
            addnav($args['infonav']);
            if ($hook == 'shades') {
                addnav('Other');
            }
            addnav('!?Read the FAQ!', 'runmodule.php?module=faq', false, true);
            break;
    }
    return $args;
}

function faq_run()
{
    $op = httpget('op');
    if (httpget('subop') != '') {
        $subOp = httpget('subop');
    }
    popup_header('FAQ');
    $args = ['Overview' => 'runmodule.php?module=faq'];
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
