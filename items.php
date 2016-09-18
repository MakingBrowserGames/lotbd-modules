<?php

function items_getmoduleinfo()
{
    $info = [
        'name' => 'Items System',
        'author' => '`&`bStephen Kise`b',
        'version' => '0.1b',
        'category' => 'Gameplay',
        'description' =>
            'Creates the central core for the items system.',
        'download' => 'nope',
        'prefs' => [
            'items' => 'Array of items and amounts on the user currently, viewonly| []',
            'in_use' => 'Array of items and amounts in use, viewonly| []'
        ]
    ];
    return $info;
}

function items_install()
{
	$items = [
		'items' => [
			'id' => ['name' => 'id', 'type' => 'int(11) unsigned', 'extra' => 'auto_increment'],
			'name' => ['name' => 'name', 'type' => 'varchar(50)', 'default' => 'Shiny Rock'],
			'value' => ['name' => 'value', 'type' => 'int(11) unsigned', 'default' => '1'],
			'value-modifier' => ['name' => 'value-modifier', 'type' => 'decimal(3,2)', 'default' => '1.02'],
			'ingredients' => ['name' => 'ingredients', 'type' => 'json', 'default' => '[]'],
			'craft' => ['name' => 'craft', 'type' => 'json', 'default' => '[]'],
			'weight' => ['name' => 'weight', 'type' => 'decimal(3,2)', 'default' => '0.05'],
			'rarity' => ['name' => 'rarity', 'type' => 'smallint unsigned', 'default' => '0'],
			'stat-modifier' => ['name' => 'stat-modifier', 'type' => 'json', 'default' => '[]'], //ex: {"attack": 15, "defense": 2}
			'functions' => ['name' => 'functions', 'type' => 'json', 'default' => '[]'],//ex: {equippable, non-tradeable, special-attack: dark-light (opens data from dark-light module (create hook in battles for special attacks.))} 
			'key-PRIMARY' => ['name' => 'PRIMARY', 'type' => 'primary key', 'unique' => '1', 'columns' => 'id'],
			'key-name' => ['name' => 'name', 'type' => 'key', 'columns' => 'name'],
			'key-value' => ['name' => 'value', 'type' => 'key', 'columns' => 'value'],
			'key-value-modifier' => ['name' => 'value-modifier', 'type' => 'key', 'columns' => 'value-modifier'],
			'key-weight' => ['name' => 'weight', 'type' => 'key', 'columns' => 'weight'],
			'key-rarity' => ['name' => 'rarity', 'type' => 'key', 'columns' => 'rarity'],
		],
		'inventory' => [
			'acctid' => ['name' => 'acctid', 'type' => 'int(11) unsigned', 'default' => 'NULL'],
			'max-slots' => ['name' => 'max-slots', 'type' => 'smallint unsigned', 'default' => '12'],
			'inventory' => ['name' => 'inventory', 'type' => 'json', 'default' => '[]'],
			'special-attack' => ['name' => 'special-attack', 'type' => 'json', 'default' => '[]'], //ex: {saber-attack, dark-light}
			'key-PRIMARY' => ['name' => 'PRIMARY', 'type' => 'primary key', 'unique' => '1', 'columns' => 'acctid'],
			'key-max-slots' => ['name' => 'max-slots', 'type' => 'key', 'columns' => 'max-slots'],
		],
		'bazaar' => [
			'acctid' => ['name' => 'acctid', 'type' => 'int(11) unsigned', 'default' => 'NULL'],
			'name' => ['name' => 'name', 'type' => 'varchar(128)', 'default' => 'A small stand'],
			'advertisement' => ['name' => 'advertisement', 'type' => 'varchar(256)', 'default' => 'Buy from us, pls.'],
			'for-sale' => ['name' => 'for-sale', 'type' => 'json', 'default' => '[]'],
			'staff' => ['name' => 'staff', 'type' => 'json', 'default' => '[]'],
			'key-PRIMARY' => ['name' => 'PRIMARY', 'type' => 'primary key', 'unique' => '1', 'columns' => 'acctid'],
			'key-name' => ['name' => 'name', 'type' => 'key', 'columns' => 'name'],
			'key-advertisement' => ['name' => 'name', 'type' => 'key', 'columns' => 'advertisement'],
		]
	];
    module_addhook('newday');
    module_addhook('superuser');
	module_addhook('footer-creatures'); // Add drops to creatures.
    return true;
}

function items_uninstall()
{
    return true;
}

function items_dohook($hook, $args)
{
    switch ($hook) {
        case 'newday':
            $items = json_decode(get_module_pref('items'), true);
            $inUse = json_decode(get_module_pref('in_use'), true);
            foreach ($inUse as $item => $amt) {
                if (!in_array($item, $items)) {
                    unset($items[$item]);
                }
                else if ($items[$item] > $amt) {
                    $items[$item] = $amt;
                }
            }
            set_module_pref('items', json_encode($items, true));
            break;
    }
    return $args;
}