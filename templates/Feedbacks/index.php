<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);

if (empty($feedbacks['customers']) && empty($feedbacks['manufacturers'])) {
    echo '<h1 style="margin-bottom:40px;">' . $title_for_layout . '</h1>';
}

if (!empty($feedbacks['customers'])) {
    echo '<h1 style="margin-bottom:40px;">' . __('Members_feedback') . '</h1>';
    foreach($feedbacks['customers'] as $feedback) {
        echo $this->element('feedback/quote', [
            'quote' => $feedback->text,
            'metaData' => $feedback->privatized_name,
        ]);
    }
}

if (!empty($feedbacks['manufacturers'])) {
    echo '<h1 style="margin-bottom:40px;">' . __('Manufacturers_feedback') . '</h1>';
    foreach($feedbacks['manufacturers'] as $feedback) {
        echo $this->element('feedback/quote', [
            'quote' => $feedback->text,
            'metaData' => $feedback->privatized_name,
        ]);
    }
}


?>

