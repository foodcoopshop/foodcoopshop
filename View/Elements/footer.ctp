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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<div class="first-column">
    <?php
        $menu = $this->Menu->buildPageMenu($pagesForFooter);
        $menu[] = array('name' => 'Nutzungsbedingungen', 'slug' => '/nutzungsbedingungen');
        $menu[] = array('name' => 'Datenschutzerklärung', 'slug' => '/datenschutzerklaerung');
        echo '<h2>Informationen</h2>';
        echo $this->Menu->render($menu, array('id' => 'footer-menu', 'class' => 'menu'));
    ?>
</div>

<div class="second-column">
	<h2>Kontakt</h2>
	<p><i class="fa fa-map-marker fa-2x fa-fw"></i> <span>
	<?php
	   echo Configure::read('app.name').', ';
	   echo implode(', ', $this->Html->getAddressFromAddressConfiguration());
	?></span></p>
	<?php
	   echo '<p><i class="fa fa-envelope-o fa-2x fa-fw"></i> <span>E-Mail: '.StringComponent::hide_email($this->Html->getEmailFromAddressConfiguration()).'</span></p>';
	   if (Configure::read('app.db_config_FCS_FACEBOOK_URL') != '') { ?>
    	<p>
    		<a target="_blank" href="<?php echo Configure::read('app.db_config_FCS_FACEBOOK_URL'); ?>"><i class="fa fa-2x fa-fw fa-facebook-square"></i></a>
    		<a target="_blank" href="<?php echo Configure::read('app.db_config_FCS_FACEBOOK_URL'); ?>"><?php echo Configure::read('app.db_config_FCS_FACEBOOK_URL'); ?></a>
    	</p>
	<?php } ?>
</div>

<?php
    
    if (Configure::read('app.db_config_FCS_FOOTER_CMS_TEXT') != '') {
        echo '<p class="additional-footer-info">'.Configure::read('app.db_config_FCS_FOOTER_CMS_TEXT').'</p>';
    }
    
    if ($appAuth->loggedIn()) {
        if ($this->Html->paymentIsCashless() && Configure::read('app.db_config_FCS_BANK_ACCOUNT_DATA') != '') {
            echo '<p class="additional-footer-info" style="margin-bottom: 0;"><b>Kontodaten (Guthaben aufladen):</b> '.Configure::read('app.db_config_FCS_BANK_ACCOUNT_DATA').'</p>';
        }
        if (Configure::read('app.db_config_FCS_MEMBER_FEE_BANK_ACCOUNT_DATA') != '') {
            echo '<p class="additional-footer-info" style="margin-bottom: 0;"><b>Kontodaten (Mitgliedsbeitrag):</b> '.Configure::read('app.db_config_FCS_MEMBER_FEE_BANK_ACCOUNT_DATA').'</p>';
        }
    }
?>

<?php if (Configure::read('app.db_config_FCS_SHOW_FOODCOOPSHOP_BACKLINK')) { ?>
	<a class="fcs-backlink" target="_blank" href="https://www.foodcoopshop.com">&copy; <?php echo date('Y'); ?> foodcoopshop.com</a>
<?php } ?>