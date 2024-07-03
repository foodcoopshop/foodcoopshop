<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\Fixture;

class PaymentsFixture extends AppFixture
{
    public string $table = 'fcs_payments';

    public array $records = [
        [
            'id_payment' => 1,
            'id_customer' => 92,
            'id_manufacturer' => 0,
            'type' => 'product',
            'amount' => 100.00,
            'text' => '',
            'date_add' => '2018-07-03 20:00:20',
            'date_changed' => '2018-07-03 20:00:20',
            'date_transaction_add' => null,
            'transaction_text' => null,
            'invoice_id' => 0,
            'status' => 1,
            'approval' => 0,
            'approval_comment' => '',
            'changed_by' => 0,
            'created_by' => 92,
        ],
        [
            'id_payment' => 2,
            'id_customer' => 87,
            'id_manufacturer' => 0,
            'type' => 'product',
            'amount' => 100000.00,
            'text' => '',
            'date_add' => '2020-12-09 20:00:20',
            'date_changed' => '2020-12-09 20:00:20',
            'date_transaction_add' => null,
            'transaction_text' => null,
            'invoice_id' => 0,
            'status' => 1,
            'approval' => 0,
            'approval_comment' => '',
            'changed_by' => 0,
            'created_by' => 87,
        ],
    ];
}
?>