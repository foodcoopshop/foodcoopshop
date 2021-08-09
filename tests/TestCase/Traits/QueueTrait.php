<?php

namespace App\Test\TestCase\Traits;

use Cake\Datasource\FactoryLocator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait QueueTrait
{

    protected $QueuedJobs;

    protected function runAndAssertQueue()
    {
        $this->commandRunner->run(['cake', 'queue', 'run', '-q']);
        $this->QueuedJobs = FactoryLocator::get('Table')->get('Queue.QueuedJobs');
        $queuedJobs = $this->QueuedJobs->find('all');
        foreach($queuedJobs as $queuedJob) {
            if ($queuedJob->failed) {
                pr($queuedJob->failure_message);
            }
            $this->assertEquals(0, $queuedJob->failed);
        }
    }

}
