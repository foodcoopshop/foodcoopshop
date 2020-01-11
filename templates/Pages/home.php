<?php

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);

echo $this->element('acceptUpdatedTermsOfUseForm');

if (!empty($blogPosts) && $blogPosts->count() > 0) {
    echo '<h1>'.__('News').'</h1>';
}
echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts,
    'useCarousel' => false
]);

if (Configure::read('appDb.FCS_FOODCOOPS_MAP_ENABLED')) {
    echo $this->element('foodCoopShopInstancesMap', [
        'isFirstElement' => empty($blogPosts) || $blogPosts->count() == 0
    ]);
}
?>
