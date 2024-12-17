<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Cronjob extends Entity
{

    const BACKUP_DATABASE_CRONJOB_ID = 1;
    const CHECK_CREDIT_BALANCE_CRONJOB_ID = 2;
    const EMAIL_ORDER_REMINDER_CRONJOB_ID = 3;
    const PICKUP_REMINDER_CRONJOB_ID = 4;
    const SEND_INVOICES_TO_MANUFACTURERS_CRONJOB_ID = 5;
    const SEND_ORDER_LISTS_CRONJOB_ID = 6;
    const SEND_INVOICES_TO_CUSTOMERS_CRONJOB_ID = 7;
    const SEND_DELIVERY_NOTES_CRONJOB_ID = 8;

    protected function _getName($name)
    {
        return match($name) {
            'BackupDatabase' => __('BackupDatabaseCronjob'),
            'CheckCreditBalance' => __('CheckCreditBalanceCronjob'),
            'EmailOrderReminder' => __('EmailOrderReminderCronjob'),
            'PickupReminder' => __('PickupReminderCronjob'),
            'SendOrderLists' => __('SendOrderListsCronjob'),
            'SendInvoicesToCustomers' =>  __('SendInvoicesToCustomersCronjob'),
            'SendInvoicesToManufacturers' => __('SendInvoicesToManufacturersCronjob'),
            'SendDeliveryNotes' => __('SendDeliveryNotesCronjob'),
             default => $name,
        };
    }

}
