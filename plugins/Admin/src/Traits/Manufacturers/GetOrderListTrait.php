<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers;

use Cake\Core\Configure;
use App\Services\PdfWriter\OrderListByProductPdfWriterService;
use App\Services\PdfWriter\OrderListByCustomerPdfWriterService;
use App\Services\PdfWriter\PdfWriterService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait GetOrderListTrait
{
    /* void and Response not possible as return type - needs refactoring */
    /** @phpstan-ignore-next-line */
    public function getOrderListByProduct()
    {
        $pdfWriter = new OrderListByProductPdfWriterService();
        return $this->getOrderList('product', $pdfWriter);
    }

    /* void and Response not possible as return type - needs refactoring */
    /** @phpstan-ignore-next-line */
    public function getOrderListByCustomer()
    {
        $pdfWriter = new OrderListByCustomerPdfWriterService();
        return $this->getOrderList('customer', $pdfWriter);
    }

    /* void and Response not possible as return type - needs refactoring */
    /** @phpstan-ignore-next-line */
    protected function getOrderList(string $type, OrderListByProductPdfWriterService|OrderListByCustomerPdfWriterService $pdfWriter)
    {

        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        $pickupDay = h($this->getRequest()->getQuery('pickupDay'));
        $pickupDayDbFormat = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $manufacturer = $manufacturersTable->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();

        if (!in_array('isAnonymized', array_keys($this->getRequest()->getQueryParams()))) {
            $isAnonymized = $manufacturer->anonymize_customers;
        } else {
            $isAnonymized = h($this->getRequest()->getQuery('isAnonymized'));
        }

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $orderDetailsTable->getOrderDetailsForOrderListPreview($pickupDayDbFormat);
        $orderDetails->where(['Products.id_manufacturer' => $manufacturerId]);
        $orderDetailIds = $orderDetails->all()->extract('id_order_detail')->toArray();

        if (empty($orderDetailIds)) {
            // do not throw exception because no debug mails wanted
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }

        if ($type == 'product') {
            $typeString = __d('admin', 'product');
        } else {
            $typeString = __d('admin', 'member');
        }

        $pdfFile = $this->getOrderListFilenameForWriteInline($manufacturerId, $manufacturer->name, $pickupDay, $typeString);
        $pdfWriter->setFilename($pdfFile);

        $pdfWriter->prepareAndSetData($manufacturerId, $pickupDayDbFormat, [], $orderDetailIds, $isAnonymized);
        if (!empty($this->request->getQuery('outputType')) && $this->request->getQuery('outputType') == 'html') {
            return $this->response->withStringBody($pdfWriter->writeHtml());
        }

        die($pdfWriter->writeInline());

    }

    private function getOrderListFilenameForWriteInline($manufacturerId, $manufacturerName, $pickupDay, $type): string
    {
        $currentDateForOrderLists = Configure::read('app.timeHelper')->getCurrentDateTimeForFilename();
        $productPdfFile = Configure::read('app.htmlHelper')->getOrderListLink($manufacturerName, $manufacturerId, $pickupDay, $type, $currentDateForOrderLists, false);
        $productPdfFile = explode(DS, $productPdfFile);
        $productPdfFile = end($productPdfFile);
        $productPdfFile = substr($productPdfFile, 11);
        $productPdfFile = $pickupDay . '-' . $productPdfFile;
        return $productPdfFile;
    }

}