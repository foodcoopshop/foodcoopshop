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
?>
<div id="pages">

     <?php
    $this->element('addScript', array(
        'script' => Configure::read('app.jsNamespace') . ".Admin.init();
        "
    ));
    ?>
   
    <div class="filter-container">
		<h1>Seiten</h1>
       	<?php echo $this->Form->input('customerId', array('type' => 'select', 'label' => '', 'empty' => 'alle Benutzer', 'options' => $customersForDropdown, 'selected' => isset($customerId) ? $customerId: '')); ?>
        <button id="filter" class="btn btn-success">
			<i class="fa fa-search"></i> Filtern
		</button>
		<div class="right">
            <?php
            echo '<div id="add-page-button-wrapper" class="add-button-wrapper">';
            echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neue Seite erstellen', $this->Slug->getPageAdd(), array(
                'class' => 'btn btn-default',
                'escape' => false
            ));
            echo '</div>';
            ?>
        </div>

	</div>

	<div id="help-container">
		<ul>
			<li>Auf dieser Seite kannst du Seiten verwalten.</li>
		</ul>
	</div>    
    
<?php

echo '<table class="list">';

echo '<tr class="sort">';
echo '<th class="hide">' . $this->Paginator->sort('Page.id_cms', 'ID') . '</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('PageLang.meta_title', 'Seitenitel') . '</th>';
echo '<th>' . $this->Paginator->sort('Page.menu_type', 'Menü') . '</th>';
echo '<th>' . $this->Paginator->sort('Page.position', 'Reihenfolge im Menü') . '</th>';
echo '<th>' . $this->Paginator->sort('Page.is_private', 'Nur für Mitglieder') . '</th>';
echo '<th>' . $this->Paginator->sort('Page.full_width', 'Ganze Breite') . '</th>';
echo '<th>' . $this->Paginator->sort('Page.url', 'Link') . '</th>';
echo '<th>' . $this->Paginator->sort('Customer.name', 'geändert von') . '</th>';
echo '<th>' . $this->Paginator->sort('Page.modified', 'geändert am') . '</th>';
echo '<th>' . $this->Paginator->sort('Page.active', 'Aktiv') . '</th>';
echo '<th></th>';
echo '</tr>';

echo $this->element('pageTreeRows', array(
    'pages' => $pages,
    'subRow' => false
));

echo '<tr>';
echo '<td colspan="12"><b>' . $totalPagesCount . '</b> Datensätze</td>';
echo '</tr>';

echo '</table>';

?>    
</div>