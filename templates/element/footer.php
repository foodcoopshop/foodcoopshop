<?php
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
</div>

<?php

if (Configure::read('appDb.FCS_FOOTER_CMS_TEXT') != '') {
    echo '<p class="additional-footer-info">'.Configure::read('appDb.FCS_FOOTER_CMS_TEXT').'</p>';
}

if ($appAuth->user()) {
    if ($this->Html->paymentIsCashless() && Configure::read('appDb.FCS_BANK_ACCOUNT_DATA') != '') {
        echo '<p class="additional-footer-info" style="margin-bottom: 0;"><b>'.__('Bank_account_credit_balance').':</b> '.Configure::read('appDb.FCS_BANK_ACCOUNT_DATA').'</p>';
    }
}
?>

<?php
    $socialMediaLinks = [];
    if (Configure::read('appDb.FCS_APP_EMAIL') != '') {
        $socialMediaLinks[] = '<i class="far fa-envelope fa-2x fa-fw"></i>' . StringComponent::hideEmail(Configure::read('appDb.FCS_APP_EMAIL'));
    }
    if (Configure::read('appDb.FCS_FACEBOOK_URL') != '') {
        $socialMediaLinks[] = '<a target="_blank" title="Facebook: ' . Configure::read('appDb.FCS_APP_NAME')  . '" href="' . Configure::read('appDb.FCS_FACEBOOK_URL') . '"><i class="fab fa-2x fa-fw fa-facebook-square"></i></a>';
    }
    if (Configure::read('appDb.FCS_INSTAGRAM_URL') != '') {
        $socialMediaLinks[] = '<a target="_blank" title="Instagram: ' . Configure::read('appDb.FCS_APP_NAME') . '" href="' . Configure::read('appDb.FCS_INSTAGRAM_URL') . '"><i class="fab fa-2x fa-fw fa-instagram-square"></i></a>';
    }
    if (Configure::read('appDb.FCS_SHOW_FOODCOOPSHOP_BACKLINK')) {
        $socialMediaLinks[] = '<a class="fcs-backlink" title="Foodcoop Software" target="_blank" href="https://www.foodcoopshop.com">foodcoopshop.com</a>';
    }
    if (!empty($socialMediaLinks)) {
        echo '<div class="bottom">';
            echo join(' ', $socialMediaLinks);
        echo '</div>';
    }
?>