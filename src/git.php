<?php

function git_getmoduleinfo()
{
    return [
        'name' => 'Git Management',
        'author' => 'Stephen Kise',
        'version' => '0.2.1',
        'category' => 'Administrative',
        'description' => 'Manage the git repository.',
        'requires' => [
            'changelog' => '0.1.0 |Stephen Kise, nope',
        ],
        'download' => 'nope',
        'settings' => [
            'core' => 'Recent status of the core:, viewonly',
            'modules' => 'Recent status of the modules:, viewonly',
        ]
    ];
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
                addnav('Pull LotGD Source', 'superuser.php?git=pull');
                if (httpget('git') == 'pull') {
                    shell_exec('git pull');
                }
                $category = get_module_setting('category', 'changelog');
                $core = @shell_exec(
                    'git log -1 --format="%b (<a href=\"http://github.com/stephenKise/Legend-of-the-Green-Dragon/commit/%h\">%h</a>)"'
                );
                if ($core != get_module_setting('core')
                    && $core != '') {
                    set_module_Setting('core', $core);
                    gamelog($core, $category);
                }
                $modules = @shell_exec(
                    'cd modules && git log -1 --format="%b (<a href=\"http://github.com/stephenKise/xythen-modules/commit/%h\">%h</a>)"'
                );
                if ($modules != get_module_setting('modules')
                    && $modules != '') {
                    set_module_setting('modules', $modules);
                    gamelog($modules, $category);
                }
            }
            break;
    }
    return $args;
}
