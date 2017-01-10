<?php

function mail_getmoduleinfo(): array
{
    return [
        'name' => 'New Mail',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Gameplay',
        'description' => 'Replaces the current mail system with a mature one.',
        'override_forced_nav' => true,
        'allowanonymous' => true,
        'settings' => [
            'length' => 'How long should replies be?, int| 2048',
        ],
        'prefs' => [
            'Mail Preferences, title',
            'contacts' => 'Array of contacts saved:, viewonly| []',
            'blocked' => 'Array of people blocked:, viewonly| []',
            'user_offset' => 'How many responses should we display?, int| 10',
        ],
        'install' => [
            'mailfunctions' => [
                'function' => 'redirectToInbox',
            ],
            'bioinfo' => [
                'function' => 'mailContactLink',
            ],
        ]
    ];
}

function mail_install(): bool
{
    // CREATE ORIGINATORS TABLE. THIS WILL ACT AS MANAGEMENT OF WHO CAN VIEW GROUP MESSAGES.
    // Originators
    // id | origin | acctid | owner | invitor | dateissued
    $mail = db_prefix('mail');
    db_query(
        "ALTER TABLE $mail 
        CHANGE sent DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"
    );
    return true;
}

function mail_uninstall(): bool
{
    return true;
}

function redirectToInbox(string $hook, array $args): array
{
    global $SCRIPT_NAME;
    if ($SCRIPT_NAME == 'mail.php' || $hook == 'force') {
        header('Location: runmodule.php?module=mail&op=inbox');
    }
    return $args;
}

function mailContactLink(string $hook, array $args): array
{
    global $session;
    if ($args['acctid'] == $session['user']['acctid']) {
        return $args;
    }
    $contacts = array_keys(json_decode(get_module_pref('contacts'), true));
    $blocked = array_keys(json_decode(get_module_pref('blocked'), true));
    addnav('Return');
    addnav('Contacts');
    if (!in_array($args['acctid'], $contacts) &&
        !in_array($args['acctid'], $blocked)) {
        addnav(
            'Buddy User',
            'runmodule.php?module=mail&op=buddy&id=' . $args['acctid']
        );
    }
    if (in_array($args['acctid'], $blocked)) {
        addnav(
            'Unblock User',
            'runmodule.php?module=mail&op=unblock&id=' . $args['acctid']
        );
    }
    else {
        addnav(
            'Block User',
            'runmodule.php?module=mail&op=block&id=' . $args['acctid']
        );
    }
    addnav('Superuser');
    return $args;
}

function mail_run(): bool
{
    global $session;
    $op = httpget('op');
    if (!$session['user']['loggedin']) {
        require_once('lib/redirect.php');
        $session['message'] = 'You are not logged in!';
        redirect('home.php');
    }
    popup_header('Mail');
    displayMailHeader();
    $function = "mail" . ucfirst($op);
    $function();
    popup_footer();
    return false;
}

