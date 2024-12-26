<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\I18n\DateTime;

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

    public function deleteOldLogs($timestamp)
    {

        if (is_object($timestamp) && get_class($timestamp)  == DateTime::class) {
            $timestamp = $timestamp->getTimestamp();
        }
        $timestamp = (int) $timestamp;
        if ($timestamp <= 0) {
            throw new \Exception('invalid timestamp: ' . $timestamp);
        }

        $diffInDays = 60;
        $this->deleteAll([
            'DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(' . $timestamp . '), \'%Y-%m-%d\'), created) > ' . $diffInDays,
        ]);

    }

}
