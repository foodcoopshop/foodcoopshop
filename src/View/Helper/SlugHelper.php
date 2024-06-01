<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\View\Helper;
use App\Services\OutputFilter\OutputFilterService;

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
class SlugHelper extends Helper
{

    public function getFeedbackList()
    {
        return '/feedback';
    }

    public function getMyFeedbackForm()
    {
        return '/admin/feedbacks/myFeedback';
    }

    public function getFeedbackForm($customerId)
    {
        return '/admin/feedbacks/form/' . $customerId;
    }

    public function getInvoiceDownloadRoute($invoiceFilename)
    {
        return '/admin/lists/getInvoice?file=' . $invoiceFilename;
    }

    public function getHelloCashInvoice($invoiceId, $cancellation=0)
    {
        return '/admin/hello-cash/getInvoice/' . $invoiceId . '/' . $cancellation;
    }

    public function getHelloCashReceipt($invoiceId, $cancellation=0)
    {
        return '/admin/hello-cash/getReceipt/' . $invoiceId . '/' . $cancellation;
    }

    public function getSelfService($keyword = '', $productWithError = '', $selfServiceUser = '')
    {
        $url = '/'.__('route_self_service');
        $queryParams = [];
        if ($keyword != '') {
            $queryParams['keyword'] = $keyword;
        }
        if ($productWithError != '') {
            $queryParams['productWithError'] = $productWithError;
        }
        if ($selfServiceUser != '') {
            $queryParams['selfServiceUser'] = $selfServiceUser;
        }
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        return $url;
    }

    public function getActivateEmailAddress($activationCode)
    {
        return '/customers/activateEmailAddress/' . $activationCode;
    }

    public function getActionLogsList()
    {
        return $this->getAdminHome().'/action-logs';
    }

    public function getOrderDetailPurchasePriceEdit($orderDetailId)
    {
        return $this->getAdminHome().'/order-details/edit-purchase-price/' . $orderDetailId;
    }

    public function getOrderDetailsList()
    {
        return $this->getAdminHome().'/order-details';
    }

    public function getOrderLists()
    {
        return $this->getAdminHome().'/lists/order-lists';
    }

    public function getAcceptTermsOfUse()
    {
        return '/'.__('route_accept_terms_of_use');
    }

    public function getManufacturerDetail($manufacturerId, $manufacturerName)
    {
        return '/'.__('route_manufacturer_detail').'/'.$manufacturerId.'-'.StringComponent::slugify($manufacturerName);
    }

    public function getPageDetail($pageId, $name)
    {
        return '/'.__('route_content').'/'.$pageId.'-'.StringComponent::slugify($name);
    }

    public function getTermsOfUse()
    {
        return '/'.__('route_terms_of_use');
    }

    public function getPrivacyPolicy()
    {
        return '/'.__('route_privacy_policy');
    }

    public function getListOfAllergens()
    {
        return '/'.__('route_list_of_allergens');
    }

    public function getManufacturerList()
    {
        return '/'.__('route_manufacturer_list');
    }

    public function getMyStatistics()
    {
        return $this->getAdminHome().'/statistics/myIndex';
    }

