<?php

namespace App\Shell;

use App\Lib\SimpleBrowser\AppSimpleBrowser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * AppShell
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppShell extends Shell
{

    public $timeStart;

    public $timeEnd;

    public $browser;

    public function main()
    {
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        error_reporting(0); // disable all error messages
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
        return __('Runtime') . ': ' . Configure::read('app.numberHelper')->formatAsDecimal($time) . ' ' . __('seconds');
    }

    public function initSimpleBrowser()
    {
        $this->browser = new AppSimpleBrowser();
        $this->browser->loginEmail = Configure::read('app.adminEmail');
        $this->browser->loginPassword = Configure::read('app.adminPassword');

        if ($this->isCalledFromUnitTest()) {
            $this->browser->addHeader('x-unit-test-mode: true');
            $this->browser->loginEmail = Configure::read('test.loginEmailSuperadmin');
            $this->browser->loginPassword = Configure::read('test.loginPassword');
        }
    }

    public function out($message = null, $newlines = 1, $level = Shell::NORMAL)
    {
        if ($this->isCalledFromUnitTest()) {
            return;
        } else {
            return parent::out($message, $newlines, $level);
        }
    }

    private function isCalledFromUnitTest()
    {
        return isset($_SERVER['HTTP_X_UNIT_TEST_MODE'])
            || (php_sapi_name() == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]));
    }
}
