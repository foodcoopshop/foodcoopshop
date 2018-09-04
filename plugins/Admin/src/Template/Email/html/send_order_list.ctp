<?php
/**
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
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <tr>
        <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                <?php echo __d('admin', 'Hello'); ?> <?php echo $manufacturer->address_manufacturer->firstname; ?>,
            </td>
    </tr>

    <tr>
        <td>

            <p><?php echo __d('admin', 'please_find_two_order_lists_attached_for_the_next_delivery_(grouped_by_product_and_member).'); ?></p>

            <p>
                <b><?php echo __d('admin', 'Your_personal_manufacturer_panel'); ?>: </b> <a href="<?php echo Configure::read('app.cakeServerName'); ?>/admin"><?php echo Configure::read('app.cakeServerName'); ?>/admin</a>
            </p>

			<p>
				<?php echo __d('admin', 'Help_pages'); ?>: <br />
				<a href="<?php echo $this->MyHtml->getDocsUrl(__d('admin', 'docs_route_manufacturers'));?>"><?php echo $this->MyHtml->getDocsUrl(__d('admin', 'docs_route_manufacturers')); ?></a><br />
				<a href="<?php echo $this->MyHtml->getDocsUrl(__d('admin', 'docs_route_order_handling'));?>"><?php echo $this->MyHtml->getDocsUrl(__d('admin', 'docs_route_order_handling')); ?></a><br />
				<a href="<?php echo $this->MyHtml->getDocsUrl(__d('admin', 'docs_route_products'));?>"><?php echo $this->MyHtml->getDocsUrl(__d('admin', 'docs_route_products')); ?></a>
			</p>
			
            <p>
                <?php echo __d('admin', 'You_can_change_your_imprint_by_yourself.'); ?>
            </p>

            <?php if (!Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) { ?> 
            <p>
                <?php echo __d('admin', 'Your_products_are_only_visible_for_members._To_view_your_products_as_the_member_sees_them_you_need_to_be_logged_in.'); ?>
            </p>
            <?php } ?>

            <p>
            	<?php echo __d('admin', 'For_signing_in_please_use_the_email_address_of_this_message._If_you_want_to_order_products_from_other_manufacturers_please_sign_on_with_a_different_email_address.'); ?>
            </p>
            
            <?php if (!empty($manufacturer->customer)) { ?>
                <p><b><?php echo __d('admin', 'Your_contact_person'); ?>: </b><?php echo $manufacturer->customer->name . ', ' . $manufacturer->customer->email . ', ' . $manufacturer->customer->address_customer->phone_mobile; ?></p>
            <?php } ?>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
