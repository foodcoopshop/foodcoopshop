<?php

namespace App\View\Helper;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SlugHelper extends Helper
{


    public function getSelfService($keyword = '')
    {
        $url = '/'.__('route_self_service');
        if ($keyword != '') {
            $url .= '?keyword=' . $keyword;
        }
        return $url;
    }

    public function getActionLogsList()
    {
        return $this->getAdminHome().'/action-logs';
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

    public function getManufacturerBlogList($manufacturerId, $manufacturerName)
    {
        return $this->getManufacturerDetail($manufacturerId, $manufacturerName) . '/' . __('route_news');
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

    public function getAllProducts()
    {
        return $this->getCategoryDetail(Configure::read('app.categoryAllProducts'), __('route_all_products'));
    }

    public function getCategoryDetail($categoryId, $name)
    {
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

    public function getLogout()
    {
        return '/'.__('route_sign_out');
    }

    public function getRegistrationSuccessful()
    {
        return '/'.__('route_registration_successful');
    }

    public function getRegistration()
    {
        return '/'.__('route_registration');
    }

    public function getMyTimebasedCurrencyBalanceForManufacturers()
    {
        return '/admin/timebased-currency-payments/my-payments-manufacturer';
    }

    public function getTimebasedCurrencyBalanceForManufacturers($manufacturerId)
    {
        return '/admin/timebased-currency-payments/payments-manufacturer/' . $manufacturerId;
    }

    public function getMyTimebasedCurrencyBalanceForCustomers($manufacturerId = null)
    {
        $url = '/admin/timebased-currency-payments/my-payments-customer';
        if (!is_null($manufacturerId)){
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getTimebasedCurrencyPaymentDetailsForManufacturers($customerId)
    {
        return '/admin/timebased-currency-payments/my-payment-details-manufacturer/'.$customerId;
    }

    public function getTimebasedCurrencyPaymentDetailsForSuperadmins($manufacturerId, $customerId)
    {
        $url = '/admin/timebased-currency-payments/payment-details-superadmin/' . $customerId;
        if (!is_null($manufacturerId)){
            $url .= '?manufacturerId='.$manufacturerId;
        }
        return $url;
    }

    public function getTimebasedCurrencyPaymentEdit($paymentId)
    {
        return '/admin/timebased-currency-payments/edit/'.$paymentId;
    }
    public function getTimebasedCurrencyPaymentAdd($customerId)
    {
        return '/admin/timebased-currency-payments/add/' . $customerId;
    }

    public function getMyMemberFeeBalance()
    {
        return '/admin/payments/myMemberFee';
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

    public function getInvoices()
    {
        return '/admin/invoices';
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
}
