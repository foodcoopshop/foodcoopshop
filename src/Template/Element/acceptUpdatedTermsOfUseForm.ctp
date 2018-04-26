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
    <h2>Hallo <?php echo $appAuth->getUserFirstname(); ?>,</h2>
    <p>um diese Plattform weiterhin verwenden zu können, <b><a class="terms-of-use-overlay" href="#terms-of-use">lese bitte die geänderten Nutzungsbedingungen</a></b> und akzeptiere sie.</p>
    <form action="<?php echo $this->Slug->getAcceptTermsOfUse(); ?>" id="AcceptTermsOfUseForm" method="post" accept-charset="utf-8">
        <?php
            echo '<div id="terms-of-use" class="featherlight-overlay">';
        if ($appAuth->isManufacturer()) {
            echo $this->element('legal/termsOfUseForManufacturers');
        } else {
            echo $this->element('legal/termsOfUse');
        }
            echo '</div>';
            echo $this->Form->control('Customers.terms_of_use_accepted_date_checkbox', [
                'label' => 'Ich akzeptiere die <b><a class="terms-of-use-overlay" href="#terms-of-use">Nutzungsbedingungen</a></b>',
                'type' => 'checkbox',
                'id' => 'CustomerTermsOfUseAcceptedDateCheckbox_'.StringComponent::createRandomString(),
                'escape' => false
            ]);
        ?>
        <br />
        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Speichern</button>
    </form>
</div>