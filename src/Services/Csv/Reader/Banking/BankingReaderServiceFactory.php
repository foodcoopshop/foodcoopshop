<?php

declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Services\Csv\Reader\Banking;

class BankingReaderServiceFactory {

	const MAP = [
		'Raiffeisen'      => RaiffeisenBankingReaderService::class,
		'GlsBank'         => GlsBankBankingReaderService::class,
		'Sparkasse'       => SparkasseBankingReaderService::class,
		'Volksbank'       => VolksbankBankingReaderService::class,
		'BankBurgenland'  => BankBurgenlandBankingReaderService::class,
	];

	public static function get($alias) {
		return self::MAP[$alias];
	}

}
?>