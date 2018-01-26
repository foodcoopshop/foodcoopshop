<?php

use Admin\Controller\AdminAppController;
use Cake\Core\Configure;
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
            $unsavedTax = $this->Tax->find('all', [
                'conditions' => [
                    'Taxes.id_tax' => $taxId
                ]
            ])->first();
        } else {
            // default value
            $unsavedTax = [
                'Taxes' => [
                    'active' => true
                ]
            ];
        }

        $this->set('unsavedTax', $unsavedTax);
        $this->set('title_for_layout', 'Steuersatz bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedTax;
        } else {
            // validate data - do not use $this->Tax->saveAll()
            $this->Tax->id = $taxId;
            $this->Tax->set($this->request->data['Taxes']);

            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['Taxes'] as &$data) {
                $data = strip_tags(trim($data));
            }

            $errors = [];
            if (! $this->Tax->validates()) {
                $errors = array_merge($errors, $this->Tax->validationErrors);
            }

            if (empty($errors)) {
                $this->ActionLog = TableRegistry::get('ActionLogs');

                $this->Tax->save($this->request->data['Taxes'], [
                    'validate' => false
                ]);
                if (is_null($taxId)) {
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'tax_added';
                } else {
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'tax_changed';
                }

                if (isset($this->request->data['Taxes']['delete_tax']) && $this->request->data['Taxes']['delete_tax']) {
                    $this->Tax->delete($this->Tax->id); // cascade does not work here
                    $message = 'Der Steuersatz "' . Configure::read('app.htmlHelper')->formatAsPercent($this->request->data['Taxes']['rate']) . '" wurde erfolgreich gelöscht.';
                    $this->ActionLog->customSave('tax_deleted', $this->AppAuth->getUserId(), $this->Tax->id, 'taxes', $message);
                    $this->Flash->success('Der Steuersatz wurde erfolgreich gelöscht.');
                } else {
                    if ($taxId > 0) {
                        $taxRate = $unsavedTax['Taxes']['rate'];
                    } else {
                        $taxRate = $this->request->data['Taxes']['rate'];
                    }
                    $message = 'Der Steuersatz "' . Configure::read('app.htmlHelper')->formatAsPercent($taxRate) . '" wurde ' . $messageSuffix;
                    $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Tax->id, 'taxes', $message);
                    $this->Flash->success('Der Steuersatz wurde erfolgreich gespeichert.');
                }

                $this->request->session()->write('highlightedRowId', $this->Tax->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }

    public function index()
    {
        $conditions = [];
        $conditions[] = 'Taxes.active > ' . APP_DEL;

        $this->Paginator->settings = array_merge([
            'conditions' => $conditions,
            'order' => [
                'Taxes.rate' => 'ASC'
            ]
        ], $this->Paginator->settings);
        $taxes = $this->Paginator->paginate('Taxes');
        $this->set('taxes', $taxes);

        $this->set('title_for_layout', 'Steuersätze');
    }
}
