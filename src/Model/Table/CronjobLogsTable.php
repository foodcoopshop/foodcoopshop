<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Lib\Error\Exception\InvalidParameterException;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CronjobLogsTable extends AppTable
{

    public const RUNNING = 2;
    public const SUCCESS = 1;
    public const FAILURE = 0;

    public function deleteOldLogs($timestamp)
    {

        $timestamp = (int) $timestamp;
        if ($timestamp <= 0) {
            throw new InvalidParameterException('invalid timestamp: ' . $timestamp);
        }

        $diffInDays = 60;
        $this->deleteAll([
            'DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(' . $timestamp . '), \'%Y-%m-%d\'), created) > ' . $diffInDays,
        ]);

    }

}
