<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use App\Services\SanitizeService;
use App\Model\Entity\Tax;
use Cake\Http\Response;

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
    
    public function add(): ?Response
    {
        $taxesTable = $this->getTableLocator()->get('Taxes');
        $tax = $taxesTable->newEntity(
            [
                'rate' => 0,
                'active' => APP_ON,
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', __('Add_tax_rate'));
        $this->_processForm($tax, false);

        if (empty($this->getRequest()->getData())) {
            return $this->render('edit');
        }
        return null;
    }

    public function edit(int $taxId): ?Response
    {
        $taxesTable = $this->getTableLocator()->get('Taxes');
        $tax = $taxesTable->find('all', conditions: [
            'Taxes.id_tax' => $taxId
        ])->first();

        if (empty($tax)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __('Edit_tax_rate'));
        return $this->_processForm($tax, true);
    }

    private function _processForm(Tax $tax, bool $isEditMode): ?Response
    {

        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('tax', $tax);
            return null;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $taxesTable = $this->getTableLocator()->get('Taxes');
        $tax = $taxesTable->patchEntity($tax, $this->getRequest()->getData());
        if ($tax->hasErrors()) {
            $this->Flash->error(__('Errors_while_saving!_admin'));
            $this->set('tax', $tax);
            return $this->render('edit');
        } else {
            $tax = $taxesTable->save($tax);

            if (!$isEditMode) {
                $messageSuffix = __('created');
                $actionLogType = 'tax_added';
            } else {
                $messageSuffix = __('changed');
                $actionLogType = 'tax_changed';
            }

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $message = __('The_tax_rate_{0}_has_been_{1}.', ['<b>' . Configure::read('app.numberHelper')->formatAsPercent($tax->rate) . '</b>', $messageSuffix]);
            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $tax->id_tax, 'taxes', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $tax->id_tax);
            return $this->redirect($this->getPreparedReferer());
        }

    }

    public function index(): void
    {
        $conditions = [
            'Taxes.active > ' . APP_DEL
        ];

        $taxesTable = $this->getTableLocator()->get('Taxes');
        $query = $taxesTable->find('all', conditions: $conditions);
        $query->select($taxesTable)
            ->select([
                'product_count' => $query->func()->count('Products.id_product')
            ])
            ->leftJoinWith('Products', function ($q) {
                return $q->where(['Products.active IN' => [APP_ON, APP_OFF]]);
            })
            ->groupBy(['Taxes.id_tax']);

        $taxes = $this->paginate($query, [
            'sortableFields' => [
                'Taxes.rate', 'Taxes.position', 'Taxes.product_count',
            ],
            'order' => [
                'Taxes.active' => 'DESC',
                'Taxes.rate' => 'ASC'
            ]
        ]);

        $this->set('taxes', $taxes);

        $productsTable = $this->getTableLocator()->get('Products');
        $zeroTaxProductCount = $productsTable->find('all', conditions: [
            'id_tax' => 0,
            'active IN' => [APP_ON, APP_OFF],
        ])->count();
        $this->set('zeroTaxProductCount', $zeroTaxProductCount);

        $this->set('title_for_layout', __('Tax_rates'));
    }
}
