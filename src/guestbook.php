<?php

function guestbook_getmoduleinfo()
{
    return [
        'name' => 'Guest Book',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Account',
        'description' => 'Add a guestbook to the biographies where players can comment on each other.',
        'settings' => [
            'recent' => 'Recent guestbook transactions, viewonly| []',
        ],
    ];
}

function guestbook_install()
{
    // Sync table to create guestbook table: id, guest, acctid, comment, deleted, deletor.
    module_addhook('bioinfo'); // Show a slideshow of recent guestbook comments on a player's profile.
    // Provide an addnav that will allow a user to post on someone's guestbook.
    // Mail the user when a guestbook message has been posted on their biography.
    // Give both staff (Admin+) and users the ability to delete their guestbook messages.
    return true;
}
