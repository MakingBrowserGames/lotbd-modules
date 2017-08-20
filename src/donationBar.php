<?php

function donationBar_getmoduleinfo()
{
    return [
        'name' => 'Donation Bar',
        'version' => '1.0.0',
        'author' => 'Stephen Kise',
        'category' => 'Administrative',
        'description' =>
            'Adds a bar and message stating how much was donated to the server.',
        'settings' => [
            'message' => 'Message to be displayed above the bar:, text| Funds Goal:',
            'base' => 'How much do we have towards our goal?, viewonly| 0.00',
            'goal' => 'What is our goal amount?, int| 10.00',
            'display_totals' => 'Should we display numerical values?, bool| 0'
        ],
    ];
}

function donationBar_install()
{
    module_addhook('css');
    module_addhook('donation_adjustments');
    module_addhook('everyfooter');
    return true;
}

function donationBar_uninstall()
{
    return true;
}

function donationBar_dohook($hook, $args)
{

    switch ($hook) {
        case 'css':
            $args['DonationBar'] = 'donationBar';
            break;
        case 'donation_adjustments':
            global $session;
            if ($args['amount'] > 0) {
                increment_module_setting('base', $args['amount']);
            }
            break;
        case 'everyfooter';
            $settings = get_all_module_settings();
            if (!is_array($args['paypal'])) {
                $args['paypal'] = [];
            }
            $totals = '';
            if ($settings['display_totals'] == 1) {
                $totals = sprintf_translate(
                    "`@\$%0.2f `0/ `^\$%0.2f",
                    (float) $settings['base'],
                    (float) $settings['goal']
                );
            }
            $percent = round($settings['base'] / $settings['goal'], 2)*100;
            if ($percent > 100) {
                $percent = 100;
            }
            $fillWidth = round(1.5 * $percent, 0);
            $unfilled = 150 - $fillWidth;
            $bar = appoencode(
                "`c{$settings['message']} $totals`c
                <div class='fund-drive' align='center'>
                    <table class='fund-drive-bar'>
                        <tr>
                            <td class='fund-drive-filled' width='{$fillWidth}px'>
                            </td>
                            <td class='fund-drive-unfilled' width='{$unfilled}px'>
                            </td>
                        </tr>
                    </table>
                </div>",
                true
            );
            array_push($args['paypal'], $bar);
            break;
    }
    return $args;
}
