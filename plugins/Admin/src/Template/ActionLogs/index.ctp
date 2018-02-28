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

?>
<div id="actionLogs">

        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ");
        "
        ]);
    ?>

    <div class="filter-container">
    	<?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php if ($appAuth->isManufacturer() || $appAuth->isSuperadmin() || $appAuth->isAdmin()) { ?>
                <?php echo $this->Form->control('type', ['type' => 'select', 'empty' => 'Alle Aktivitäten', 'label' => '', 'options' => $actionLogModel->getTypesForDropdown($appAuth), 'default' => isset($type) ? $type : '']); ?>
                <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => 'alle Benutzer', 'options' => $customersForDropdown, 'default' => isset($customerId) ? $customerId: '']); ?>
                <?php echo $this->Form->control('productId', ['type' => 'select', 'label' => '', 'empty' => 'alle Produkte', 'options' => []]); ?>
            <?php } ?>
            <?php if ($appAuth->isCustomer()) { ?>
                <?php echo $this->Form->control('type', ['class' => 'hide', 'label' => '', 'value' => isset($type) ? $type : '']); ?>
            <?php } ?>
            <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameFrom' => 'dateFrom', 'nameTo' => 'dateTo']); ?>
            <div class="right"></div>
       	<?php echo $this->Form->end(); ?>
    </div>

    <div id="help-container">
        <ul>
            <li>Auf dieser Seite siehst du alle Aktivitäten im FoodCoopShop.</li>
            <?php if ($appAuth->isManufacturer()) { ?>
                <li>Die stornierten Produkte werden erst ab dem
                20.07.2015 angezeigt.</li>
            <?php } ?>
        </ul>
    </div>

<?php

echo '<table class="list no-hover">';
echo '<tr class="sort">';
echo '<th class="hide">' . $this->Paginator->sort('ActionLogs.id', 'ID') . '</th>';
echo '<th>' . $this->Paginator->sort('ActionLogs.type', 'Aktivitäts-Typ') . '</th>';
echo '<th>' . $this->Paginator->sort('ActionLogs.date', 'Datum') . '</th>';
echo '<th>' . $this->Paginator->sort('ActionLogs.text', 'Text') . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.name', 'Benutzer') . '</th>';
echo '<th></th>';
echo '</tr>';

$i = 0;
foreach ($actionLogs as $actionLog) {
    $i ++;

    $actionType = $actionLogModel->types[$actionLog->type];
    $actionClass = empty($actionType['class']) ? [] : $actionType['class'];
    $actionClass = array_merge(['data'], $actionClass);

    echo '<tr class="' . implode(' ', $actionClass) . '">';

    echo '<td class="hide">';
    echo $actionLog->id;
    echo '</td>';

    echo '<td>';
    echo $this->Html->link(
        $actionType['de'],
        '/admin/action-logs/index/?type='.$actionLog->type.'&productId='.$productId.'&customerId='.$customerId.'&dateFrom='.$dateFrom.'&dateTo='.$dateTo.(!empty($this->request->getQuery('sort')) ? '&sort='.$this->request->getQuery('sort') : '').(!empty($this->request->getQuery('direction')) ? '&direction='.$this->request->getQuery('direction') : '')
    );
    echo '</td>';

    echo '<td>';
    echo $actionLog->date->i18nFormat(Configure::read('DateFormat.de.DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td>';
    echo $actionLog->text;
    echo '</td>';

    echo '<td>';
    $name = $actionLog->customer->name;
    if ($actionLog->customer->manufacturer) {
        $name = $actionLog->customer->manufacturer->name;
    }
    echo $this->Html->link(
        $name,
        '/admin/action-logs/index/?type='.$type.'&productId='.$productId.'&customerId='.$actionLog->customer->id_customer.'&dateFrom='.$dateFrom.'&dateTo='.$dateTo.(!empty($this->request->getQuery('sort')) ? '&sort='.$this->request->getQuery('sort') : '').(!empty($this->request->getQuery('direction')) ? '&direction='.$this->request->getQuery('direction') : '')
    );
    echo '</td>';

    echo '<td class="center">';

    $showLink = false;
    $targetBlank = true;

    // products
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'products' && ! ($actionLog->type == 'product_set_inactive')) {
        $showLink = true;
        $title = 'Produkt anzeigen';
        $url = $this->Slug->getProductDetail($actionLog->object_id, '');
    }

    // manufacturers
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'manufacturers') {
        $showLink = true;
        $title = 'Hersteller anzeigen';
        $url = $this->Slug->getManufacturerDetail($actionLog->object_id, '');
    }

    // blog_posts
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'blog_posts' && ! (in_array($actionLog->type, [
        'blog_post_deleted'
    ]))) {
        $showLink = true;
        $title = 'Blog-Artikel anzeigen';
        $url = $this->Slug->getBlogPostDetail($actionLog->object_id, '');
    }

    // pages
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'pages' && ! (in_array($actionLog->type, [
        'page_deleted'
    ]))) {
        $showLink = true;
        $title = 'Seite anzeigen';
        $url = $this->Slug->getPageDetail($actionLog->object_id, '');
    }

    // categories
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'categories' && ! (in_array($actionLog->type, [
        'category_deleted'
    ]))) {
        $showLink = true;
        $title = 'Kategorie anzeigen';
        $url = $this->Slug->getCategoryDetail($actionLog->object_id, '');
    }

    // order details
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'order_details') {
        $showLink = true;
        $title = 'Bestelltes Produkt anzeigen';
        $url = '/admin/order-details/index/?orderDetailId=' . $actionLog->object_id;
        $targetBlank = false;
    }

    // orders
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'orders') {
        $showLink = true;
        $title = 'Bestellung anzeigen';
        // manually add ORDER_STATE_CANCELLED to orderState to show cancelled orders
        $url = '/admin/orders/index/?orderId=' . $actionLog->object_id . '&orderStates[]=' . join(',', array_keys($this->Html->getOrderStates()));
        $targetBlank = false;
    }

    if ($showLink) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), [
            'title' => $title,
            'target' => $targetBlank ? '_blank' : ''
        ], $url);
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="10"><b>' . $this->Html->formatAsDecimal($i, 0) . '</b> Datensätze</td>';
echo '</tr>';

echo '</table>';

?>
</div>
