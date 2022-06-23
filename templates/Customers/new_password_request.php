<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1><?php echo $title_for_layout; ?></h1>

    <?php
        echo $this->Form->create(
            $customer,
            [
                'url' => $this->Slug->getNewPasswordRequest(),
                'novalidate' => 'novalidate'
            ]
        );
    ?>

    <p><?php echo __('Please_enter_your_email_address_and_click_on_send_button.'); ?></p>

    <?php echo $this->Form->control('Customers.email', ['label' => __('Email')]); ?>
    <button type="submit" class="btn btn-success"><?php echo __('Send'); ?></button>

<?php echo $this->Form->end(); ?>
