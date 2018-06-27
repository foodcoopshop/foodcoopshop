<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\I18n\I18n;

?>
<?php

if (!$appAuth->user() || $appAuth->termsOfUseAccepted()) {
    return false;
}

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForHref('.accept-updated-terms-of-use-form a.terms-of-use-overlay');"
]);
?>
<div class="accept-updated-terms-of-use-form">
    <h2><?php echo __('Hello'); ?> <?php echo $appAuth->getUserFirstname(); ?>,</h2>
    <?php
        $termsOfUseTermsLink = '<a class="terms-of-use-overlay" href="#terms-of-use">'.__('terms_and_conditions').'</a>';
    ?>
    <p><?php echo __('if_you_want_to_continue_to_use_this_platform_please_read_and_accept_the_{0}.', ['<b>' . $termsOfUseTermsLink . '</b>']); ?></p>
    <form action="<?php echo $this->Slug->getAcceptTermsOfUse(); ?>" id="AcceptTermsOfUseForm" method="post" accept-charset="utf-8">
        <?php
            echo '<div id="terms-of-use" class="featherlight-overlay">';
        if ($appAuth->isManufacturer()) {
            echo $this->element('legal/'.I18n::getLocale().'/termsOfUseForManufacturers');
        } else {
            echo $this->element('legal/'.I18n::getLocale().'/termsOfUse');
        }
            echo '</div>';
            echo $this->Form->control('Customers.terms_of_use_accepted_date_checkbox', [
                'label' => __('I_accept_the_{0}.', ['<b>' . $termsOfUseTermsLink . '</b>']),
                'type' => 'checkbox',
                'id' => 'CustomerTermsOfUseAcceptedDateCheckbox_'.StringComponent::createRandomString(),
                'escape' => false
            ]);
        ?>
        <br />
        <button type="submit" class="btn btn-success"><i class="fa fa-check fa-lg"></i> <?php echo __('Save'); ?></button>
    </form>
</div>