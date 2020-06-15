<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\OutputFilter;

/**
 * Strings in any outputs (email, html, pdf) can be replaced using
 * `app.outputStringReplacements`
 *
 * Example to be set in custom_config.php
 *
 * 'outputStringReplacements' => [
 *     'Manfacturers' => 'Producers',
 *  ]
 */
class OutputFilter
{
    public static function replace(string $text, array $searchAndReplace): string
    {

        if (empty($searchAndReplace)) {
            return $text;
        }

        foreach($searchAndReplace as $search => $replace) {
            $text = preg_replace('`' . $search . '`', $replace, $text);
        }

        return $text;

    }

}
