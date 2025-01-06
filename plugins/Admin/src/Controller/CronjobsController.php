<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Query;
use App\Services\SanitizeService;

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

    public function index(): void
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjobs = $cronjobsTable->find('available');

        $cronjobs->contain([
            'CronjobLogs' => function (Query $q) {
                $q->orderByDesc('CronjobLogs.created');
                return $q;
            }
        ]);

        $this->set('cronjobs', $cronjobs);
        $this->set('title_for_layout', __d('admin', 'Cronjobs'));
    }

    public function edit($cronjobId): void
    {
        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
        $cronjob = $cronjobsTable->find('available', conditions: [
            'Cronjobs.id' => $cronjobId,
        ])->first();

        if (empty($cronjob)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_cronjob'));

        $this->setFormReferer();
        $this->set('timeIntervals', $cronjobsTable->getTimeIntervals());
        $this->set('daysOfMonth', $cronjobsTable->getDaysOfMonth());
        $this->set('weekdays', $cronjobsTable->getWeekdays());

        if (empty($this->getRequest()->getData())) {
            $this->set('cronjob', $cronjob);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $validationName = $cronjob->getOriginalValues()['name'];
        if (!method_exists($cronjobsTable, 'validation'.$validationName)) {
            $validationName = 'default';
        }

        $cronjob = $cronjobsTable->patchEntity(
            $cronjob,
            $this->getRequest()->getData(),
            [
                'validate' => $validationName,
            ],
        );
        if ($cronjob->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('cronjob', $cronjob);
        } else {
            $cronjob = $cronjobsTable->save($cronjob);
            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $message = __d('admin', 'The_cronjob_{0}_has_been_changed.', ['<b>' . $cronjob->name . '</b>']);
            $actionLogsTable->customSave('cronjob_changed', $this->identity->getId(), $cronjob->id, 'cronjobs', $message);
            $this->Flash->success($message);

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('cronjob', $cronjob);

    }

}
