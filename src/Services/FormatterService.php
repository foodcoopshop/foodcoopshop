<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services;

class FormatterService
{
    public static function assureCorrectFloat(float $float): float
    {
        $float = round($float, 2); // rounding avoids problems with very tiny numbers (eg. 2.8421709430404E-14)
        $float = $float + 0; // "+ 0" converts -0,00 to 0,00
        return $float;
    }
}