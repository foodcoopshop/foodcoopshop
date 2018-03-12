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

<h1>Bestellung abgeschlossen</h1>

<p><b>Vielen Dank, deine Bestellung wurde erfolgreich abgeschlossen.</b></p>

<ul>

    <li>Die Bestellbestätigung wurde per E-Mail an <b><?php echo $order['Customers']['email']; ?></b> versendet.</li>
    <li>Bitte hole die bestellten Waren verlässlich am <b><?php echo $this->Time->getFormattedDeliveryDateByCurrentDay(); ?></b> in unserem Abhollager ab.</li>

    <?php if ($this->Html->paymentIsCashless()) { ?>
        <li>Der Warenwert von <b><?php echo $this->Html->formatAsEuro($order['Orders']['total_paid']); ?></b>
            <?php if ($order['Orders']['total_deposit'] > 0) { ?>
                 (zuzüglich <b><?php echo $this->Html->formatAsEuro($order['Orders']['total_deposit']); ?></b> Pfand)
            <?php } ?>
            wurde automatisch von deinem Guthaben abgezogen.</li>
        <li><a class="btn btn-success" href="<?php echo $this->Slug->getMyCreditBalance(); ?>">Guthaben aufladen</a></li>
    <?php } else { ?>
        <li>Bitte vergiss nicht, den Betrag beim Abholen so genau wie möglich in bar mitzunehmen.</li>
    <?php } ?>

</ul>

<?php
if (!empty($blogPosts)) {
    echo '<h2><a href="'.$this->Slug->getBlogList().'">Aktuelles</a></h2>';
    echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts
    ]);
}
?>
