<?php

use Admin\Controller\AdminAppController;
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
        $this->edit();
        $this->set('title_for_layout', 'Variante erstellen');
        $this->render('edit');
    }

    public function edit($attributeId = null)
    {
        $this->setFormReferer();

        if ($attributeId > 0) {
            $unsavedAttribute = $this->Attribute->find('all', [
                'conditions' => [
                    'Attributes.id_attribute' => $attributeId
                ]
            ])->first();
            $this->ProductAttributeCombination = TableRegistry::get('ProductAttributeCombinations');
            $unsavedAttribute['CombinationProducts'] = $this->ProductAttributeCombination->getCombinationCounts($attributeId);
        } else {
            $unsavedAttribute = [];
        }

        $this->set('unsavedAttribute', $unsavedAttribute);
        $this->set('title_for_layout', 'Variante bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedAttribute;
        } else {
            // validate data - do not use $this->Attribute->saveAll()
            $this->Attribute->id = $attributeId;
            $this->Attribute->set($this->request->data['Attributes']);

            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['Attributes'] as &$data) {
                $data = strip_tags(trim($data));
            }

            $errors = [];
            $this->Attribute->set($this->request->data['Attributes']);
            if (! $this->Attribute->validates()) {
                $errors = array_merge($errors, $this->Attribute->validationErrors);
            }

            if (empty($errors)) {
                $this->ActionLog = TableRegistry::get('ActionLogs');

                $this->Attribute->save($this->request->data['Attributes'], [
                    'validate' => false
                ]);
                if (is_null($attributeId)) {
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'attribute_added';
                } else {
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'attribute_changed';
                }

                if (isset($this->request->data['Attributes']['delete_attribute']) && $this->request->data['Attributes']['delete_attribute']) {
                    $this->Attribute->delete($this->Attribute->id); // cascade does not work here
                    $message = 'Die Variante "' . $this->request->data['Attributes']['name'] . '" wurde erfolgreich gelöscht.';
                    $this->ActionLog->customSave('attribute_deleted', $this->AppAuth->getUserId(), $this->Attribute->id, 'attributes', $message);
                    $this->Flash->success('Die Variante wurde erfolgreich gelöscht.');
                } else {
                    $message = 'Die Variante "' . $this->request->data['Attributes']['name'] . '" wurde ' . $messageSuffix;
                    $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Attribute->id, 'attributes', $message);
                    $this->Flash->success('Die Variante wurde erfolgreich gespeichert.');
                }

                $this->request->session()->write('highlightedRowId', $this->Attribute->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }

    public function index()
    {
        $conditions = [];
        $conditions[] = 'Attributes.active > ' . APP_DEL;

        $this->Paginator->settings = array_merge([
            'conditions' => $conditions,
            'order' => [
                'Attributes.name' => 'ASC'
            ]
        ], $this->Paginator->settings);
        $attributes = $this->Paginator->paginate('Attributes');

        $this->ProductAttributeCombination = TableRegistry::get('ProductAttributeCombinations');
        foreach ($attributes as &$attribute) {
            $attribute['CombinationProducts'] = $this->ProductAttributeCombination->getCombinationCounts($attribute['Attributes']['id_attribute']);
        }

        $this->set('attributes', $attributes);

        $this->set('title_for_layout', 'Varianten');
    }
}
