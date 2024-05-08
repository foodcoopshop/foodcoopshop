<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Core\Configure;
use App\Mailer\AppMailer;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Services\PdfWriter\MyMemberCardPdfWriterService;
use App\Services\PdfWriter\MemberCardsPdfWriterService;

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

trait GenerateMemberCardsTrait
{

    public function generateMyMemberCard()
    {
        $customerId = $this->identity->getId();
        $pdfWriter = new MyMemberCardPdfWriterService();
        $customers = $pdfWriter->getMemberCardCustomerData($customerId);
        $pdfWriter->setFilename(__d('admin', 'Member_card') . ' ' . $customers->toArray()[0]->name.'.pdf');
        $pdfWriter->setData([
            'customers' => $customers,
        ]);
        die($pdfWriter->writeInline());
    }

    public function generateMemberCards()
    {
        $customerIds = h($this->getRequest()->getQuery('customerIds'));
        $customerIds = explode(',', $customerIds);
        $pdfWriter = new MemberCardsPdfWriterService();
        $pdfWriter->setFilename(__d('admin', 'Members') . ' ' . Configure::read('appDb.FCS_APP_NAME').'.pdf');
        $pdfWriter->setData([
            'customers' => $pdfWriter->getMemberCardCustomerData($customerIds),
        ]);
        die($pdfWriter->writeInline());
    }

}