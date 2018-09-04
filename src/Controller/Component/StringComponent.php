<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * StringComponent
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class StringComponent extends Component
{

    /**
     * @param string $url
     * @return string
     */
    public static function addHttpToUrl($url)
    {
        if ($url == '') {
            return $url;
        }
        if (!preg_match('/^http(s)?\:\/\//', $url)) {
            $url = 'http://'.$url;
        }
        return $url;
    }
    /**
     * @param string $string
     * @return string
     */
    public static function removeIdFromSlug($string)
    {
        return preg_replace('/^([\d]+)-(.*)$/', '$2', $string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function removeSpecialChars($string)
    {
        return preg_replace('/[<>;=#{}]/u', '', $string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function decodeJsonFromForm($string)
    {
        return json_decode(str_replace("\r\n", '', $string), true);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function brAndP2nl($string)
    {
        $string = preg_replace("/<p>(.*?)<\/p>/", "$1<br />", $string);
        return preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function nl2br2($string)
    {
        $string = str_replace([
            "\r\n",
            "\r",
            "\n"
        ], "<br />", $string);
        return $string;
    }

    /**
     *
     * http://stackoverflow.com/questions/5305879/automatic-clean-and-seo-friendly-url-slugs
     *
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function slugify($string, $separator = '-', $toLower = true)
    {
        $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $special_cases = [
            '&' => 'and',
            "'" => '',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss'
        ];
        $string = trim($string);
        if ($toLower) {
            $string = mb_strtolower($string, 'UTF-8');
        }
        $string = str_replace(array_keys($special_cases), array_values($special_cases), $string);
        $string = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace("/[^a-zA-Z0-9]/u", "$separator", $string);
        $string = preg_replace("/[$separator]+/u", "$separator", $string);
        return $string;
    }

    public static function slugifyAndKeepCase($string)
    {
        return self::slugify($string, '-', false);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function createRandomString($length = 6)
    {
        $salt = "abcdefghijkmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"; // salt to select chars from
        srand((double) microtime() * 1000000); // start the random generator
        $string = "";
        for ($i = 0; $i < $length; $i ++) {
            $string .= substr($salt, rand() % strlen($salt), 1);
        }
        return $string;
    }

    /**
     * http://www.maurits.vdschee.nl/php_hide_email/
     * @param string $email
     * @return string
     */
    public static function hideEmail($email)
    {
        $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        $key = str_shuffle($character_set);
        $cipher_text = '';
        $id = 'e' . rand(1, 999999999);
        for ($i = 0; $i < strlen($email); $i += 1) {
            $cipher_text .= $key[strpos($character_set, $email[$i])];
        }

        $script = 'var a="' . $key . '";var b=a.split("").sort().join("");var c="' . $cipher_text . '";var d="";';
        $script .= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';
        $script .= 'document.getElementById("' . $id . '").innerHTML="<a href=\\"mailto:"+d+"\\">"+d+"</a>"';
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
