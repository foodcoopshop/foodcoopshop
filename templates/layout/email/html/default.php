<?php
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
use Cake\Core\Configure;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="initial-scale=1.0">
        <meta name="format-detection" content="telephone=no">
        <title><?php echo Configure::read('appDb.FCS_APP_NAME'); ?></title>
    </head>

    <table width="742" cellpadding="0" border="0" cellspacing="0" style="color:#000;font-family:Arial;">
        <tbody>
            <tr>
                <td align="center" valign="middle" style="padding-bottom: 20px;">
                    <a href="<?php echo Configure::read('App.fullBaseUrl'); ?>">
                        <img src="<?php echo Configure::read('App.fullBaseUrl').'/files/images/'.Configure::read('app.logoFileName'); ?>" width="150" />
                    </a>
                </td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #d6d4d4;"></td>
            </tr>
            <tr>
                <td style="padding-bottom: 20px;"></td>
            </tr>
            <tr>
                <td><?php echo $this->fetch('content'); ?></td>
            </tr>
            <tr>
                <td style="padding-top:20px;font-size:12px;">
                    <?php echo __('This_email_was_created_automatically.'); ?>
                        <?php if (isset($showManufacturerUnsubscribeLink) && $showManufacturerUnsubscribeLink) { ?>
                           <?php echo __('You_can_unsubscribe_it_<a href="{0}">in_your_settings</a>.', [Configure::read('App.fullBaseUrl') . $this->Slug->getManufacturerMyOptions()]); ?>
                        <?php } ?><br /><br />
                        <?php
                        if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED') && isset($newsletterCustomer->newsletter_enabled) && !$newsletterCustomer->newsletter_enabled) {
                                echo __('You_can_subscribe_our_newsletter_<a href="{0}">in_the_admin_areas_menu_point_my_data</a>.', [Configure::read('App.fullBaseUrl') . $this->Slug->getCustomerProfile()]);
                                echo '<br /><br />';
                            }
                        ?>
                    --<br />
                    <?php
                        echo Configure::read('appDb.FCS_APP_NAME').'<br />';
                        echo Configure::read('appDb.FCS_APP_ADDRESS').'<br />';
                        echo '<a href="mailto:'.Configure::read('appDb.FCS_APP_EMAIL').'">'.Configure::read('appDb.FCS_APP_EMAIL').'</a><br />';
                        echo '<a href="'.Configure::read('App.fullBaseUrl').'">'.$this->MyHtml->getHostWithoutProtocol(Configure::read('App.fullBaseUrl')).'</a>';
                    ?>
                    <?php if (isset($appAuth) && $appAuth->user()) { ?>
                        <br /><br /><?php echo __('Signed_in'); ?>:
                            <?php
                            if ($appAuth->isManufacturer()) {
                                echo $appAuth->getManufacturerName();
                            } else {
                                if (isset($originalLoggedCustomer) && !is_null($originalLoggedCustomer)) {
                                    // for shop orders
                                    echo $originalLoggedCustomer['name'];
                                } else {
                                    echo $appAuth->getUsername();
                                }
                            }
                            ?>
                    <?php } else { ?>
                        <br />
                    <?php  } ?>
                </td>
            </tr>
        </tbody>
    </table>

</html>