function displayMailHeader(): bool
{
    rawoutput(
        "<link href='modules/css/mail.css' rel='stylesheet' type='text/css'>
        <script src='modules/js/mail.js'></script>"
    );
    $mailFunctions = modulehook(
        'mailfunctions',
        [
            'Inbox' => 'runmodule.php?module=mail&op=inbox',
            'Compose' => 'runmodule.php?module=mail&op=compose',
            'Contacts' => 'runmodule.php?module=mail&op=contacts'
        ]
    );
    rawoutput(
        "<div class='mail-header'>
            <ul>");
    foreach ($mailFunctions as $text => $link) {
        rawoutput("<li><a href='$link'>$text</a></li>");
    }
    rawoutput(
        "   </ul>
        </div>"
    );
    return false;
}

function canViewMessage(int $id): bool
{
    global $session;
    $mail = db_prefix('mail');
    $user = (int) $session['user']['acctid'];
    if (!is_numeric($id)) {
        return false;
    }
    $sql = db_query(
        "SELECT msgfrom, msgto FROM $mail WHERE originator = '$id'
        AND (msgto = '$user' OR msgfrom = '$user')
        ORDER BY messageid+0 DESC LIMIT 0, 25"
    );
    if (db_num_rows($sql) < 1) {
        return false;
    }
    db_free_result($sql);
    /*$sql = db_query(
        "SELECT acctid FROM $originators WHERE $origin = '$id' LIMIT 0, 10"
    );
    while ($row = db_fetch_assoc($sql)) {
        if ($row['acctid'] == $user) {
            $allowed = true;
        }
    }
    db_free_result($sql);
    */
    return true;
}

function mailBuddy(): bool
{
    require_once('lib/redirect.php');
    global $session;
    $id = httpget('id');
    $contacts = json_decode(get_module_pref('contacts'), true);
    $contacts[$id] = date('Y-m-d H:i:s', time());
    set_module_pref('contacts', json_encode($contacts));
    redirect($session['user']['restorepage']);
    return false;
}

function mailBlock(): bool
{
    require_once('lib/redirect.php');
    global $session;
    $id = httpget('id');
    $contacts = json_decode(get_module_pref('contacts'), true);
    $blocked = json_decode(get_module_pref('blocked'), true);
    $blocked[$id] = date('Y-m-d H:i:s', time());
    set_module_pref('blocked', json_encode($blocked));
    unset($contacts[$id]);
    set_module_pref('contacts', json_encode($contacts));
    redirect($session['user']['restorepage']);
    return false;
}

function mailUnblock(): bool
{
    require_once('lib/redirect.php');
    global $session;
    $id = httpget('id');
    $blocked = json_decode(get_module_pref('blocked'), true);
    unset($blocked[$id]);
    set_module_pref('blocked', json_encode($blocked));
    redirect($session['user']['restorepage']);
    return false;
}

function mailInbox(): bool
{
    global $session;
    $mail = db_prefix('mail');
    $accounts = db_prefix('accounts');
    $user = (int) $session['user']['acctid'];
    rawoutput(
        "<div class='mail-inbox'>
            <h1>Current Messages</h1>
            <form action='runmodule.php?module=mail&op=del'>
            </form>
            <table class='mail-list-messages'>
                <thead>
                    <th colspan='2'>Message</th>
                    <th>Received</th>
                </thead>");
    $sql = db_query(
        "SELECT * FROM (SELECT m.*, a.name, a.loggedin FROM $mail AS m
        RIGHT JOIN $accounts AS a ON m.msgfrom = a.acctid
        WHERE msgto = '$user' GROUP BY originator, messageid DESC) as tmp
        GROUP BY tmp.originator ORDER BY seen+0 ASC"
    );
    while ($row = db_fetch_assoc($sql)) {
        rawoutput(
            sprintf(
            "<tr name='messages' data-originator='%s'>
                <td>
                    <span class='mail-message-subject'>%s</span>
                </td>
                <td>
                    <span class='mail-message-last-responder'>%s</span>
                </td>
                <td>
                    <span class='mail-message-received'>%s</span>
                </td>
            </tr>",
            $row['originator'],
            $row['subject'],
            full_sanitize($row['name']),
            $row['sent']
            )
        );
    }
    rawoutput(
        "   </table>
        </div>"
    );
    return false;
}

// USE ORIGINATOR TO GROUP MESSAGES. WHEN COMPOSING A NEW MESSAGE, CREATE A NEW ORIGINATOR ID

function mailView(): bool
{
    global $session;
    $mail = db_prefix('mail');
    $accounts = db_prefix('accounts');
    $id = (int) httpget('id');
    $userOffset = (int) get_module_pref('user_offset');
    $offset = (int) httpget('page');
    $offsetString = "LIMIT " . $offset * $userOffset .
        ", " . ($offset + 1) * $userOffset;
    if (canViewMessage($id) == false) {
        debuglog(
            sprintf(
                "tried to view mail with origin of id %s but was not allowed",
                $id
            )
        );
        redirectToInbox('force', []);
    }
    $sql = db_query(
        "SELECT m.body, m.subject, m.sent, m.seen, m.msgfrom, a.name FROM $mail AS m
        RIGHT JOIN $accounts AS a ON m.msgfrom = a.acctid
        WHERE m.originator = '$id'
        ORDER BY m.messageid DESC $offsetString"
    );
    $sortedMessages = [];
    while ($row = db_fetch_assoc($sql)) {
        //debug($row);
        $title = $row['subject'];
        $row['body'] = stripslashes($row['body']);
        $row['body'] = nl2br($row['body']);
        array_push($sortedMessages, $row);
    }
    $sortedMessages = array_reverse($sortedMessages);
    rawoutput(
        "<div class='mail-inbox'>
            <h1 id='message-subject'>{$title}</h1>
            <form action='runmodule.php?module=mail&op=title&id={$id}'
                class='message-title-edit' id='message-subject-form'>
                <input name='message-subject-edit'
                    value='{$title}'>
                <input type='submit' value='Submit'>
            </form>"
    );
    foreach ($sortedMessages as $number => $row) {
        $class = 'mail-reply-from-user';
        if ($session['user']['acctid'] == $row['msgfrom']) {
            $class = 'mail-reply-from-me';
        }
        if ($row['msgfrom'] < 1) {
            $class = 'mail-reply-from-system';
        }
        output(
            "<div class='mail-message-container'>
                <div class='$class'>
                    <div class='message-details'>
                        {$row['name']}
                    </div>
                    {$row['body']}
                </div>
            </div>",
            true
        );
    }
    rawoutput(
        "
            <form action='runmodule.php?module=mail&op=reply&id={$id}'
                method='POST'>
                <div class='message-reply' id='message-reply' contenteditable>
                    <textarea name='reply' id='message-reply-form'
                        class='input'></textarea>
                    <input type='submit' value=' Send'>
                    <input type='hidden' name='id' value='{$id}'>
                    <input type='hidden' name='to' value='{$row['msgfrom']}'>
                    <input type='hidden' name='subject'
                        value='{$title}'>
                </div>
            </form>
            <a name='last'></a>
        </div>"
    );
    return false;
}

function mailReply(): bool
{
    global $session, $HTTP_POST_VARS;
    $post = httpallpost();
    sendMail(
        $post['to'],
        $post['reply'],
        $post['subject'],
        $session['user']['acctid'],
        $post['id']
    );
    header("Location: runmodule.php?module=mail&op=view&id={$post['id']}#reply");
    return false;
}

function sendMail(
    int $recipient,
    string $message,
    string $subject,
    int $sender,
    int $originator
): bool
{
    require_once('lib/sanitize.php');
    $mail = db_prefix('mail');
    $accounts = db_prefix('accounts');
    $message = addslashes(sanitizeHTML($message));
    $sql = db_query(
        "INSERT INTO $mail (msgto, msgfrom, subject, body, originator)
        VALUES ($recipient, $sender, '$subject', '$message', $originator)"
    );
    if (db_error()) {
        debug(db_error());
        return false;
    }
    return true;
}