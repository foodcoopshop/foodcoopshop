<?php
namespace App\Queue\Task;

use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Queue\Queue\Task;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class SendOrderConfirmationEmailToCustomerTask extends Task {

    public $timeout = 30;

    public $retries = 2;

    public $Invoice;

    public function run(array $data, $jobId) : void
    {

        $cart = $data['cart'];
        $cartGroupedByPickupDay = $data['cartGroupedByPickupDay'];
        $products = $data['products'];
        $pickupDayEntities = $data['pickupDayEntities'];
        $loggedUser = $data['loggedUser'];
        $originalLoggedCustomer = $data['originalLoggedCustomer'];

        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('order_successful');
        $email->setTo($loggedUser['email'])
        ->setSubject(__('Order_confirmation'))
        ->setViewVars([
            'cart' => $cartGroupedByPickupDay,
            'pickupDayEntities' => $pickupDayEntities,
            'customer' => $loggedUser,
            'originalLoggedCustomer' => $originalLoggedCustomer,
        ]);

        if (Configure::read('app.rightOfWithdrawalEnabled')) {
            $email->addAttachments([__('Filename_Right-of-withdrawal-information-and-form').'.pdf' => ['data' => $this->generateRightOfWithdrawalInformationAndForm($cart, $products), 'mimetype' => 'application/pdf']]);
        }

        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            $email->addAttachments([__('Filename_Order-confirmation').'.pdf' => ['data' => $this->generateOrderConfirmation($cart), 'mimetype' => 'application/pdf']]);
        }
        if (Configure::read('app.generalTermsAndConditionsEnabled')) {
            $generalTermsAndConditionsFiles = [];
            $uniqueManufacturers = $this->getUniqueManufacturers();
            foreach($uniqueManufacturers as $manufacturerId => $manufacturer) {
                $src = Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrc($manufacturerId);
                if ($src !== false) {
                    $generalTermsAndConditionsFiles[__('Filename_General-terms-and-conditions') . '-' . StringComponent::slugify($manufacturer['name']) . '.pdf'] = [
                        'file' => WWW_ROOT . Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrcTemplate($manufacturerId), // avoid timestamp
                        'mimetype' => 'application/pdf'
                    ];
                }
            }
            if (count($uniqueManufacturers) > count($generalTermsAndConditionsFiles)) {
                $generalTermsAndConditionsFiles[__('Filename_General-terms-and-conditions').'.pdf'] = [
                    'data' => $this->generateGeneralTermsAndConditions(),
                    'mimetype' => 'application/pdf'
                ];
            }

            $email->addAttachments($generalTermsAndConditionsFiles);
        }

        $email->send();

    }

}
?>