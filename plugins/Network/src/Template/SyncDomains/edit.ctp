<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Homepage-Verwaltung', 'Einstellungen');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-close"></i> Abbrechen</a>
    </div>
</div>

<div class="sc"></div>

<?php if ($this->request->getRequestTarget() != $this->Network->getSyncDomainAdd()) { ?>
    <h2 class="warning"><b>Achtung!</b> Eine Remote-Foodcoop zu ändern, kann zu Problemen führen, wenn Hersteller bereits Produkte zugeordnet haben!</h2>
<?php } ?>

<?php

echo $this->Form->create($syncDomain, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isEditMode ? $this->Network->getSyncDomainEdit($syncDomain->id) : $this->Network->getSyncDomainAdd()
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo $this->Form->control('SyncDomains.domain', [
    'label' => 'Remote-Foodcoop <span class="after small">Domain der Foodcoop, <b>muss mit https</b> beginnen (z. B. https://www.fairteiler-scharnstein.at)</span>',
    'required' => true,
    'escape' => false
]);

echo $this->Form->control('SyncDomains.active', [
    'label' => 'Aktiv?',
    'type' => 'checkbox'
]);

if ($this->request->getRequestTarget() != $this->Network->getSyncDomainAdd()) {
    echo $this->Form->input('SyncDomains.delete_sync_domain', [
        'label' => 'Remote-Foodcoop löschen? <span class="after small">Anhaken und dann auf <b>Speichern</b> klicken.</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

echo $this->Form->end();

?>

<div class="sc"></div>
