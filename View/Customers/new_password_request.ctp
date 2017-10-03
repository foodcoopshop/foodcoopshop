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
$this->element('addScript', array('script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
));
?>

<h1><?php echo $title_for_layout; ?></h1>

<form novalidate="novalidate" action="<?php echo $this->Slug->getNewPasswordRequest();?>" method="post">
    
    <p>Bitte gib deine E-Mail-Adresse an und klicke dann auf "Senden".</p>
    <p style="margin-bottom: 20px;">Wir senden dir dann einen Link zu, mit dem du das neue Passwort
    <br /> generieren kannst.</p>
    
    <?php echo $this->Form->input('Customer.email', array('label' => 'E-Mail')); ?>
    <button type="submit" class="btn btn-success">Senden</button>
    
</form>
