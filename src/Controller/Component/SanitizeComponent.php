<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SanitizeComponent extends Component
{

    /**
     * @param array $array
     * @return array
     */
    public function trimRecursive($data, $excludedFields = [])
    {
        array_walk_recursive($data, function (&$item, $key) use ($excludedFields) {
            if (is_string($item) && !in_array($key, $excludedFields)) {
                $item = trim($item);
            }
        });
        return $data;
    }

    public static function stripBase64DataFromImageTag($item)
    {
        $item = preg_replace('/src="(data:image\/[^;]+;base64[^"]+)"/i', 'src="invalid-image"', $item);
        return $item;
    }


    /**
     * @param array $array
     * @return array
     */
    public function stripTagsAndPurifyRecursive($data, $excludedFields = [])
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', TMP . 'cache' . DS . 'html_purifier');
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%(.*)%');
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('Attr.EnableID', true); // enables anchors: <a name="xxx">Text</a>
        $purifier = new \HTMLPurifier($config);

        array_walk_recursive($data, function (&$item, $key) use ($excludedFields, $purifier) {
            if (is_string($item)) {
                if (!in_array($key, $excludedFields)) {
                    $item = strip_tags($item);
                }
                // avoid xss attacks
                $item = self::stripBase64DataFromImageTag($item);
                $item = $purifier->purify($item);
            }
        });
        return $data;
    }
}
