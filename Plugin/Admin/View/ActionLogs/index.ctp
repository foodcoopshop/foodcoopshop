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
<div id="actionLogs">

        <?php
        $this->element('addScript', array(
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ");
        "
        ));
    ?>

    <div class="filter-container">
        <?php if ($appAuth->isManufacturer() || $appAuth->isSuperadmin() || $appAuth->isAdmin()) { ?>
            <?php echo $this->Form->input('type', array('type' => 'select', 'empty' => 'Alle Aktivitäten', 'label' => '', 'options' => $actionLogModel->getTypesForDropdown($appAuth), 'selected' => isset($type) ? $type : '')); ?>
            <?php echo $this->Form->input('customerId', array('type' => 'select', 'label' => '', 'empty' => 'alle Benutzer', 'options' => $customersForDropdown, 'selected' => isset($customerId) ? $customerId: '')); ?>
            <?php echo $this->Form->input('productId', array('type' => 'select', 'label' => '', 'empty' => 'alle Produkte', 'options' => array())); ?>
        <?php } ?>
        <?php if ($appAuth->isCustomer()) { ?>
            <?php echo $this->Form->input('type', array('class' => 'hide', 'label' => '', 'value' => isset($type) ? $type : '')); ?>
        <?php } ?>
        <?php echo $this->element('dateFields', array('dateFrom' => $dateFrom, 'dateTo' => $dateTo)); ?>
        <div class="right"></div>
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
echo '<th class="hide">' . $this->Paginator->sort('CakeActionLog.id', 'ID') . '</th>';
echo '<th>' . $this->Paginator->sort('CakeActionLog.type', 'Aktivitäts-Typ') . '</th>';
echo '<th>' . $this->Paginator->sort('CakeActionLog.date', 'Datum') . '</th>';
echo '<th>' . $this->Paginator->sort('CakeActionLog.text', 'Text') . '</th>';
echo '<th>' . $this->Paginator->sort('Customer.name', 'Benutzer') . '</th>';
echo '<th></th>';
echo '</tr>';

$i = 0;
foreach ($actionLogs as $actionLog) {
    $i ++;

    $actionType = $actionLogModel->types[$actionLog['CakeActionLog']['type']];
    $actionClass = empty($actionType['class']) ? array() : $actionType['class'];
    $actionClass = array_merge(array('data'), $actionClass);

    echo '<tr class="' . implode(' ', $actionClass) . '">';

    echo '<td class="hide">';
    echo $actionLog['CakeActionLog']['id'];
    echo '</td>';

    echo '<td>';
    echo $this->Html->link(
        $actionType['de'],
        '/admin/action_logs/index/type:'.$actionLog['CakeActionLog']['type'].'/productId:'.$productId.'/customerId:'.$customerId.'/dateFrom:'.$dateFrom.'/dateTo:'.$dateTo.(!empty($this->params['named']['sort']) ? '/sort:'.$this->params['named']['sort'] : '').(!empty($this->params['named']['direction']) ? '/direction:'.$this->params['named']['direction'] : '')
    );
    echo '</td>';

    echo '<td>';
    echo $this->Time->formatToDateNTimeLongWithSecs($actionLog['CakeActionLog']['date']);
    echo '</td>';

    echo '<td>';
    echo $actionLog['CakeActionLog']['text'];
    echo '</td>';

    echo '<td>';
    $name = $actionLog['Customer']['name'];
    if (isset($actionLog['Customer']['Manufacturer'])) {
        $name = $actionLog['Customer']['Manufacturer']['name'];
    }
    echo $this->Html->link(
        $name,
        '/admin/action_logs/index/type:'.$type.'/productId:'.$productId.'/customerId:'.$actionLog['Customer']['id_customer'].'/dateFrom:'.$dateFrom.'/dateTo:'.$dateTo.(!empty($this->params['named']['sort']) ? '/sort:'.$this->params['named']['sort'] : '').(!empty($this->params['named']['direction']) ? '/direction:'.$this->params['named']['direction'] : '')
    );
    echo '</td>';

    echo '<td class="center">';

    $showLink = false;
    $targetBlank = true;

    // products
    if ($actionLog['CakeActionLog']['object_id'] > 0 && $actionLog['CakeActionLog']['object_type'] == 'products' && ! ($actionLog['CakeActionLog']['type'] == 'product_set_inactive')) {
        $showLink = true;
        $title = 'Produkt anzeigen';
        $url = $this->Slug->getProductDetail($actionLog['CakeActionLog']['object_id'], '');
    }

    // manufacturers
    if ($actionLog['CakeActionLog']['object_id'] > 0 && $actionLog['CakeActionLog']['object_type'] == 'manufacturers') {
        $showLink = true;
        $title = 'Hersteller anzeigen';
        $url = $this->Slug->getManufacturerDetail($actionLog['CakeActionLog']['object_id'], '');
    }

    // blog_posts
    if ($actionLog['CakeActionLog']['object_id'] > 0 && $actionLog['CakeActionLog']['object_type'] == 'blog_posts' && ! (in_array($actionLog['CakeActionLog']['type'], array(
        'blog_post_deleted'
    )))) {
        $showLink = true;
        $title = 'Blog-Artikel anzeigen';
        $url = $this->Slug->getBlogPostDetail($actionLog['CakeActionLog']['object_id'], '');
    }

    // pages
    if ($actionLog['CakeActionLog']['object_id'] > 0 && $actionLog['CakeActionLog']['object_type'] == 'pages' && ! (in_array($actionLog['CakeActionLog']['type'], array(
        'page_deleted'
    )))) {
        $showLink = true;
        $title = 'Seite anzeigen';
        $url = $this->Slug->getPageDetail($actionLog['CakeActionLog']['object_id'], '');
    }

    // categories
    if ($actionLog['CakeActionLog']['object_id'] > 0 && $actionLog['CakeActionLog']['object_type'] == 'categories' && ! (in_array($actionLog['CakeActionLog']['type'], array(
        'category_deleted'
    )))) {
        $showLink = true;
        $title = 'Kategorie anzeigen';
        $url = $this->Slug->getCategoryDetail($actionLog['CakeActionLog']['object_id'], '');
    }

    // order details
    if ($actionLog['CakeActionLog']['object_id'] > 0 && $actionLog['CakeActionLog']['object_type'] == 'order_details') {
        $showLink = true;
        $title = 'Bestelltes Produkt anzeigen';
        $url = '/admin/order_details/index/orderDetailId:' . $actionLog['CakeActionLog']['object_id'];
        $targetBlank = false;
    }

    // orders
    if ($actionLog['CakeActionLog']['object_id'] > 0 && $actionLog['CakeActionLog']['object_type'] == 'orders') {
        $showLink = true;
        $title = 'Bestellung anzeigen';
        // manually add ORDER_STATE_CANCELLED to orderState to show cancelled orders
        $url = '/admin/orders/index/orderId:' . $actionLog['CakeActionLog']['object_id'] . '/orderState:' . join(',', array_keys($this->Html->getOrderStates()));
        $targetBlank = false;
    }

    if ($showLink) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), array(
            'title' => $title,
            'target' => $targetBlank ? '_blank' : ''
        ), $url);
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="10"><b>' . $i . '</b> Datensätze</td>';
echo '</tr>';

echo '</table>';

?>
</div>
