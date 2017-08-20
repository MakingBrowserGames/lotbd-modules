<?php

function faqMute_getmoduleinfo()
{
    return [
        'name' => 'FAQ Mute',
        'author' => 'Stephen Kise',
        'version' => '1.0.1',
        'category' => 'Administrative',
        'description' =>
            'Blocks the commentary system until players read the FAQ.',
        'prefs' => [
            'seen_faq' => 'Has the player seen the FAQ, bool| 0',
        ],
    ];
}

function faqMute_install()
{
    module_addhook('insertcomment');
    module_addhook('mailfunctions');
    module_addhook('faq-list');
    return true;
}

function faqMute_uninstall()
{
    return true;
}

function faqMute_dohook($hook, $args)
{
    global $session;
    $seen = get_module_pref('seen_faq');
    if ($seen == 0) {
        switch ($hook) {
            case 'insertcomment':
                $message = "You need to read the FAQ before you can make posts!";
                $args['mute'] = 1;
                $args['mutemsg'] = translate_inline("`n`\$$message`0`n");
                break;
            case 'mailfunctions':
                array_push(
                    $args,
                    ['petition.php?op=faq', 'Read the FAQ']
                );
                if (httpget('op') == 'write') {
                    $session['message'] = '`$You need to read the FAQ before you can write mail.';
                    header('Location: mail.php');
                }
                break;
            case 'faq-list':
                set_module_pref('seen_faq', 1);
                break;
        }
    }
    return $args;
}
