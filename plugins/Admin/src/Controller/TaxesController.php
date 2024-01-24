<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Model\Table\TaxesTable;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use App\Services\SanitizeService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TaxesController extends AdminAppController
{

    protected TaxesTable $Tax;
    
    public function add()
    {
        $this->Tax = $this->getTableLocator()->get('Taxes');
        $tax = $this->Tax->newEntity(
            [
                'rate' => 0,
                'active' => APP_ON,
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('admin', 'Add_tax_rate'));
        $this->_processForm($tax, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($taxId)
    {
        if ($taxId === null) {
            throw new NotFoundException;
        }

        $this->Tax = $this->getTableLocator()->get('Taxes');
        $tax = $this->Tax->find('all', conditions: [
            'Taxes.id_tax' => $taxId
        ])->first();

        if (empty($tax)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_tax_rate'));
        $this->_processForm($tax, true);
    }

    private function _processForm($tax, $isEditMode)
    {

        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('tax', $tax);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $tax = $this->Tax->patchEntity($tax, $this->getRequest()->getData());
        if ($tax->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('tax', $tax);
            $this->render('edit');
        } else {
            $tax = $this->Tax->save($tax);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'tax_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'tax_changed';
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $message = __d('admin', 'The_tax_rate_{0}_has_been_{1}.', ['<b>' . Configure::read('app.numberHelper')->formatAsPercent($tax->rate) . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->identity->getId(), $tax->id_tax, 'taxes', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $tax->id_tax);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('tax', $tax);
    }

    public function index()
    {
        $conditions = [
            'Taxes.active > ' . APP_DEL
        ];

        $this->Tax = $this->getTableLocator()->get('Taxes');
        $query = $this->Tax->find('all', conditions: $conditions);
        $taxes = $this->paginate($query, [
            'sortableFields' => [
                'Taxes.rate', 'Taxes.position'
            ],
            'order' => [
                'Taxes.rate' => 'ASC'
            ]
        ]);

        $this->set('taxes', $taxes);

        $this->set('title_for_layout', __d('admin', 'Tax_rates'));
    }
}
