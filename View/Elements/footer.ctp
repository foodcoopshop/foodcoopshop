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
        echo '<h2>Informationen</h2>';
        echo $this->Menu->render($menu, array('id' => 'footer-menu', 'class' => 'menu'));
    ?>
</div>

<div class="second-column">
	<h2>Kontakt</h2>
	<p><i class="fa fa-map-marker fa-2x fa-fw"></i> <span><?php
	   $address = explode("\n", Configure::read('app.addressForPdf'));
	   $address = array_filter($address); // remove empty elements
	   $email = array_pop($address); // remove last element
	   echo implode(', ', $address);
	?></span></p>
	<p><?php
	   echo '<i class="fa fa-envelope-o fa-2x fa-fw"></i> <span>E-Mail: '.StringComponent::hide_email($email).'</span>';
	?></p>
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

<?php if (Configure::read('app.db_config_FCS_FACEBOOK_URL') != '') { ?>
    
    <div id="facebook-wrapper">
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/de_DE/sdk.js#xfbml=1&version=v2.6";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
        
        <div class="fb-page" data-href="<?php echo Configure::read('app.db_config_FCS_FACEBOOK_URL'); ?>" data-small-header="false" data-width="500" data-hide-cover="false" data-show-facepile="true"><div class="fb-xfbml-parse-ignore"><blockquote cite="<?php echo Configure::read('app.db_config_FCS_FACEBOOK_URL'); ?>"><a href="<?php echo Configure::read('app.db_config_FCS_FACEBOOK_URL'); ?>"><?php echo Configure::read('app.name'); ?></a></blockquote></div></div>
	</div>

<?php } ?>

<?php if (Configure::read('app.db_config_FCS_SHOW_FOODCOOPSHOP_BACKLINK')) { ?>
	<a class="fcs-backlink" target="_blank" href="https://www.foodcoopshop.com">&copy; <?php echo date('Y'); ?> foodcoopshop.com</a>
<?php } ?>