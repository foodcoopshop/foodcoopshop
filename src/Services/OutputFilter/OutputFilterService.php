<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\OutputFilter;

use App\Controller\Component\StringComponent;

/**
 * Strings in any outputs (email, html, pdf) can be replaced using
 * `app.outputStringReplacements`
 *
 * Example to be set in custom_config.php
 *
 * 'outputStringReplacements' => [
 *     'Manufacturers' => 'Producers',
 *  ]
 */
class OutputFilterService
{
    public static function replace(string $text, array $searchAndReplace): string
    {

        if (empty($searchAndReplace)) {
            return $text;
        }

        $delimiter = '`';

        foreach($searchAndReplace as $search => $replace) {
            $escapedSearch = preg_quote($search, $delimiter);
            $text = preg_replace($delimiter . $escapedSearch . $delimiter, $replace, $text);
        }

        return $text;

    }

    protected static function getEmailsFromString($string)
    {
        preg_match_all(EMAIL_REGEX, $string, $matches);
        return $matches[0];
    }

    public static function protectEmailAdresses(string $text): string
    {
        $emails = self::getEmailsFromString($text);
        foreach($emails as $email) {
            // replace email one by one
            // https://stackoverflow.com/questions/1252693/using-str-replace-so-that-it-only-acts-on-the-first-match#1252710
            $position = strpos($text, $email);
            if ($position !== false) {
                $text = substr_replace($text, StringComponent::hideEmail($email), $position, strlen($email));
            }
        }
        return $text;
    }

}
