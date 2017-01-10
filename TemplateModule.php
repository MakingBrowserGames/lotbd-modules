<?php

function TemplateModule_getmoduleinfo()
{
    return [
        'name' => 'Template Module',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category', => 'Administrative',
        'description' => 'Template for future modules!',
        // Perhaps do away with the 'download' section and just link to a server's git repository?
        'download' => 'http://github.com/stephenkise/xythen-modules',
        'prefs' => [
            'user_text' => 'What do you want this value to be?, text'
        ],
        'prefs_values' => [
            'user_text' => 'SAMPLE TEXT',
        ],
        'settings' => [
            'setting' => 'Is this server supporting the new core?, bool'
        ],
        'settings_values' => [
            'setting' => false
        ],
        'install' => [
            'village' => [
                'function' => 'displayTemplateModule',
                'priority' => 51,
            ]
        ],
        'uninstall' => [
            'message' => [
                'Thank you for using the Template Module!',
            ],
            'function' => 'wipeTemplateModule',
        ],
        'run' => [
            'default' => [
                'allow_anonymous' => true,
                'override_forced_navigation' => true,
                'function' =>'defaultTemplateAction',
            ],
            'save' => [
                'allow_anonymous' => true,
                'function' => 'saveTemplateAction',
            ]
        ]
    ];
}

function displayTemplateModule(string $hook, array $args) :array
{
    global $session;
    output("Hello, %s, welcome to LotGD!", $session['user']['acctid']);
    return $args;
}

function wipeTemplateModule() :bool
{
    $modules = db_prefix('modules');
    db_query("DELETE FROM $modules WHERE modulename = 'TemplateModule'");
    return false;
}