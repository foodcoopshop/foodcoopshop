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
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1><?php echo $title_for_layout; ?></h1>

    <?php
        echo $this->Form->create(
            $customer,
            [
                'url' => $this->Slug->getNewPasswordRequest(),
                'novalidate' => 'novalidate'
            ]
        );
    ?>
    
    <p>Bitte gib deine E-Mail-Adresse an und klicke dann auf "Senden".</p>
    <p>Wir senden dir dann einen Link zu, mit dem du das neue Passwort generieren kannst.</p>
    
    <?php echo $this->Form->input('Customers.email', ['label' => 'E-Mail']); ?>
    <button type="submit" class="btn btn-success">Senden</button>
    
<?php echo $this->Form->end(); ?>
