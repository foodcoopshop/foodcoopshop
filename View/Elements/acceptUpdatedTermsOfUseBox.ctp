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
?>
<?php

if (!$appAuth->loggedIn() || $appAuth->termsOfUseAccepted()) return false;

$this->element('addScript', array('script' =>
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForHref('.accept-updated-terms-of-use-box .input.checkbox label a');"
));
?>
<div class="accept-updated-terms-of-use-box">
	<h2>Hallo <?php echo $appAuth->getUserFirstname(); ?>,</h2>
	<p>die Nutzungsbedingungen der Plattform (FoodCoopShop) haben sich geändert.<br />
	Um sie weiterhin verwenden zu können, akzeptiere bitte die Nutzungsbedingungen.</p>
	<form action="/nutzungsbedingungen-akzeptieren" id="RegistrationForm" method="post" accept-charset="utf-8">
    	<?php
        	echo '<div id="terms-of-use" class="featherlight-overlay">';
        	echo $this->element('legal/termsOfUse');
        	echo '</div>';
        	echo $this->Form->input('Customer.terms_of_use_accepted_date', array(
        	    'label' => 'Ich akzeptiere die <b><a href="#terms-of-use">Nutzungsbedingungen</a></b>',
        	    'type' => 'checkbox'
        	));
    	?>
    	<br />
    	<button type="submit" class="btn btn-success"><i class="fa fa-check fa-lg"></i> Speichern</button>
	</form>
</div>