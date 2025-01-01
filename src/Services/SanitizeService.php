<?php
declare(strict_types=1);

namespace App\Services;

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
class SanitizeService
{

    public function trimRecursive($data, $excludedFields = []): array
    {
        array_walk_recursive($data, function (&$item, $key) use ($excludedFields) {
            if (is_string($item) && !in_array($key, $excludedFields)) {
                $item = trim($item);
            }
        });
        return $data;
    }

    public function stripBase64DataFromImageTag($item): string
    {
        $item = preg_replace('/src="(data:image\/[^;]+;base64[^"]+)"/i', 'src="invalid-image"', $item);
        return $item;
    }

    public function stripTagsAndPurifyRecursive($data, $excludedFields = []): array
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
                $item = $this->stripBase64DataFromImageTag($item);
                $item = $purifier->purify($item);
            }
        });
        return $data;
    }
}