    public function getStatistics($manufacturerId = '')
    {
        $url = '/admin/statistics';
        if ($manufacturerId != '') {
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getProductImport($manufacturerId = '') {
        $url = '/admin/products/import';
        if ($manufacturerId != '') {
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getMyProductImport()
    {
        return '/admin/products/myImport';
    }

    public function getMyDepositList()
    {
        return '/admin/deposits/myIndex';
    }

    public function getMyDepositDetail($monthAndYear)
    {
        return '/admin/deposits/myDetail/'.$monthAndYear;
    }

    public function getDepositList($manufacturerId = '')
    {
        $url = '/admin/deposits/index';
        if ($manufacturerId != '') {
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getDepositOverviewDiagram()
    {
        return '/admin/deposits/overview_diagram';
    }

    public function getDepositDetail($manufacturerId, $monthAndYear)
    {
        $url = '/admin/deposits/detail/'.$monthAndYear;
        if ($manufacturerId != '') {
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getCreditBalanceSum()
    {
        return '/admin/customers/credit_balance_sum';
    }

    public function getCartDetail()
    {
        return '/'.__('route_cart') . '/' . __('route_cart_show');
    }

    public function getCartFinish()
    {
        return '/'.__('route_cart') . '/' . __('route_cart_finish');
    }

    public function getCartFinished($cartId)
    {
        return '/'.__('route_cart') . '/' . __('route_cart_finished').'/'.$cartId;
    }

    public function getAdminHome()
    {
        return '/admin';
    }

    public function getHome()
    {
        return '/';
    }

    public function getNewProducts()
    {
        return '/' . __('route_new_products');
    }

    public function getProductSearch($keyword)
    {
        return '/' . __('route_search') . '?keyword=' . $keyword;
    }

    public function getAllProducts()
    {
        return $this->getCategoryDetail(Configure::read('app.categoryAllProducts'), __('route_all_products'));
    }

    public function getCategoryDetail($categoryId, $name)
    {
        // if "Produkte" is globally replaced with a word with an umlaut, this umlaut would not be replaced, so replace it here
        // eg: Produkte => Naturschätze
        if (Configure::check('app.outputStringReplacements')) {
            $name = OutputFilterService::replace($name, Configure::read('app.outputStringReplacements'));
        }
        return '/' . __('route_category') . '/' . $categoryId . '-' . StringComponent::slugify($name);
    }

    public function getLogin($redirect='')
    {
        $url = '/'.__('route_sign_in');
        if ($redirect != '') {
            $url .= '?redirect=' . urlencode($redirect);
        }
        return $url;
    }

    public function getLogout($redirect='')
    {
        $url = '/'.__('route_sign_out');
        if ($redirect != '') {
            $url .= '?redirect=' . urlencode($redirect);
        }
        return $url;
    }

    public function getRegistrationSuccessful()
    {
        return '/'.__('route_registration_successful');
    }

    public function getRegistration()
    {
        return '/'.__('route_registration');
    }

    public function getMyCreditBalance()
    {
        return '/admin/payments/overview';
    }

    public function getCreditBalance($customerId)
    {
        return '/admin/payments/product/?customerId='.$customerId;
    }

    public function getChangePassword()
    {
        return '/admin/customers/changePassword';
    }

    public function getCustomerProfile()
    {
        return '/admin/customers/profile';
    }

    public function getCustomerEdit($customerId)
    {
        return '/admin/customers/edit/' . $customerId;
    }

    public function getCustomerListAdmin()
    {
        return '/admin/customers';
    }

    public function getManufacturerProfile()
    {
        return '/admin/manufacturers/profile';
    }

    public function getManufacturerMyOptions()
    {
        return '/admin/manufacturers/myOptions';
    }

    public function getActivateNewPassword($activateNewPasswordCode)
    {
        return '/'.__('route_activate_new_password') . '/' . $activateNewPasswordCode;
    }

    public function getNewPasswordRequest()
    {
        return '/'.__('route_request_new_password');
    }

    public function getProfit($dateFrom=null, $dateTo=null, $customerId=null, $manufacturerId=null, $productId=null)
    {
        $url = '/admin/order-details/profit';
        if ($dateFrom !== null) {
            $urlParams['dateFrom'] = $dateFrom;
        }
        if ($dateTo !== null) {
            $urlParams['dateTo'] = $dateTo;
        }
        if ($customerId !== null) {
            $urlParams['customerId'] = $customerId;
        }
        if ($manufacturerId !== null) {
            $urlParams['manufacturerId'] = $manufacturerId;
        }
        if ($productId !== null) {
            $urlParams['productId'] = $productId;
        }
        if (!empty($urlParams)) {
            $url .= '?' . http_build_query($urlParams);
        }
        return $url;
    }

    public function getInvoices()
    {
        return '/admin/invoices';
    }

    public function getMyInvoices()
    {
        return '/admin/invoices/myInvoices';
    }

    public function getReport($paymentType)
    {
        return '/admin/reports/payments/'.$paymentType;
    }

    public function getBlogList()
    {
        return '/'.__('route_news_list');
    }

    public function getBlogPostDetail($blogPostId, $name)
    {
        return '/'.__('route_news_detail') . '/' . $blogPostId . '-' . StringComponent::slugify($name);
    }

    public function getBlogPostListAdmin()
    {
        return '/admin/blog-posts';
    }
    public function getBlogPostEdit($blogPostId)
    {
        return '/admin/blog-posts/edit/'.$blogPostId;
    }
    public function getBlogPostAdd()
    {
        return '/admin/blog-posts/add';
    }

    public function getPagesListAdmin()
    {
        return '/admin/pages';
    }

    public function getPageEdit($pageId)
    {
        return '/admin/pages/edit/'.$pageId;
    }

    public function getPageAdd()
    {
        return '/admin/pages/add';
    }

    public function getPaymentEdit($paymentId)
    {
        return '/admin/payments/edit/'.$paymentId;
    }

    public function getAttributesList()
    {
        return '/admin/attributes';
    }
    public function getAttributeAdd()
    {
        return '/admin/attributes/add';
    }
    public function getAttributeEdit($attributeId)
    {
        return '/admin/attributes/edit/'.$attributeId;
    }

    public function getCategoriesList()
    {
        return '/admin/categories';
    }
    public function getCategoryAdd()
    {
        return '/admin/categories/add';
    }
    public function getCategoryEdit($categoryId)
    {
        return '/admin/categories/edit/'.$categoryId;
    }

    public function getTaxesList()
    {
        return '/admin/taxes';
    }
    public function getTaxAdd()
    {
        return '/admin/taxes/add';
    }
    public function getTaxEdit($taxId)
    {
        return '/admin/taxes/edit/'.$taxId;
    }

    public function getManufacturerAdmin()
    {
        return '/admin/manufacturers';
    }
    public function getManufacturerEdit($manufacturerId)
    {
        return '/admin/manufacturers/edit/'.$manufacturerId;
    }
    public function getManufacturerEditOptions($manufacturerId)
    {
        return '/admin/manufacturers/editOptions/'.$manufacturerId;
    }
    public function getManufacturerAdd()
    {
        return '/admin/manufacturers/add';
    }

    public function getSlidersList()
    {
        return '/admin/sliders';
    }
    public function getSliderEdit($slideId)
    {
        return '/admin/sliders/edit/'.$slideId;
    }
    public function getSliderAdd()
    {
        return '/admin/sliders/add';
    }

    public function getProductAdmin($manufacturerId = null, $productId = null)
    {
        $url = '/admin/products';

        if (!empty($manufacturerId)) {
            $urlParams['manufacturerId'] = $manufacturerId;
        }
        if (!empty($productId)) {
            $urlParams['productId'] = $productId;
        }
        if (!empty($urlParams)) {
            $url .= '?' . http_build_query($urlParams);
        }
        return $url;
    }

    public function getProductDetail($productId, $name)
    {
        return '/' . __('routes_product') . '/' . $productId . '-' . StringComponent::slugify($name);
    }

    public function getConfigurationsList()
    {
        return '/admin/configurations';
    }

    public function getConfigurationEdit($configurationId)
    {
        return '/admin/configurations/edit/'.$configurationId;
    }

    public function getCronjobsList()
    {
        return '/admin/cronjobs';
    }

    public function getCronjobEdit($cronjobId)
    {
        return '/admin/cronjobs/edit/'.$cronjobId;
    }

}
