<?php

function git_getmoduleinfo()
{
    $info = [
        'name' => 'Git Management',
        'author'=> '`&`bStephen Kise`b',
        'version' => '0.1b',
        'category' => 'Administrative',
        'description' =>
            'Manage the git repository.',
        'requires' => [
            'changelog' => '0.1b |Stephen Kise, nope',
        ],
        'download' => 'nope',
    ];
    return $info;
}

function git_install()
{
    module_addhook('superuser');
    return true;
}

function git_uninstall()
{
    return true;
}

function git_dohook($hook, $args)
{
    switch ($hook) {
        case 'superuser':
            global $session;
            if ($session['user']['superuser'] & SU_MANAGE_MODULES) {
                addnav('Mechanics');
                addnav('Git Pull', 'superuser.php?git=pull');
                addnav('Update Modules', 'superuser.php?git=submodules');
                require_once('lib/gamelog.php');
                switch (httpget('git')) {
                    case 'pull':
                        shell_exec('git pull');
                        $output = shell_exec('git log --format=%B -1');
                        $output = explode(PHP_EOL, $output);
                        unset($output[0]);
                        $output = trim(implode(PHP_EOL, $output));
                        gamelog($output, get_module_setting('category', 'changelog'));
                        break;
                    case 'submodules':
                        shell_exec('git submodule foreach git pull');
                        gamelog(
                            "updated modules from remote branch",
                            get_module_setting('category', 'changelog')
                        );
                        break;
                }
            }
            break;
    }
    return $args;
}
