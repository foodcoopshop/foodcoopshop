<?php
namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Query;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class CronjobsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin();
    }

    public function index()
    {
        $this->Cronjobs = $this->getTableLocator()->get('Cronjobs');
        $cronjobs = $this->Cronjobs->find('available');

        $cronjobs->contain([
            'CronjobLogs' => function (Query $q) {
                $q->orderDesc('CronjobLogs.created');
                return $q;
            }
        ]);

        $this->set('cronjobs', $cronjobs);
        $this->set('title_for_layout', __d('admin', 'Cronjobs'));
    }

    public function edit($cronjobId)
    {
        $this->Cronjob = $this->getTableLocator()->get('Cronjobs');
        $cronjob = $this->Cronjob->find('available', [
            'conditions' => [
                'Cronjobs.id' => $cronjobId,
            ]
        ])->first();

        if (empty($cronjob)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_cronjob'));

        $this->setFormReferer();
        $this->set('timeIntervals', $this->Cronjob->getTimeIntervals());
        $this->set('daysOfMonth', $this->Cronjob->getDaysOfMonth());
        $this->set('weekdays', $this->Cronjob->getWeekdays());

        if (empty($this->getRequest()->getData())) {
            $this->set('cronjob', $cronjob);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $cronjob = $this->Cronjob->patchEntity($cronjob, $this->getRequest()->getData());
        if ($cronjob->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('cronjob', $cronjob);
        } else {
            $cronjob = $this->Cronjob->save($cronjob);
            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $message = __d('admin', 'The_cronjob_{0}_has_been_changed.', ['<b>' . $cronjob->id_cronjob . '</b>']);
            $this->ActionLog->customSave('cronjob_changed', $this->AppAuth->getUserId(), $cronjob->id, 'cronjobs', $message);
            $this->Flash->success($message);

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('cronjob', $cronjob);

    }

}
