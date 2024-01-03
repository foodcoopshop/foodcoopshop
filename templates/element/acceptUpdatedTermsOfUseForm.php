<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\I18n\I18n;

if ($identity === null  || $identity->termsOfUseAccepted()) {
    return false;
}

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".ModalText.init('.accept-updated-terms-of-use-form a.terms-of-use-overlay');"
]);
?>
<div class="accept-updated-terms-of-use-form">
    <h2><?php echo __('Hello'); ?> <?php echo $identity->firstname; ?>,</h2>
    <?php
        $termsOfUseTermsLink = '<a class="terms-of-use-overlay" href="javascript:void(0);" data-element-selector="#terms-of-use">'.__('terms_and_conditions').'</a>';
    ?>
    <p><?php echo __('if_you_want_to_continue_to_use_this_platform_please_read_and_accept_the_{0}.', ['<b>' . $termsOfUseTermsLink . '</b>']); ?></p>
    <?php
        echo $this->Form->create(
            null,
            [
                'url' => $this->Slug->getAcceptTermsOfUse(),
                'id' => 'AcceptTermsOfUseForm',
            ]
        );
           echo '<div id="terms-of-use" class="hide">';
           if ($identity->isManufacturer()) {
            echo $this->element('legal/'.I18n::getLocale() . '/' . $this->Html->getLegalTextsSubfolder() . '/termsOfUseForManufacturers');
           } else {
            echo $this->element('legal/'.I18n::getLocale() . '/' . $this->Html->getLegalTextsSubfolder() . '/termsOfUse');
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
        <button type="submit" class="btn btn-success"><i class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></button>
    <?php echo $this->Form->end(); ?>
</div>