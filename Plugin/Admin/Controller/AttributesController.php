<?php
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
            $unsavedAttribute = $this->Attribute->find('first', array(
                'conditions' => array(
                    'Attribute.id_attribute' => $attributeId
                )
            ));
            $this->loadModel('ProductAttributeCombination');
            $unsavedAttribute['CombinationProducts'] = $this->ProductAttributeCombination->getCombinationCounts($attributeId);
        } else {
            $unsavedAttribute = array();
        }

        $this->set('unsavedAttribute', $unsavedAttribute);
        $this->set('title_for_layout', 'Variante bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedAttribute;
        } else {
            // validate data - do not use $this->Attribute->saveAll()
            $this->Attribute->id = $attributeId;
            $this->Attribute->set($this->request->data['Attribute']);

            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['Attribute'] as &$data) {
                $data = strip_tags(trim($data));
            }
            foreach ($this->request->data['AttributeLang'] as $key => &$data) {
                $data = strip_tags(trim($data));
            }

            $errors = array();
            $this->Attribute->AttributeLang->set($this->request->data['AttributeLang']);
            if (! $this->Attribute->AttributeLang->validates()) {
                $errors = array_merge($errors, $this->Attribute->AttributeLang->validationErrors);
            }

            if (empty($errors)) {
                $this->loadModel('CakeActionLog');

                $this->request->data['AttributeLang']['id_lang'] = Configure::read('app.langId');

                $this->Attribute->save($this->request->data['Attribute'], array(
                    'validate' => false
                ));
                if (is_null($attributeId)) {
                    $this->request->data['AttributeLang']['id_attribute'] = $this->Attribute->id;
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'attribute_added';
                } else {
                    $this->Attribute->AttributeLang->id = $attributeId;
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'attribute_changed';
                }

                $this->Attribute->AttributeLang->save($this->request->data, array(
                    'validate' => false
                ));

                if (isset($this->request->data['Attribute']['delete_attribute']) && $this->request->data['Attribute']['delete_attribute']) {
                    $this->Attribute->delete($this->Attribute->id); // cascade does not work here
                    $this->Attribute->AttributeLang->delete($this->Attribute->id); // AttributeLang record needs to be deleted manually
                    $message = 'Die Variante "' . $this->request->data['AttributeLang']['name'] . '" wurde erfolgreich gelöscht.';
                    $this->CakeActionLog->customSave('attribute_deleted', $this->AppAuth->getUserId(), $this->Attribute->id, 'attributes', $message);
                    $this->Flash->success('Die Variante wurde erfolgreich gelöscht.');
                } else {
                    $message = 'Die Variante "' . $this->request->data['AttributeLang']['name'] . '" wurde ' . $messageSuffix;
                    $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Attribute->id, 'attributes', $message);
                    $this->Flash->success('Die Variante wurde erfolgreich gespeichert.');
                }

                $this->Session->write('highlightedRowId', $this->Attribute->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }

    public function index()
    {
        $conditions = array();
        $conditions[] = 'Attribute.active > ' . APP_DEL;

        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'AttributeLang.name' => 'ASC'
            )
        ), $this->Paginator->settings);
        $attributes = $this->Paginator->paginate('Attribute');

        $this->loadModel('ProductAttributeCombination');
        foreach ($attributes as &$attribute) {
            $attribute['CombinationProducts'] = $this->ProductAttributeCombination->getCombinationCounts($attribute['Attribute']['id_attribute']);
        }

        $this->set('attributes', $attributes);

        $this->set('title_for_layout', 'Varianten');
    }
}
