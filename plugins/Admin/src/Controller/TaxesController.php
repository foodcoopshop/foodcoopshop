<?php

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * TaxesController
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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TaxesController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin();
    }

    public function add()
    {
        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
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

        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
        $tax = $this->Tax->find('all', [
            'conditions' => [
                'Taxes.id_tax' => $taxId
            ]
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

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData())));

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

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $message = __d('admin', 'The_tax_rate_{0}_has_been_{1}.', ['<b>' . Configure::read('app.numberHelper')->formatAsPercent($tax->rate) . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $tax->id_tax, 'taxes', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $tax->id_tax);
            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('tax', $tax);
    }

    public function index()
    {
        $conditions = [
            'Taxes.active > ' . APP_DEL
        ];

        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
        $query = $this->Tax->find('all', [
            'conditions' => $conditions
        ]);
        $taxes = $this->paginate($query, [
            'sortWhitelist' => [
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
