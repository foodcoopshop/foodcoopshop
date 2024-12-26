<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\View\JsonView;
use Cake\ORM\TableRegistry;

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
class CronController extends AppController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }
    
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'index',
        ]);
    }

    public function index()
    {

        $this->request = $this->request->withParam('_ext', 'json');
        $cronjobsTable = TableRegistry::getTableLocator()->get('Cronjobs');

        $executedCronjobs = $cronjobsTable->run();
        $this->set([
            'executedCronjobs' => $executedCronjobs,
        ]);
        $this->viewBuilder()->setOption('serialize', ['executedCronjobs']);

    }

}
