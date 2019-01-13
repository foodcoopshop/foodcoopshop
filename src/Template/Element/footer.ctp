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
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

?>
<div class="first-column">
    <?php
        $menu = $this->Menu->buildPageMenu($pagesForFooter);
        if (Configure::read('app.termsOfUseEnabled')) {
            $menu[] = ['name' => __('Terms_of_use'), 'slug' => $this->Slug->getTermsOfUse()];
        }
        $menu[] = ['name' => __('Privacy_policy'), 'slug' => $this->Slug->getPrivacyPolicy()];
        $menu[] = ['name' => __('List_of_allergens'), 'slug' => $this->Slug->getListOfAllergens()];
        echo '<h2>'.__('Information').'</h2>';
        echo $this->Menu->render($menu, ['id' => 'footer-menu', 'class' => 'menu']);
    ?>
</div>

<div class="second-column">
    <h2><?php echo __('Contact'); ?></h2>
    <p><i class="fas fa-map-marker-alt fa-2x fa-fw"></i> <span>
    <?php
       echo Configure::read('appDb.FCS_APP_NAME').', ';
       echo str_replace('<br />', ', ', $this->Html->getAddressFromAddressConfiguration());
    ?></span></p>
    <?php
       echo '<p><i class="far fa-envelope fa-2x fa-fw"></i> <span>'.__('Email').': '.StringComponent::hideEmail($this->Html->getEmailFromAddressConfiguration()).'</span></p>';
    if (Configure::read('appDb.FCS_FACEBOOK_URL') != '') { ?>
        <p>
            <a target="_blank" href="<?php echo Configure::read('appDb.FCS_FACEBOOK_URL'); ?>"><i class="fab fa-2x fa-fw fa-facebook-square"></i></a>
            <a target="_blank" href="<?php echo Configure::read('appDb.FCS_FACEBOOK_URL'); ?>"><?php echo Configure::read('appDb.FCS_FACEBOOK_URL'); ?></a>
        </p>
    <?php
    }
    ?>
</div>

<?php

if (Configure::read('appDb.FCS_FOOTER_CMS_TEXT') != '') {
    echo '<p class="additional-footer-info">'.Configure::read('appDb.FCS_FOOTER_CMS_TEXT').'</p>';
}

if ($appAuth->user()) {
    if ($this->Html->paymentIsCashless() && Configure::read('appDb.FCS_BANK_ACCOUNT_DATA') != '') {
        echo '<p class="additional-footer-info" style="margin-bottom: 0;"><b>'.__('Bank_account_credit_balance').':</b> '.Configure::read('appDb.FCS_BANK_ACCOUNT_DATA').'</p>';
    }
    if (Configure::read('app.memberFeeEnabled') && Configure::read('appDb.FCS_MEMBER_FEE_BANK_ACCOUNT_DATA') != '') {
        echo '<p class="additional-footer-info" style="margin-bottom: 0;"><b>'.__('Bank_account_member_fee').':</b> '.Configure::read('appDb.FCS_MEMBER_FEE_BANK_ACCOUNT_DATA').'</p>';
    }
}
?>

<?php if (Configure::read('appDb.FCS_SHOW_FOODCOOPSHOP_BACKLINK')) { ?>
    <a class="fcs-backlink" title="Foodcoop Software" target="_blank" href="https://www.foodcoopshop.com">foodcoopshop.com</a>
<?php } ?>
