<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\Datasource\FactoryLocator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait QueueTrait
{

    protected $QueuedJobs;

    protected function runAndAssertQueue()
    {
        $this->exec('queue run -q');
        $this->QueuedJobs = FactoryLocator::get('Table')->get('Queue.QueuedJobs');
        $queuedJobs = $this->QueuedJobs->find('all');
        foreach($queuedJobs as $queuedJob) {
            if (!empty($queuedJob->failure_message)) {
                pr($queuedJob->failure_message);
            }
            $this->assertEmpty($queuedJob->failurue_message);
        }
    }

}
