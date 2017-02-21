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
            'post_master' => 'Acctid of the post master:, viewonly',
            'password' => 'Password of post master:, viewonly',
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
    $mail = db_prefix('mail');
    $accounts = db_prefix('accounts');
    db_query(
        "ALTER TABLE $mail 
        CHANGE sent DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"
    );
    $sql = db_query(
        "SELECT acctid FROM $accounts
        WHERE name = '`^Post Master' LIMIT 1"
    );
    if (db_num_rows($sql) > 0) {
        $row = db_fetch_assoc($sql);
    }
    else {
        $password = md5(rand(0, 100).time());
        db_query(
            "INSERT INTO $accounts (name, password)
            VALUES ('`^Post Master', '$password')"
        );
        $sql = db_query(
            "SELECT acctid FROM accounts
            WHERE name = '`^Post Master' LIMIT 1"
        );
        $row = db_fetch_assoc($sql);
    }
    set_module_setting('post_master', $row['acctid']);
    set_module_setting('password', $password);
    return true;
}

function mail_uninstall(): bool
{
    $accounts = db_prefix('accounts');
    $settings = get_all_module_settings();
    db_query(
        "DELETE FROM $accounts WHERE name = '`^Post Master'"
    );
    return true;
}

function redirectToInbox(string $hook, array $args): array
{
    global $SCRIPT_NAME;
    if ($SCRIPT_NAME == 'mail.php' || $hook == 'force') {
        header('Location: runmodule.php?module=mail&op=inbox');
    }
    if (httpget('op') == 'view') {
        $id = httpget('id');
        $args['Add to Convo'] = "runmodule.php?module=mail&op=addUser&id=$id";
        $args['Leave Convo'] = "runmodule.php?module=mail&op=leave&id=$id";
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
    if ($originator < 1) {
        $sql = db_query("SELECT MAX(originator) AS n FROM $mail LIMIT 1");
        $originator = db_fetch_assoc($sql)['n'] + 1;
    }
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
            //'Contacts' => 'runmodule.php?module=mail&op=contacts'
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

function addUserToOrigin(int $origin, int $user): bool
{
    global $session;
    $mail = db_prefix('mail');
    $mailOrigins = db_prefix('mail_origins');
    $invitor = (int) $session['user']['acctid'];
    $sql = db_query(
        "SELECT DISTINCT msgfrom, msgto FROM $mail WHERE originator = '$origin'"
    );
    $isOriginalUser = false;
    while ($row = db_fetch_assoc($sql)) {
        if ($invitor == $row['msgfrom'] ||
            $invitor == $row['msgto']) {
            $isOriginalUser = true;
        }
    }
    if ($isOriginalUser != true) {
        debuglog(
            "tried to illegally invite a person to conversation id $origin"
        );
        return false;
    }
    db_query(
        "INSERT INTO $mailOrigins (origin, acctid, invitor)
        VALUES ($origin, $user, $invitor)"
    );
    if (db_error()) {
        debug(db_error());
    }
    return true;
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
        WHERE (msgto = '$user' OR msgfrom = '$user') GROUP BY originator, messageid DESC) as tmp
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
                class='message-title-edit' id='message-subject-form'
                method='POST'>
                <input type='hidden' name='id' value='$id'>
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
    db_query("UPDATE $mail SET seen = 1 WHERE msgto = $user AND originator = $id");
    return false;
}

function mailReply(): bool
{
    global $session;
    $post = httpallpost();
    sendMail(
        $post['to'],
        $post['reply'],
        $post['subject'],
        $session['user']['acctid'],
        $post['id']
    );
    header("Location: runmodule.php?module=mail&op=view&id={$post['id']}#last");
    return false;
}

function mailCompose(): bool
{
    $accounts = db_prefix('accounts');
    $to = httpPostClean('message-to');
    $search = implode('%', str_split($to));
    $timeOut = date(
        'Y-m-d H:i:s',
        strtotime('-' . getsetting('LOGINTIMEOUT', 900) . ' seconds')
    );
    $extraSql = "loggedin = 1 AND laston > '$timeOut'";
    if ($to != '') {
        $extraSql = "(name LIKE '%$search%' OR login LIKE '%$search%')";
    }

    output("<div class='mail-inbox'>`@", true);
    rawoutput(
        "<div class='message-to' id='message-to'>
        Who would you like to message?</span><br>
            <form action='runmodule.php?module=mail&op=compose'
                method='POST'>
                <input type='text' name='message-to' id='message-to' value='{$to}'>
                <input type='submit' value='Search'>
            </form>
            <table class='compose-list-users'>
            <thead>
                <th>User</th>
            </thead>"
    );
    $sql = db_query(
        "SELECT name, login, loggedin, acctid FROM $accounts
        WHERE $extraSql
        ORDER BY loggedin DESC LIMIT 0, 10"
    );
    while ($row = db_fetch_assoc($sql)) {
        output(
            sprintf(
                "<tr name='users' data-acctid='%s' data-name='%s'>
                    <td>`^%s `#%s</td>
                </tr>",
                $row['acctid'],
                full_sanitize($row['name']),
                $row['name'],
                $row['loggedin'] ? "`#(online)" : ""
            ),
            true
        );
    }
    rawoutput(
        "       </table>
            </form>
        </div>"
    );
    debug($contacts);
    rawoutput(
        "<form action='runmodule.php?module=mail&op=newMessage' id='new-message'
                class='new-message' method='POST'>
            <input type='text' name='subject' id='subject'
                placeholder='Message Subject' required>
            <div class='message-reply' id='message-reply'>
                <input type='hidden' name='to' id='to' value='{$row['msgfrom']}'>
                <textarea name='reply' id='message-reply-form'
                    class='input'></textarea>
                <input type='submit' value=' Send'>
            </div>
        </form>
        </div>"
    );
    return false;
}

function mailTitle(): bool
{
    global $session;
    $mail = db_prefix('mail');
    $title = httpPostClean('message-subject-edit');
    $id = httpPostClean('id');
    db_query("UPDATE $mail SET subject = '$title' WHERE originator = $id");
    header("Location: runmodule.php?module=mail&op=view&id=$id");
    return false;
}

function mailAddUser(): bool
{
    global $session;
    if (!canViewMessage((int) httpget('id'))) {
        header("Location: mail.php");
        exit;
    }
    $id = httpget('id');
    $accounts = db_prefix('accounts');
    $to = httpPostClean('message-to');
    $search = implode('%', str_split($to));
    $timeOut = date(
        'Y-m-d H:i:s',
        strtotime('-' . getsetting('LOGINTIMEOUT', 900) . ' seconds')
    );
    $extraSql = "loggedin = 1 AND laston > '$timeOut'";
    if ($to != '') {
        $extraSql = "(name LIKE '%$search%' OR login LIKE '%$search%')";
    }

    output("<div class='mail-inbox'>`@", true);
    rawoutput(
        "<div class='message-to' id='message-to'>
        Who would you like to add to this message?</span><br>
            <form action='runmodule.php?module=mail&op=addUser&id=$id'
                method='POST'>
                <input type='text' name='message-to' id='message-to' value='{$to}'>
                <input type='submit' value='Search'>
            </form>
            <table>
            <thead>
                <th>User</th>
            </thead>"
    );
    $sql = db_query(
        "SELECT name, login, loggedin, acctid FROM $accounts
        WHERE $extraSql
        ORDER BY loggedin DESC LIMIT 0, 10"
    );
    while ($row = db_fetch_assoc($sql)) {
        output(
            sprintf(
                "<tr>
                    <td>
                    <a href='runmodule.php?module=mail&op=invite&user=%s&id=$id'>%s</a>
                    </td>
                </tr>",
                $row['acctid'],
                appoencode($row['name'])
            ),
            true
        );
    }
    rawoutput(
        "       </table>
            </form>
        </div>"
    );
    return false;
}

function mailInvite(): bool
{
    global $session;
    if (!canViewMessage((int) httpget('id'))) {
        header("Location: mail.php");
        exit;
    }
    $mail = db_prefix('mail');
    $msgTo = (int) httpget('user');
    $id = (int) httpget('id');
    $postMaster = (int) get_module_setting('post_master');
    $sql = db_query("SELECT subject FROM $mail WHERE originator = $id LIMIT 1");
    $row = db_fetch_assoc($sql);
    db_query(
        "INSERT INTO $mail (msgfrom, msgto, subject, body, originator)
        VALUES ($postMaster,
        $msgTo,
        '{$row['subject']}',
        'A user was invited to this conversation.',
        $id)"
    );
    header('Location: mail.php');
    return false;
}

function mailLeave(): bool
{
    global $session;
    if (!canViewMessage((int) httpget('id'))) {
        header('Location: mail.php');
        exit;
    }
    $mail = db_prefix('mail');
    $accounts = db_prefix('accounts');
    $id = (int) httpget('id');
    $postMaster = (int) get_module_setting('post_master');
    db_query(
        "UPDATE $mail SET msgto = $postMaster
        WHERE msgto = {$session['user']['acctid']}"
    );
    db_query(
        "DELETE FROM $mail WHERE msgfrom = {$session['user']['acctid']}"
    );
    db_query(
        "INSERT INTO $mail (msgfrom, msgto, subject, body, originator)
        VALUES ($postMaster,
        $postMaster,
        '{$row['subject']}',
        '{$session['user']['name']} left this conversation. Their messages have been removed.',
        $id)"
    );
    header('Location: mail.php');
    return false;
}

function mailNewMessage(): bool
{
    global $session;
    $post = httpallpost();
    sendMail(
        $post['to'],
        $post['reply'],
        $post['subject'],
        $session['user']['acctid'],
        0
    );
    //header("Location: runmodule.php?module=mail&op=inbox");
    return false;
}
