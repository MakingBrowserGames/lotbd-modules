<?php

function lumberyard_getmoduleinfo()
{
    return [
        'name' => 'The Lumberyard',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Gameplay',
        'description' => 'Adds the lumberyard area for the woodcutting skill.',
        'override_forced_nav' => 'true',
        'prefs' => [
            'task' => 'Times player has completed tasks at Lumberyard:, viewonly| Nothing',
            'time' => 'Last time the player ran a task, viewonly| 0000-00-00 00:00:00',
        ],
    ];
}

function lumberyard_install()
{
    module_addhook('api');
    return true;
}

function lumberyard_uninstall()
{
    return true;
}

function lumberyard_dohook($hook, $args)
{
    switch ($hook) {
        case 'api':
            global $session;
            $args['lumberyard'] = [
                'chop' => 'lumberyardDoChop',
                'sell' => 'lumberyardSellItem',
            ];
            break;
    }
    return $args;
}

/**
  *
  * @schema Skill_Actions
  * id, skill, type, level-req, exp, item-reward, message, timeout
  * id: number of the skill action
  * skill: name of the skill that the action is under
  * type: name of the action user committed
  * level-req: Level needed to complete this action
  * exp: Experience to reward the players for completing said action.
  * item-reward: What to give the players (in JSON format) when they complete the action.
  * message: output message, if needed, to display when the player completes said action.
   ---> "You chop down a tree and receive %s" > "You chop down a tree and receive 2 x Logs, 1 x Bird's Nest"
  * timeout: Time (if needed) to delay incoming skilling attempts.
  * fail-rate:  1-100 representation of failure.
  * fail-message: Message when failing.
  *
**/

function lumberyardDoChop()
{
    global $session;
    //require_once('modules/skills.php'); // Grab skills file, execute checks for level-req, inventory space, and assign items to inventory.
    $acts = db_prefix('skill_actions');
    $response = [];
    if (($type = httpget('type')) == '') {
        die(['message' => 'Unfortunately, a proper tree type was not selected!']);
    }
    $response['message'] = "You chop down a tree and receive <div class='items-reward'>1 Log</div>";
    /*$sql = db_query_cached(
        "SELECT level-req, exp, message, item-reward, timeout FROM $acts WHERE type = '$type' AND skill = 'woodcutting' LIMIT 1",
        "woodcutting-$type-reward",
        86400
    );
    $row = db_fetch_assoc($sql);
    /*
    if (levelCheck('woodcutting', $row['level-req']) == false) { // Check level of player's skill according to the action's level.
        die(json_encode(['message' => 'You do not have the required level to do that!'));
    }
    $itemReward = json_decode($row['item-reward'], true);
    $rewardString = '';
    foreach ($itemReward as $item => $data) {
        if (spaceForItem($item)) { // Check if there is space for x item.
            $reward = rand($data['min'], $data['max']);
            saveItemToSlot($item, $reward); // Save the rewarded item to the player's slot.
            $rewardString .= "<div class='items-reward'>$reward {$data['name']}</div> ";
            
        }
        else {
            $response['error'] = "You did not have enough space for all of your rewards!";
        }
    }
    $response['message'] = sprintf($row['message'], $rewardString);
    */
    return json_encode($response);
}

function lumberyardSellItem()
{

}
