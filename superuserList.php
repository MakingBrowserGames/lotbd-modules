<?php

function superuserList_getmoduleinfo()
{
    return [
        'name' => 'Superuser List',
        'author' => 'Stephen Kise',
        'version' => '1.0',
        'category' => 'Administrative',
        'description' => 'Adds a superuser list to the about page.',
        'settings' => [
            'description' => 'What should we say about superusers?, textarea|',
            'This will appear below the list of superusers, note',
        ],
        'prefs' => [
            'display' => 'Should this user appear on the list?, bool| 0',
        ]
    ];
}

function superuserList_install()
{
    module_addhook_priority('faq-list', 5);
    return true;
}

function superuserList_uninstall()
{
    return true;
}

function superuserList_dohook($hook, $args)
{
    switch ($hook) {
        case 'faq-list':
            $args['`QSuperusers'] = 'runmodule.php?module=faq&op=superuserList';
            break;
    }
    return $args;
}

function superuserList()
{
    $accounts = db_prefix('accounts');
    $userPrefs = db_prefix('module_userprefs');
    $description = get_module_setting('description', 'superuserList');
    $description = str_replace(PHP_EOL, '`n', $description);
    $sql = db_query(
        "SELECT a.name, a.emailaddress FROM $accounts AS a
        INNER JOIN $userPrefs AS m ON a.acctid = m.userid
        WHERE m.modulename = 'superuserList' AND setting = 'display'
        AND value > 0 ORDER BY a.acctid+0 ASC"
    );
    rawoutput(
        "<table class='superuser-list' align='center'>
            <tr>
                <th>Superuser</th>
                <th>Email</th>
            </tr>"
    );
    while ($row = db_fetch_assoc($sql)) {
        output(
            "<tr>
                <td>{$row['name']}</td>
                <td>{$row['emailaddress']}</td>
            </tr>",
            true
        );
    }
    rawoutput("</table>");
    output("`n`n");
    output_notl($description);
}