<?php
declare(strict_types=1);

namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;

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
class AppShell extends Shell
{

    public $timeStart;

    public $timeEnd;

    public function main()
    {
        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
    }

    public function startTimeLogging()
    {
        $this->timeStart = microtime(true);
    }

    public function stopTimeLogging()
    {
        $this->timeEnd = microtime(true);
    }

    public function getRuntime()
    {
        $time = $this->timeEnd - $this->timeStart;
        return __('Runtime') . ': ' . Configure::read('app.timeHelper')->convertSecondsInMinutesAndSeconds($time);
    }

    public function out($message, int $newlines = 1, int $level = Shell::NORMAL): ?int
    {
        if ($this->isCalledFromUnitTest()) {
            return null;
        } else {
            return parent::out($message, $newlines, $level);
        }
    }

    private function isCalledFromUnitTest()
    {
        return php_sapi_name() == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]);
    }
}
