<?php
namespace App\Queue\Task;

use App\Controller\Component\StringComponent;
use App\Lib\PdfWriter\GeneralTermsAndConditionsPdfWriter;
use App\Lib\PdfWriter\InformationAboutRightOfWithdrawalPdfWriter;
use App\Lib\PdfWriter\OrderConfirmationPdfWriter;
use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
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
        $loggedUser = json_decode(json_encode($data['loggedUser']), false); // convert array recursively into object
        $originalLoggedCustomer = json_decode(json_encode($data['originalLoggedCustomer']), false); // convert array recursively into object

        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('order_successful');
        $email->setTo($loggedUser->email)
        ->setSubject(__('Order_confirmation'))
        ->setViewVars([
            'cart' => $cartGroupedByPickupDay,
            'pickupDayEntities' => $pickupDayEntities,
            'customer' => $loggedUser,
            'newsletterCustomer' => $loggedUser,
            'originalLoggedCustomer' => $originalLoggedCustomer,
        ]);

        if (Configure::read('app.rightOfWithdrawalEnabled')) {
            $email->addAttachments([__('Filename_Right-of-withdrawal-information-and-form').'.pdf' => ['data' => $this->generateRightOfWithdrawalInformationAndForm($cart, $products, $loggedUser), 'mimetype' => 'application/pdf']]);
        }

        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            $email->addAttachments([__('Filename_Order-confirmation').'.pdf' => ['data' => $this->generateOrderConfirmation($cart, $loggedUser), 'mimetype' => 'application/pdf']]);
        }
        if (Configure::read('app.generalTermsAndConditionsEnabled')) {
            $generalTermsAndConditionsFiles = [];
            $uniqueManufacturers = $this->getUniqueManufacturers($products);
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

    protected function generateRightOfWithdrawalInformationAndForm($cart, $products, $customer)
    {
        $manufacturers = [];
        foreach ($products as $product) {
            $manufacturers[$product->manufacturer->id_manufacturer][] = $product;
        }

        $pdfWriter = new InformationAboutRightOfWithdrawalPdfWriter();
        $pdfWriter->setData([
            'products' => $products,
            'customer' => $customer,
            'cart' => $cart,
            'manufacturers' => $manufacturers,
        ]);
        return $pdfWriter->writeAttachment();
    }

    protected function generateGeneralTermsAndConditions()
    {
        $pdfWriter = new GeneralTermsAndConditionsPdfWriter();
        return $pdfWriter->writeAttachment();
    }

    protected function generateOrderConfirmation($cart, $customer)
    {

        $manufacturers = [];
        $this->Cart = FactoryLocator::get('Table')->get('Carts');
        $cart = $this->Cart->find('all', [
            'conditions' => [
                'Carts.id_cart' => $cart['Cart']->id_cart,
            ],
            'contain' => [
                'CartProducts.OrderDetails',
                'CartProducts.Products',
                'CartProducts.Products.Manufacturers.AddressManufacturers'
            ]
        ])->first();

        foreach ($cart->cart_products as $cartProduct) {
            $manufacturers[$cartProduct->product->id_manufacturer] = [
                'CartProducts' => $cart->cart_products,
                'Manufacturer' => $cartProduct->product->manufacturer
            ];
        }

        $pdfWriter = new OrderConfirmationPdfWriter();
        $pdfWriter->setData([
            'customer' => $customer,
            'cart' => $cart,
            'manufacturers' => $manufacturers,
        ]);
        return $pdfWriter->writeAttachment();
    }

    protected function getUniqueManufacturers($products)
    {
        $manufactures = [];
        foreach ($products as $product) {
            $manufactures[$product['manufacturerId']] = [
                'name' => $product['manufacturerName']
            ];
        }
        return $manufactures;
    }

}
?>