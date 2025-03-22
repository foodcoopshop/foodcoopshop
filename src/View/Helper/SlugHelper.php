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

    public function getAutoLoginAsSelfServiceCustomer(int $id): string
    {
        return '/'.__('route_self_service').'/autoLoginAsSelfServiceCustomer/' . $id;
    }

    public function getFeedbackList(): string
    {
        return '/feedback';
    }

    public function getMyFeedbackForm(): string
    {
        return '/admin/feedbacks/myFeedback';
    }

    public function getFeedbackForm(int $customerId): string
    {
        return '/admin/feedbacks/form/' . $customerId;
    }

    public function getInvoiceDownloadRoute(string $filename): string
    {
        return '/admin/lists/getInvoice?file=' . $filename;
    }

    public function getOrderListDownloadRoute(string $filename): string
    {
        return '/admin/lists/getOrderList?file=' . $filename;
    }

    public function getHelloCashInvoice(string|int $invoiceId, int $cancellation=0): string
    {
        return '/admin/hello-cash/getInvoice/' . $invoiceId . '/' . $cancellation;
    }

    public function getHelloCashReceipt(string|int $invoiceId, int $cancellation=0): string
    {
        return '/admin/hello-cash/getReceipt/' . $invoiceId . '/' . $cancellation;
    }

    public function getSelfService(string|int $keyword = '', string $productWithError = ''): string
    {
        $url = '/'.__('route_self_service');
        $queryParams = [];
        if ($keyword != '') {
            $queryParams['keyword'] = $keyword;
        }
        if ($productWithError != '') {
            $queryParams['productWithError'] = $productWithError;
        }
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        return $url;
    }

    public function getActivateEmailAddress(string $activationCode): string
    {
        return '/customers/activateEmailAddress/' . $activationCode;
    }

    public function getActionLogsList(): string
    {
        return $this->getAdminHome().'/action-logs';
    }

    public function getOrderDetailPurchasePriceEdit(int $orderDetailId): string
    {
        return $this->getAdminHome().'/order-details/edit-purchase-price/' . $orderDetailId;
    }

    public function getOrderDetailsList(): string
    {
        return $this->getAdminHome().'/order-details';
    }

    public function getOrderLists(): string
    {
        return $this->getAdminHome().'/lists/order-lists';
    }

    public function getManufacturerInvoices(): string
    {
        return $this->getAdminHome().'/lists/invoices';
    }

    public function getAcceptTermsOfUse(): string
    {
        return '/'.__('route_accept_terms_of_use');
    }

    public function getManufacturerDetail(int $manufacturerId, string $manufacturerName): string
    {
        return '/'.__('route_manufacturer_detail').'/'.$manufacturerId.'-'.StringComponent::slugify($manufacturerName);
    }

    public function getPageDetail(int $pageId, string $name): string
    {
        return '/'.__('route_content').'/'.$pageId.'-'.StringComponent::slugify($name);
    }

    public function getTermsOfUse(): string
    {
        return '/'.__('route_terms_of_use');
    }

    public function getPrivacyPolicy(): string
    {
        return '/'.__('route_privacy_policy');
    }

    public function getListOfAllergens(): string
    {
        return '/'.__('route_list_of_allergens');
    }

    public function getManufacturerList(): string
    {
        return '/'.__('route_manufacturer_list');
    }

    public function getMyStatistics(): string
    {
        return $this->getAdminHome().'/statistics/myIndex';
    }

    public function getStatistics(string|int $manufacturerId = ''): string
    {
        $url = '/admin/statistics';
        if ($manufacturerId != '') {
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getProductImport(string|int $manufacturerId = ''): string
    {
        $url = '/admin/products/import';
        if ($manufacturerId != '') {
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getMyProductImport(): string
    {
        return '/admin/products/myImport';
    }

    public function getMyDepositList(): string
    {
        return '/admin/deposits/myIndex';
    }

    public function getMyDepositDetail(string $monthAndYear): string
    {
        return '/admin/deposits/myDetail/'.$monthAndYear;
    }

    public function getDepositList(string|int $manufacturerId = ''): string
    {
        $url = '/admin/deposits/index';
        if ($manufacturerId != '') {
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getDepositOverviewDiagram(): string
    {
        return '/admin/deposits/overview_diagram';
    }

    public function getDepositDetail(string|int $manufacturerId, string $monthAndYear): string
    {
        $url = '/admin/deposits/detail/'.$monthAndYear;
        if ($manufacturerId != '') {
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getCreditBalanceSum(): string
    {
        return '/admin/customers/credit_balance_sum';
    }

    public function getCartDetail(): string
    {
        return '/'.__('route_cart') . '/' . __('route_cart_show');
    }

    public function getCartFinish(): string
    {
        return '/'.__('route_cart') . '/' . __('route_cart_finish');
    }

    public function getCartFinished(int $cartId): string
    {
        return '/'.__('route_cart') . '/' . __('route_cart_finished') . '/' . $cartId;
    }

    public function getAdminHome(): string
    {
        return '/admin';
    }

    public function getHome(): string
    {
        return '/';
    }

    public function getNewProducts(): string
    {
        return '/' . __('route_new_products');
    }

    public function getRandomProducts(): string
    {
        return '/' . __('route_random_products');
    }

    public function getChangeShowOnlyProductsForNextWeek(): string
    {
        return '/admin/customers/changeShowOnlyProductsForNextWeek';
    }

    public function getProductSearch(string $keyword): string
    {
        return '/' . __('route_search') . '?keyword=' . $keyword;
    }

    public function getAllProducts(): string
    {
        return $this->getCategoryDetail(Configure::read('app.categoryAllProducts'), __('route_all_products'));
    }

    public function getCategoryDetail(int $categoryId, string $name): string
    {
        // if "Produkte" is globally replaced with a word with an umlaut, this umlaut would not be replaced, so replace it here
        // eg: Produkte => Naturschätze
        if (Configure::check('app.outputStringReplacements')) {
            $name = OutputFilterService::replace($name, Configure::read('app.outputStringReplacements'));
        }
        return '/' . __('route_category') . '/' . $categoryId . '-' . StringComponent::slugify($name);
    }

    public function getLogin(?string $redirect=''): string
    {
        $url = '/'.__('route_sign_in');
        if ($redirect != '') {
            $url .= '?redirect=' . urlencode($redirect);
        }
        return $url;
    }

    public function getLogout(?string $redirect=''): string
    {
        $url = '/'.__('route_sign_out');
        if ($redirect != '') {
            $url .= '?redirect=' . urlencode($redirect);
        }
        return $url;
    }

    public function getRegistrationSuccessful(): string
    {
        return '/'.__('route_registration_successful');
    }

    public function getRegistration(): string
    {
        return '/'.__('route_registration');
    }

    public function getMyCreditBalance(): string
    {
        return '/admin/payments/overview';
    }

    public function getCreditBalance(int $customerId): string
    {
        return '/admin/payments/product/?customerId='.$customerId;
    }

    public function getChangePassword(): string
    {
        return '/admin/customers/changePassword';
    }

    public function getCustomerProfile(): string
    {
        return '/admin/customers/profile';
    }

    public function getCustomerEdit(int $customerId): string
    {
        return '/admin/customers/edit/' . $customerId;
    }

    public function getCustomerListAdmin(): string
    {
        return '/admin/customers';
    }

    public function getManufacturerProfile(): string
    {
        return '/admin/manufacturers/profile';
    }

    public function getManufacturerMyOptions(): string
    {
        return '/admin/manufacturers/myOptions';
    }

    public function getActivateNewPassword(string $activateNewPasswordCode): string
    {
        return '/'.__('route_activate_new_password') . '/' . $activateNewPasswordCode;
    }

    public function getNewPasswordRequest(): string
    {
        return '/'.__('route_request_new_password');
    }

    public function getProfit(?string $dateFrom=null, ?string $dateTo=null, ?string $customerIds=null, ?int $manufacturerId=null, ?int $productId=null): string
    {
        $url = '/admin/order-details/profit';
        if ($dateFrom !== null) {
            $urlParams['dateFrom'] = $dateFrom;
        }
        if ($dateTo !== null) {
            $urlParams['dateTo'] = $dateTo;
        }
        if ($customerIds !== null) {
            $urlParams['customerIds'] = $customerIds;
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

    public function getInvoices(): string
    {
        return '/admin/invoices';
    }

    public function getMyInvoices(): string
    {
        return '/admin/invoices/myInvoices';
    }

    public function getReport(string $paymentType): string
    {
        return '/admin/reports/payments/'.$paymentType;
    }

    public function getBlogList(): string
    {
        return '/'.__('route_news_list');
    }

    public function getBlogPostDetail(int $blogPostId, string $name): string
    {
        return '/'.__('route_news_detail') . '/' . $blogPostId . '-' . StringComponent::slugify($name);
    }

    public function getBlogPostListAdmin(): string
    {
        return '/admin/blog-posts';
    }
    public function getBlogPostEdit(int $blogPostId): string
    {
        return '/admin/blog-posts/edit/'.$blogPostId;
    }
    public function getBlogPostAdd(): string
    {
        return '/admin/blog-posts/add';
    }

    public function getPagesListAdmin(): string
    {
        return '/admin/pages';
    }

    public function getPageEdit(int $pageId): string
    {
        return '/admin/pages/edit/'.$pageId;
    }

    public function getPageAdd(): string
    {
        return '/admin/pages/add';
    }

    public function getPaymentEdit(int $paymentId): string
    {
        return '/admin/payments/edit/'.$paymentId;
    }

    public function getAttributesList(): string
    {
        return '/admin/attributes';
    }
    public function getAttributeAdd(): string
    {
        return '/admin/attributes/add';
    }
    public function getAttributeEdit(int $attributeId): string
    {
        return '/admin/attributes/edit/'.$attributeId;
    }

    public function getCategoriesList(): string
    {
        return '/admin/categories';
    }
    public function getCategoryAdd(): string
    {
        return '/admin/categories/add';
    }
    public function getCategoryEdit(int $categoryId): string
    {
        return '/admin/categories/edit/'.$categoryId;
    }

    public function getTaxesList(): string
    {
        return '/admin/taxes';
    }
    public function getTaxAdd(): string
    {
        return '/admin/taxes/add';
    }
    public function getTaxEdit(int $taxId): string
    {
        return '/admin/taxes/edit/'.$taxId;
    }

    public function getManufacturerAdmin(): string
    {
        return '/admin/manufacturers';
    }
    public function getManufacturerEdit(int $manufacturerId): string
    {
        return '/admin/manufacturers/edit/'.$manufacturerId;
    }
    public function getManufacturerEditOptions(int $manufacturerId): string
    {
        return '/admin/manufacturers/editOptions/'.$manufacturerId;
    }
    public function getManufacturerAdd(): string
    {
        return '/admin/manufacturers/add';
    }

    public function getSlidersList(): string
    {
        return '/admin/sliders';
    }
    public function getSliderEdit(int $slideId): string
    {
        return '/admin/sliders/edit/'.$slideId;
    }
    public function getSliderAdd(): string
    {
        return '/admin/sliders/add';
    }

    public function getProductAdmin(string|int|null $manufacturerId = null, ?int $productId = null): string
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

    public function getProductDetail(int $productId, string $name): string
    {
        return '/' . __('routes_product') . '/' . $productId . '-' . StringComponent::slugify($name);
    }

    public function getConfigurationsList(): string
    {
        return '/admin/configurations';
    }

    public function getConfigurationEdit(string $name): string
    {
        return '/admin/configurations/edit/'.$name;
    }

    public function getCronjobsList(): string
    {
        return '/admin/cronjobs';
    }

    public function getCronjobEdit(int $cronjobId): string
    {
        return '/admin/cronjobs/edit/'.$cronjobId;
    }

}
