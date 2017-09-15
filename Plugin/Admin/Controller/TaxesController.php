<?php
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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
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
        $this->edit();
        $this->set('title_for_layout', 'Steuersatz erstellen');
        $this->render('edit');
    }

    public function edit($taxId = null)
    {
        $this->setFormReferer();

        if ($taxId > 0) {
            $unsavedTax = $this->Tax->find('first', array(
                'conditions' => array(
                    'Tax.id_tax' => $taxId
                )
            ));
        } else {
            // default value
            $unsavedTax = array(
                'Tax' => array(
                    'active' => true
                )
            );
        }

        $this->set('unsavedTax', $unsavedTax);
        $this->set('title_for_layout', 'Steuersatz bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedTax;
        } else {
            // validate data - do not use $this->Tax->saveAll()
            $this->Tax->id = $taxId;
            $this->Tax->set($this->request->data['Tax']);

            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['Tax'] as &$data) {
                $data = strip_tags(trim($data));
            }

            $errors = array();
            if (! $this->Tax->validates()) {
                $errors = array_merge($errors, $this->Tax->validationErrors);
            }

            if (empty($errors)) {
                $this->loadModel('CakeActionLog');

                $this->Tax->save($this->request->data['Tax'], array(
                    'validate' => false
                ));
                if (is_null($taxId)) {
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'tax_added';
                } else {
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'tax_changed';
                }

                if (isset($this->request->data['Tax']['delete_tax']) && $this->request->data['Tax']['delete_tax']) {
                    $this->Tax->delete($this->Tax->id); // cascade does not work here
                    $message = 'Der Steuersatz "' . Configure::read('htmlHelper')->formatAsPercent($this->request->data['Tax']['rate']) . '" wurde erfolgreich gelöscht.';
                    $this->CakeActionLog->customSave('tax_deleted', $this->AppAuth->getUserId(), $this->Tax->id, 'taxes', $message);
                    $this->Flash->success('Der Steuersatz wurde erfolgreich gelöscht.');
                } else {
                    if ($taxId > 0) {
                        $taxRate = $unsavedTax['Tax']['rate'];
                    } else {
                        $taxRate = $this->request->data['Tax']['rate'];
                    }
                    $message = 'Der Steuersatz "' . Configure::read('htmlHelper')->formatAsPercent($taxRate) . '" wurde ' . $messageSuffix;
                    $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Tax->id, 'taxes', $message);
                    $this->Flash->success('Der Steuersatz wurde erfolgreich gespeichert.');
                }

                $this->Session->write('highlightedRowId', $this->Tax->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }

    public function index()
    {
        $conditions = array();
        $conditions[] = 'Tax.active > ' . APP_DEL;

        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'Tax.rate' => 'ASC'
            )
        ), $this->Paginator->settings);
        $taxes = $this->Paginator->paginate('Tax');
        $this->set('taxes', $taxes);

        $this->set('title_for_layout', 'Steuersätze');
    }
}
