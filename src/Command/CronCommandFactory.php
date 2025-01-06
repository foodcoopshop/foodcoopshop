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

namespace App\Command;

class CronCommandFactory {

	const MAP = [
		'BackupDatabase'              => BackupDatabaseCommand::class,
		'CheckCreditBalance'          => CheckCreditBalanceCommand::class,
		'EmailOrderReminder'          => EmailOrderReminderCommand::class,
		'PickupReminder'              => PickupReminderCommand::class,
		'SendDeliveryNotes'           => SendDeliveryNotesCommand::class,
		'SendInvoicesToCustomers'     => SendInvoicesToCustomersCommand::class,
		'SendInvoicesToManufacturers' => SendInvoicesToManufacturersCommand::class,
		'SendOrderLists'              => SendOrderListsCommand::class,
		'TestCronjob'                 => TestCronjobCommand::class,
		'TestCronjobWithException'    => TestCronjobWithExceptionCommand::class,
	];

	public static function get($alias): string
	{
		return self::MAP[$alias];
	}

}
?>