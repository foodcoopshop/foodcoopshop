<?php

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CronController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow('index');
    }

    public function index()
    {

        $this->RequestHandler->renderAs($this, 'json');

        $this->Cronjob = $this->getTableLocator()->get('Cronjobs');

        $executedCronjobs = $this->Cronjob->run();
        $this->set([
            'executedCronjobs' => $executedCronjobs,
        ]);
        $this->viewBuilder()->setOption('serialize', ['executedCronjobs']);

    }

}
