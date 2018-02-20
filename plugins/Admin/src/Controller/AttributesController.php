<?php
namespace Admin\Controller;
use Cake\Network\Exception\NotFoundException;
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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
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
        $this->Attribute = TableRegistry::get('Attributes');
        $attribute = $this->Attribute->newEntity(
            ['active' => APP_ON],
            ['validate' => false]
        );
        $this->set('title_for_layout', 'Variante erstellen');
        $this->_processForm($attribute, false);
        
        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }
    
    public function edit($attributeId)
    {
        if ($attributeId === null) {
            throw new NotFoundException;
        }
        
        $this->Attribute = TableRegistry::get('Attributes');
        $attribute = $this->Attribute->find('all', [
            'conditions' => [
                'Attributes.id_attribute' => $attributeId
            ]
        ])->first();
        
        $this->ProductAttributeCombination = TableRegistry::get('ProductAttributeCombinations');
        $combinationCounts = $this->ProductAttributeCombination->getCombinationCounts($attributeId);
        $attribute->has_combined_products = count($combinationCounts['online']) + count($combinationCounts['offline']) > 0;
        
        if (empty($attribute)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', 'Variante bearbeiten');
        $this->_processForm($attribute, true);
    }

    private function _processForm($attribute, $isEditMode)
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->request->getData())) {
            $this->set('attribute', $attribute);
            return;
        }
            
        $this->loadComponent('Sanitize');
        $this->request->data = $this->Sanitize->trimRecursive($this->request->data);
        $this->request->data = $this->Sanitize->stripTagsRecursive($this->request->data);
        
        $attribute = $this->Attribute->patchEntity($attribute, $this->request->getData());
        if (!empty($attribute->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            $this->set('attribute', $attribute);
            $this->render('edit');
        } else {
            $attribute = $this->Attribute->save($attribute);
            
            if (!$isEditMode) {
                $messageSuffix = 'erstellt';
                $actionLogType = 'attribute_added';
            } else {
                $messageSuffix = 'geändert';
                $actionLogType = 'attribute_changed';
            }
            
            $this->ActionLog = TableRegistry::get('ActionLogs');
            if (!empty($this->request->getData('Attributes.delete_attribute'))) {
                $this->Attribute->delete($attribute);
                $messageSuffix = 'gelöscht';
                $actionLogType = 'attribute_deleted';
            }
            $message = 'Die Variante <b>' . $attribute->name . '</b> wurde ' . $messageSuffix . '.';
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $attribute->id_attribute, 'attributes', $message);
            $this->Flash->success($message);
            
            $this->request->getSession()->write('highlightedRowId', $attribute->id_attribute);
            $this->redirect($this->request->getData('referer'));
            
        }
        
        $this->set('attribute', $attribute);
        
    }

    public function index()
    {
        $conditions = [
            'Attributes.active > ' . APP_DEL
        ];

        $this->Attribute = TableRegistry::get('Attributes');
        $query = $this->Attribute->find('all', [
            'conditions' => $conditions
        ]);
        $attributes = $this->paginate($query, [
            'sortWhitelist' => [
                'Attributes.name', 'Attributes.modified'
            ],
            'order' => [
                'Attributes.name' => 'ASC'
            ]
        ])->toArray();

        $this->ProductAttributeCombination = TableRegistry::get('ProductAttributeCombinations');
        foreach ($attributes as $attribute) {
            $attribute->combination_product = $this->ProductAttributeCombination->getCombinationCounts($attribute->id_attribute);
        }
        $this->set('attributes', $attributes);

        $this->set('title_for_layout', 'Varianten');
    }
}
