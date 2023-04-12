<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".Admin.initCopyPersonalTransactionCodeToClipboardButton('" . __d('admin', 'Your_code_has_been_copied_successfully_into_the_clipboard.') . "');"
]);
?>

<h2 class="info personal-transaction-code"><?php echo __d('admin', 'Please_add_this_code_to_your_transaction:_{0}', ['<b>' . $personalTransactionCode . '</b>']); ?>
<?php
    echo $this->Html->link(
        '<i class="far fa-fw fa-clone"></i>',
        'javascript:void(0)',
        [
            'data-clipboard-text' => $personalTransactionCode,
            'class' => 'btn btn-outline-light btn-clipboard',
            'title' => __d('admin', 'Copy_to_clipboard'),
            'style' => 'float:right;margin-top:-5px;margin-right:-3px;',
            'escape' => false
        ]
    );
?></h2>
