<?php
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

if (!is_array($lines)
    || empty($lines)
    ) {
        exit(PHP_EOL . 'Cannot load config' . DS . 'app_config.php.' . PHP_EOL);
    }

    foreach ($lines as $line) {
        if (($pos = strpos($line, '\'mysqlDumpCommand\'')) !== false) {
            $line = substr($line, $pos + strlen('\'mysqlDumpCommand\','));
            $line = explode('\'', $line);
            if (count($line) == 3) {
                $mysqldump_cmd = $line[1];
            } else {
                $mysqldump_cmd = $line[0];
            }
        }
    }

    if (empty($mysqldump_cmd)) {
        exit(PHP_EOL . 'Cannot read mysqlDumpCommand from Config' . DS . 'app_config.php.' . PHP_EOL);
    }

    if (strpos($mysqldump_cmd, 'mysqldump') === false) {
        exit(PHP_EOL . 'Cannot use mysqlDumpCommand from Config' . DS . 'app_config.php. Must use mysqldump' . PHP_EOL);
    }
?>