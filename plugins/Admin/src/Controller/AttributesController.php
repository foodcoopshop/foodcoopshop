<?php
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * AttributesController
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

class AttributesController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
    }

    public function add()
    {
        $this->Attribute = TableRegistry::getTableLocator()->get('Attributes');
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

        $this->Attribute = TableRegistry::getTableLocator()->get('Attributes');
        $attribute = $this->Attribute->find('all', [
            'conditions' => [
                'Attributes.id_attribute' => $attributeId
            ]
        ])->first();

        $this->ProductAttributeCombination = TableRegistry::getTableLocator()->get('ProductAttributeCombinations');
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
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData())));

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

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('Attributes.delete_attribute'))) {
                $this->Attribute->delete($attribute);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'attribute_deleted';
            }
            $message = __d('admin', 'The_attribute_{0}_has_been_{1}.', ['<b>' . $attribute->name . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $attribute->id_attribute, 'attributes', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $attribute->id_attribute);
            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('attribute', $attribute);
    }

    public function index()
    {
        $conditions = [
            'Attributes.active > ' . APP_DEL
        ];

        $this->Attribute = TableRegistry::getTableLocator()->get('Attributes');
        $query = $this->Attribute->find('all', [
            'conditions' => $conditions
        ]);
        $attributes = $this->paginate($query, [
            'sortWhitelist' => [
                'Attributes.name', 'Attributes.modified', 'Attributes.can_be_used_as_unit'
            ],
            'order' => [
                'Attributes.name' => 'ASC'
            ]
        ])->toArray();

        $this->ProductAttributeCombination = TableRegistry::getTableLocator()->get('ProductAttributeCombinations');
        foreach ($attributes as $attribute) {
            $attribute->combination_product = $this->ProductAttributeCombination->getCombinationCounts($attribute->id_attribute);
        }
        $this->set('attributes', $attributes);

        $this->set('title_for_layout', __d('admin', 'Attributes'));
    }
}
