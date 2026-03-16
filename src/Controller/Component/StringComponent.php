<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Services\SanitizeService;
use Cake\Controller\Component;
use Cake\Utility\Text;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class StringComponent extends Component
{

    public static function removeWhitespace(string $string): string
    {
        return preg_replace('/\s+/', '', $string);
    }

    /**
     * \x{2600}-\x{27BF}   : Miscellaneous Symbols and Dingbats (Hearts, Stars, Weather)
     * \x{1F000}-\x{1F9FF} : Supplemental Symbols (The "Modern" 4-byte Emojis)
     * \x{1F1E0}-\x{1F1FF} : Flags / Regional Indicators
     */
    public static function cleanForPdfGeneration(string $string): string
    {
        $pattern = '/[\x{2600}-\x{27BF}]|[\x{1F000}-\x{1F9FF}]|[\x{1F1E0}-\x{1F1FF}]/u';

        return preg_replace($pattern, '', $string);
    }

    public static function addProtocolToUrl(?string $url): string
    {
        if ($url === null || $url == '') {
            return '';
        }
        if (!preg_match('/^http(s)?\:\/\//', $url)) {
            $url = 'https://'.$url;
        }
        return $url;
    }

    public static function removeIdFromSlug(string $slug): string
    {
        return preg_replace('/^([\d]+)-(.*)$/', '$2', $slug);
    }

    public static function removeSpecialChars(string $string): string
    {
        return preg_replace('/[<>;=#{}]/u', '', $string);
    }

    public static function prepareWysiwygEditorHtml(string $string, string $allowedTags): string
    {
        $sanitizeService = new SanitizeService();
        $string = $sanitizeService->stripBase64DataFromImageTag($string);
        return strip_tags(htmlspecialchars_decode(trim($string)), $allowedTags);
    }

    public static function br2space(string $string): string
    {
        return preg_replace('/<br\s*\/?>/i', " ", $string);
    }

    public static function nl2br2(string $string): string
    {
        $string = str_replace([
            "\r\n",
            "\r",
            "\n"
        ], "<br />", $string);
        return $string;
    }

    public static function slugify(string $string): string
    {
        $string = html_entity_decode($string);
        $specialCases = [
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss'
        ];
        $string = str_replace(array_keys($specialCases), array_values($specialCases), $string);
        $string = Text::slug($string);
        return $string;
    }

    public static function createRandomString(int $n = 6): string
    {
        $characters = "abcdefghijkmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    /**
     * http://www.maurits.vdschee.nl/php_hide_email/
     */
    public static function hideEmail(string $email, string $innerHtml='d'): string
    {
        $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';

        // assure that $key never matches the email regexp
        $dotPos = 1;
        $atPos = 0;
        while($dotPos > $atPos) {
            $key = str_shuffle($character_set);
            $atPos = strpos($key, '@');
            $dotPos = strpos($key, '.');
        }
        
        $cipher_text = '';
        $id = 'e' . rand(1, 999999999);
        for ($i = 0; $i < strlen($email); $i += 1) {
            $cipher_text .= $key[strpos($character_set, $email[$i])];
        }

        $script = 'var a="' . $key . '";var b=a.split("").sort().join("");var c="' . $cipher_text . '";var d="";';
        $script .= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';
        $script .= 'document.getElementById("' . $id . '").innerHTML="<a href=\\"mailto:"+d+"\\">"+'.$innerHtml.'+"</a>"';
        $script = "eval(\"" . str_replace([
            "\\",
            '"'
        ], [
            "\\\\",
            '\"'
        ], $script) . "\")";
        $script = '<script type="text/javascript">/*<![CDATA[*/' . $script . '/*]]>*/</script>';

        return '<span id="' . $id . '">[javascript protected email address]</span>' . $script;
    }
}
