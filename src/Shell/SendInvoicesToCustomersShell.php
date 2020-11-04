<?php
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
namespace App\Shell;

use Cake\Core\Configure;

class SendInvoicesToCustomersShell extends AppShell
{

    public $cronjobRunDay;

    public function main()
    {
        parent::main();

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Customer = $this->getTableLocator()->get('Customers');

        // $this->cronjobRunDay can is set in unit test
        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfLastMonth($this->cronjobRunDay);
        $dateTo = Configure::read('app.timeHelper')->getLastDayOfLastMonth($this->cronjobRunDay);

    }


}
