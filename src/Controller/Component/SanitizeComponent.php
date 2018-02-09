<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SanitizeComponent extends Component
{

    /**
     * @param array $array
     * @return array
     */
    public function trimRecursive($data)
    {
        array_walk_recursive($data, function(&$item, $key) {
            if (is_string($item)){
                $item = trim($item);
            }
        });
        return $data;
    }
    
    /**
     * @param array $array
     * @return array
     */
    public function stripTagsRecursive($data)
    {
        array_walk_recursive($data, function(&$item, $key) {
            if (is_string($item)){
                $item = strip_tags($item);
            }
        });
        return $data;
    }

}
