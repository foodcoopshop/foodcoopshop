<?php
declare(strict_types=1);

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
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

?>
<div class="c1">
    <?php
        $menu = $this->Menu->buildPageMenu($pagesForFooter);
        if (Configure::read('app.isBlogFeatureEnabled')) {
            $menu[] = ['name' => __('Blog_archive'), 'slug' => $this->Slug->getBlogList()];
        }
        if (Configure::read('app.termsOfUseEnabled')) {
            $menu[] = ['name' => __('Terms_of_use'), 'slug' => $this->Slug->getTermsOfUse()];
        }
        $menu[] = ['name' => __('Privacy_policy'), 'slug' => $this->Slug->getPrivacyPolicy()];
        $menu[] = ['name' => __('List_of_allergens'), 'slug' => $this->Slug->getListOfAllergens()];
        echo '<h2>'.__('Information').'</h2>';
        echo $this->Menu->render($menu, ['id' => 'footer-menu', 'class' => 'menu']);
    ?>
</div>

<div class="c2">
    <h2><?php echo __('Contact'); ?></h2>
    <p>
        <?php
           echo Configure::read('appDb.FCS_APP_NAME') . '<br />';
           echo $this->Html->getAddressFromAddressConfiguration();
        ?>
    </p>
</div>

<?php
    if (Configure::read('appDb.FCS_FOOTER_CMS_TEXT') != '') {
        echo '<p class="additional-footer-info">'.Configure::read('appDb.FCS_FOOTER_CMS_TEXT').'</p>';
    }

    $socialMediaLinks = [];
    if (Configure::read('appDb.FCS_APP_EMAIL') != '') {
        $socialMediaLinks[] = StringComponent::hideEmail(Configure::read('appDb.FCS_APP_EMAIL'), '\'<i class="fas fa-envelope fa-2x fa-fw" title="E-Mail"></i>\'');
    }
    if (Configure::read('appDb.FCS_FACEBOOK_URL') != '') {
        $socialMediaLinks[] = '<a target="_blank" title="Facebook: ' . Configure::read('appDb.FCS_APP_NAME')  . '" href="' . Configure::read('appDb.FCS_FACEBOOK_URL') . '"><i class="fab fa-2x fa-fw fa-facebook"></i></a>';
    }
    if (Configure::read('appDb.FCS_INSTAGRAM_URL') != '') {
        $socialMediaLinks[] = '<a target="_blank" title="Instagram: ' . Configure::read('appDb.FCS_APP_NAME') . '" href="' . Configure::read('appDb.FCS_INSTAGRAM_URL') . '"><i class="fab fa-2x fa-fw fa-instagram"></i></a>';
    }
    if (Configure::read('appDb.FCS_SHOW_FOODCOOPSHOP_BACKLINK')) {
        $backlinkInnerHtml = 'foodcoopshop.com';
        if ($isMobile) {
            $backlinkInnerHtml = '<i class="fas fa-2x fa-fw fa-external-link-square-alt"></i>';
        }
        $socialMediaLinks[] = '<a class="fcs-backlink" title="Foodcoop Software" target="_blank" href="https://www.foodcoopshop.com">'.$backlinkInnerHtml.'</a>';
    }
    if (!empty($socialMediaLinks)) {
        echo '<div class="bottom">';
            echo join(' ', $socialMediaLinks);
        echo '</div>';
    }
?>