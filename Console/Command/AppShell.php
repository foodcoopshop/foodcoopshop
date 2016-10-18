<?php

App::uses('Shell', 'Console');
App::uses('AppSimpleBrowser', 'Lib/SimpleBrowser');

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppShell extends Shell
{

    public $timeStart;

    public $timeEnd;

    public $browser;

    public $uses = array(
        'CakeActionLog',
        'Customer',
        'Configuration'
    );

    public function loadConfigurations()
    {
        $this->Configuration->loadConfigurations();
    }

    public function main()
    {
        $this->loadConfigurations();
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
        return 'Laufzeit: ' . number_format($time, 2, ',', '.') . ' Sekunden';
    }

    public function initSimpleBrowser()
    {
        $this->browser = new AppSimpleBrowser();
        $this->browser->loginEmail = Configure::read('app.adminEmail');
        $this->browser->loginPassword = Configure::read('app.adminPassword');
    }
}
