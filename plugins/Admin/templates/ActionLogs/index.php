<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

?>
<div id="actionLogs">

        <?php
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            var datefieldSelector = $('input.datepicker');
            datefieldSelector.datepicker();" .
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('i.fa-envelope');" .
            Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ");" .
            Configure::read('app.jsNamespace') . ".Admin.initCustomerDropdown(" . ($customerId != '' ? $customerId : '0') . ", 1);
        "
        ]);
    ?>

    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php if ($identity->isManufacturer() || $identity->isSuperadmin() || $identity->isAdmin()) { ?>
                <?php echo $this->Form->control('types', ['type' => 'select', 'multiple' => true, 'empty' => __d('admin', 'all_activities'), 'label' => '', 'options' => $actionLogsTable->getTypesForDropdown($identity), 'data-val' => join(',', $types)]); ?>
            <?php } ?>
            <?php if ($identity->isSuperadmin() || $identity->isAdmin()) { ?>
                <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'placeholder' => __d('admin', 'all_users'), 'options' => []]); ?>
            <?php } ?>
            <?php if ($identity->isManufacturer() || $identity->isSuperadmin() || $identity->isAdmin()) { ?>
                <?php echo $this->Form->control('productId', ['type' => 'select', 'label' => '', 'placeholder' => __d('admin', 'all_products'), 'options' => []]); ?>
            <?php } ?>
            <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameFrom' => 'dateFrom', 'nameTo' => 'dateTo']); ?>
            <div class="right">
                <?php
                   echo $this->element('printIcon');
                ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

<?php

$this->Paginator->setPaginated($actionLogs);
echo '<table class="list no-hover">';
echo '<tr class="sort">';
echo '<th class="hide">' . $this->Paginator->sort('ActionLogs.id', 'ID') . '</th>';
echo '<th>' . $this->Paginator->sort('ActionLogs.type', __d('admin', 'Action_log_type')) . '</th>';
echo '<th>' . $this->Paginator->sort('ActionLogs.date', __d('admin', 'Date')) . '</th>';
echo '<th>' . $this->Paginator->sort('ActionLogs.text', __d('admin', 'Text')) . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'User')) . '</th>';
echo '<th></th>';
echo '</tr>';

$i = 0;
foreach ($actionLogs as $actionLog) {

    $i ++;
    $name = null;

    $actionType = $actionLogsTable->types[$actionLog->type];
    $actionClass = empty($actionType['class']) ? [] : $actionType['class'];
    $actionClass = array_merge(['data'], $actionClass);

    echo '<tr class="' . implode(' ', $actionClass) . '">';

    echo '<td class="hide">';
    echo $actionLog->id;
    echo '</td>';

    echo '<td>';
    echo $this->Html->link(
        $actionType['name'],
        '/admin/action-logs/index/?types[]='.$actionLog->type.'&productId='.$productId.'&customerId='.$customerId.'&dateFrom='.$dateFrom.'&dateTo='.$dateTo.(!empty($this->request->getQuery('sort')) ? '&sort='.$this->request->getQuery('sort') : '').(!empty($this->request->getQuery('direction')) ? '&direction='.$this->request->getQuery('direction') : '')
    );
    echo '</td>';

    echo '<td>';
    echo $actionLog->date->i18nFormat($this->Time->getI18Format('DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td class="text">';
    echo $actionLog->text;
    echo '</td>';

    echo '<td>';
    if ($actionLog->customer) {
        $name = $actionLog->customer->name;
        if ($identity->isManufacturer() && $identity->getManufacturerAnonymizeCustomers()) {
            $name = $this->Html->anonymizeCustomerName($name, $actionLog->customer->id_customer);
        }
        if ($actionLog->customer->manufacturer) {
            $name = $actionLog->customer->manufacturer->name;
        }
    }
    if (isset($name)) {
        echo $this->Html->link(
            $name,
            '/admin/action-logs/index/?types[]='.join('&types[]=', $types).'&productId='.$productId.'&customerId='.($actionLog->customer ? $actionLog->customer->id_customer : '').'&dateFrom='.$dateFrom.'&dateTo='.$dateTo.(!empty($this->request->getQuery('sort')) ? '&sort='.$this->request->getQuery('sort') : '').(!empty($this->request->getQuery('direction')) ? '&direction='.$this->request->getQuery('direction') : ''),
            [
                'escape' => false
            ]
        );
    }
    echo '</td>';

    echo '<td class="center">';

    $showLink = false;

    // products
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'products' && ! ($actionLog->type == 'product_set_inactive')) {
        $showLink = true;
        $title = __d('admin', 'Show_product');
        $url = $this->Slug->getProductDetail($actionLog->object_id, '');
    }

    // manufacturers
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'manufacturers') {
        $showLink = true;
        $title = __d('admin', 'Show_manufacturer');
        $url = $this->Slug->getManufacturerDetail($actionLog->object_id, '');
    }

    // blog_posts
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'blog_posts' && ! (in_array($actionLog->type, [
        'blog_post_deleted'
    ]))) {
        $showLink = true;
        $title = __d('admin', 'Show_blog_post');
        $url = $this->Slug->getBlogPostDetail($actionLog->object_id, '');
    }

    // pages
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'pages' && ! (in_array($actionLog->type, [
        'page_deleted'
    ]))) {
        $showLink = true;
        $title = __d('admin', 'Show_page');
        $url = $this->Slug->getPageDetail($actionLog->object_id, '');
    }

    // categories
    if ($actionLog->object_id > 0 && $actionLog->object_type == 'categories' && ! (in_array($actionLog->type, [
        'category_deleted'
    ]))) {
        $showLink = true;
        $title = __d('admin', 'Show_category');
        $url = $this->Slug->getCategoryDetail($actionLog->object_id, '');
    }

    if ($showLink) {
        echo $this->Html->link(
            '<i class="fas fa-arrow-right ok"></i>',
            $url,
            [
                'class' => 'btn btn-outline-light',
                'title' => $title,
                'target' => '_blank',
                'escape' => false
            ]
        );
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="10"><b>' . $this->Number->formatAsDecimal($i, 0) . '</b> '.__d('admin', 'records').'</td>';
echo '</tr>';

echo '</table>';

?>
</div>
