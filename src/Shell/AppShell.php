<?php

namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use App\Network\AppHttpClient;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppShell extends Shell
{

    public $timeStart;

    public $timeEnd;

    public $httpClient;

    public function main()
    {
        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
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

    public function initHttpClient()
    {
        if ($this->isCalledFromUnitTest()) {
            $this->httpClient = new AppHttpClient([
                'headers' => [
                    'x-unit-test-mode' => true
                ]
            ]);
            $this->httpClient->loginEmail = Configure::read('test.loginEmailSuperadmin');
            $this->httpClient->loginPassword = Configure::read('test.loginPassword');
        } else {
            $this->httpClient = new AppHttpClient();
            $this->httpClient->loginEmail = Configure::read('app.adminEmail');
            $this->httpClient->loginPassword = Configure::read('app.adminPassword');
        }
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
        return isset($_SERVER['HTTP_X_UNIT_TEST_MODE'])
            || (php_sapi_name() == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]));
    }
}
