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
<div id="taxes">

        <?php
        $this->element('addScript', [ 'script' =>
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__('Website_administration')."', '".__('Configurations')."');
            "
        ]);
        $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#tax-'
        ]);
    ?>

    <div class="filter-container">
        <h1><?php echo $title_for_layout; ?></h1>
        <div class="right">
            <?php
            echo '<div id="add-tax-button-wrapper" class="add-button-wrapper">';
            echo $this->Html->link('<i class="fas fa-plus-circle ok"></i>', $this->Slug->getTaxAdd(), [
                'class' => 'btn btn-outline-light',
                'title' => __('Add_tax_rate'),
                'escape' => false
            ]);
            echo '</div>';
            echo $this->element('printIcon');
            ?>
        </div>

    </div>

<?php
echo $this->element('navTabs/configurationNavTabs', [
    'key' => 'tax_rates',
]);

$this->Paginator->setPaginated($taxes);
echo '<table class="list">';
echo '<tr class="sort">';
echo '<th class="hide">'.__('ID').'</th>';
echo '<th></th>';
echo '<th class="stretch">' . $this->Paginator->sort('Taxes.rate', __('Tax_rate_admin')) . '</th>';
echo '<th class="right">' . $this->Paginator->sort('Taxes.product_count', __('Products')) . '</th>';
echo '<th>' . $this->Paginator->sort('Taxes.active', __('Active')) . '</th>';
echo '</tr>';

$i = 0;

if (Configure::read('app.isZeroTaxEnabled')) {
    $i ++;
    echo '<tr id="tax-0" class="data">';

    echo '<td class="hide">';
        echo 0;
    echo '</td>';

    echo '<td></td>';

    echo '<td>';
        echo $this->Number->formatTaxRate(0) . '%';
    echo '</td>';

    echo '<td class="right">';
        echo $this->Number->formatAsDecimal($zeroTaxProductCount, 0);
    echo '</td>';

    echo '<td align="center">';
        echo '<i class="fas fa-check-circle ok"></i>';
    echo '</td>';

    echo '</tr>';
}

foreach ($taxes as $tax) {
    $i ++;
    $rowClass = [
        'data'
    ];
    if (! $tax->active) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr id="tax-' . $tax->id_tax . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td class="hide">';
    echo $tax->id_tax;
    echo '</td>';

    echo '<td>';
    echo $this->Html->link(
        '<i class="fas fa-pencil-alt ok"></i>',
        $this->Slug->getTaxEdit($tax->id_tax),
        [
            'class' => 'btn btn-outline-light',
            'title' => __('Edit'),
            'escape' => false
        ]
    );
    echo '</td>';

    echo '<td>';
        echo $this->Number->formatTaxRate($tax->rate) . '%';
    echo '</td>';

    echo '<td class="right">';
        echo $this->Number->formatAsDecimal($tax->product_count, 0);
    echo '</td>';

    echo '<td align="center">';
    if ($tax->active == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    } else {
        echo '<i class="fas fa-minus-circle not-ok"></i>';
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="5"><b>' . $i . '</b> '.__('{0,plural,=1{record} other{records}}', $i).'</td>';
echo '</tr>';

echo '</table>';

?>
</div>
