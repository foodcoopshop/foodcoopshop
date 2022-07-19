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
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1><?php echo $title_for_layout; ?></h1>

<?php
foreach($feedbacks as $feedback) {
    echo '<div class="feedback-wrapper" style="margin-bottom:10px;">';
        echo '"' . StringComponent::nl2br2($feedback->text) . '"<br />';
        echo '<i>' . $feedback->privatized_name . ', ' . $feedback->modified->i18nFormat($this->Time->getI18Format('DateLong2')) . '</i>';
    echo '</div>';
}
?>