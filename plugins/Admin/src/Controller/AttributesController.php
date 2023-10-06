<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;

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

class AttributesController extends AdminAppController
{

    protected $Attribute;
    protected $ProductAttributeCombination;
    protected $Sanitize;

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
    }

    public function add()
    {
        $this->Attribute = $this->getTableLocator()->get('Attributes');
        $attribute = $this->Attribute->newEntity(
            ['active' => APP_ON],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('admin', 'Add_attribute'));
        $this->_processForm($attribute, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($attributeId)
    {
        if ($attributeId === null) {
            throw new NotFoundException;
        }

        $this->Attribute = $this->getTableLocator()->get('Attributes');
        $attribute = $this->Attribute->find('all', [
            'conditions' => [
                'Attributes.id_attribute' => $attributeId
            ]
        ])->first();

        $this->ProductAttributeCombination = $this->getTableLocator()->get('ProductAttributeCombinations');
        $combinationCounts = $this->ProductAttributeCombination->getCombinationCounts($attributeId);
        $attribute->has_combined_products = count($combinationCounts['online']) + count($combinationCounts['offline']) > 0;

        if (empty($attribute)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_attribute'));
        $this->_processForm($attribute, true);
    }

    private function _processForm($attribute, $isEditMode)
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('attribute', $attribute);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $attribute = $this->Attribute->patchEntity($attribute, $this->getRequest()->getData());
        if ($attribute->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('attribute', $attribute);
            $this->render('edit');
        } else {
            $attribute = $this->Attribute->save($attribute);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'attribute_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'attribute_changed';
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('Attributes.delete_attribute'))) {
                $this->Attribute->delete($attribute);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'attribute_deleted';
            }
            $message = __d('admin', 'The_attribute_{0}_has_been_{1}.', ['<b>' . $attribute->name . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $attribute->id_attribute, 'attributes', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $attribute->id_attribute);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('attribute', $attribute);
    }

    public function index()
    {
        $conditions = [
            'Attributes.active > ' . APP_DEL
        ];

        $this->Attribute = $this->getTableLocator()->get('Attributes');
        $query = $this->Attribute->find('all', [
            'conditions' => $conditions
        ]);
        $attributes = $this->paginate($query, [
            'sortableFields' => [
                'Attributes.name', 'Attributes.modified', 'Attributes.can_be_used_as_unit'
            ],
            'order' => [
                'Attributes.name' => 'ASC'
            ]
        ])->toArray();

        $this->ProductAttributeCombination = $this->getTableLocator()->get('ProductAttributeCombinations');
        foreach ($attributes as $attribute) {
            $attribute->combination_product = $this->ProductAttributeCombination->getCombinationCounts($attribute->id_attribute);
        }
        $this->set('attributes', $attributes);

        $this->set('title_for_layout', __d('admin', 'Attributes'));
    }
}
