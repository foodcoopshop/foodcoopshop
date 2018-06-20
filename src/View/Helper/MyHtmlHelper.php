<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\View\View;
use Cake\View\Helper\HtmlHelper;
use App\Controller\Component\StringComponent;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since FoodCoopShop 1.0.0
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @author Mario Rothauer <office@foodcoopshop.com>
 * @copyright Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link https://www.foodcoopshop.com
 */
class MyHtmlHelper extends HtmlHelper
{
    
    public function __construct(View $View, array $config = [])
    {
        $this->_defaultConfig['templates']['javascriptblock'] = "{{content}}";
        $this->helpers[] = 'MyNumber';
        parent::__construct($View, $config);
    }
    
    function wrapJavascriptBlock($content) {
        return "<script>
            //<![CDATA[
                $(document).ready(function() {
                    ".$content."
                });
            //]]>
        </script>";
    }
    
    public function getCurrencyName($currencySymbol)
    {
        switch($currencySymbol) {
            case '€':
                return 'Euro';
                break;
            case '$':
                return 'Dollar';
                break;
            default:
                return '';
                break;
        }
    }
    
    public function getDocsUrl($page)
    {
        $languageCode = substr(I18n::getLocale(), 0, 2);
        $url = 'https://foodcoopshop.github.io/' . $languageCode . '/' . $page;
        return $url;
    }
    
    public function getNameRespectingIsDeleted($customer)
    {
        if (empty($customer)) {
            return self::getDeletedCustomerName();
        }
        return $customer->name;
    }
    
    public function getDeletedCustomerName()
    {
        return 'Gelöschtes Mitglied';
    }
    
    public function getDeletedCustomerEmail()
    {
        return 'Gelöschte E-Mail-Adresse';
    }
    
    /**
     * converts eg. months with only one digit with leading zero
     * @param int $number
     * @param int $maxDigits
     * @return int eg. 1 => 01 / 10 => 10
     */
    public function addLeadingZero($number, $maxDigits = 2)
    {
        return sprintf('%0'.$maxDigits.'d', $number);
    }

    public function getManufacturerHolidayString($dateFrom, $dateTo, $isHolidayActive, $long = false, $name = '')
    {

        if (!is_null($dateFrom)) {
            $dateFrom = $dateFrom->i18nFormat(Configure::read('DateFormat.Database'));
        }
        if (!is_null($dateTo)) {
            $dateTo = $dateTo->i18nFormat(Configure::read('DateFormat.Database'));
        }

        $result = '';

        // both from and to date not set
        if (Configure::read('app.timeHelper')->isDatabaseDateNotSet($dateTo) && Configure::read('app.timeHelper')->isDatabaseDateNotSet($dateFrom)) {
            return $result;
        }

        // holiday over?
        if (!Configure::read('app.timeHelper')->isDatabaseDateNotSet($dateTo) && $dateTo < date('Y-m-d')) {
            return $result;
        }

        if ($long) {
            $result .= 'Der Hersteller <b>' . $name . '</b> hat ';
        }
        if (!Configure::read('app.timeHelper')->isDatabaseDateNotSet($dateFrom)) {
            if ($isHolidayActive) {
                $result .= 'seit';
            } else {
                $result .= 'von';
            }
            $result .= ' ' . Configure::read('app.timeHelper')->formatToDateShort($dateFrom);
        }
        if (!Configure::read('app.timeHelper')->isDatabaseDateNotSet($dateTo)) {
            $result .= ' bis ' . Configure::read('app.timeHelper')->formatToDateShort($dateTo);
        }
        if ($long && $result != '') {
            $result .= ' Lieferpause.';
        }

        $result = str_replace('  ', ' ', $result);

        return $result;
    }
    
    public function getCustomerAddress($customer)
    {
        if (empty($customer->address_customer)) {
            return '';
        }
        $details = $customer->address_customer->address1;
        if ($customer->address_customer->address2 != '') {
            $details .= '<br />' . $customer->address_customer->address2;
        }
        $details .= '<br />' . $customer->address_customer->postcode . ' ' . $customer->address_customer->city;
        
        if ($customer->address_customer->phone_mobile != '') {
            $details .= '<br />Tel.: ' . $customer->address_customer->phone_mobile;
        }
        if ($customer->address_customer->phone != '') {
            $details .= '<br />Tel.: ' . $customer->address_customer->phone;
        }
        return $details;
    }

    /**
     * @param array $manufacturer
     * @param string $outputType "pdf" of "html"
     * @return string
     */
    public function getManufacturerImprint($manufacturer, $outputType, $addressOnly)
    {
        $imprintLines = [];
        $imprintLines[] = '<b>'.$manufacturer->name.'</b>';
        if ($manufacturer->name != $manufacturer->address_manufacturer->firstname . ' ' . $manufacturer->address_manufacturer->lastname) {
            $imprintLines[] = $manufacturer->address_manufacturer->firstname . ' ' . $manufacturer->address_manufacturer->lastname;
        }
        $address = $manufacturer->address_manufacturer->address1;
        if ($manufacturer->address_manufacturer->address2 != '') {
            $address .= ' / ' . $manufacturer->address_manufacturer->address2;
        }
        $imprintLines[] = $address;
        if (!($manufacturer->address_manufacturer->postcode == '' || $manufacturer->address_manufacturer->city == '')) {
            $imprintLines[] = @$manufacturer->address_manufacturer->postcode . ' ' . @$manufacturer->address_manufacturer->city;
        }
        if ($manufacturer->address_manufacturer->phone_mobile != '') {
            $imprintLines[] = __('Mobile') . ': ' . $manufacturer->address_manufacturer->phone_mobile;
        }
        if ($manufacturer->address_manufacturer->phone != '') {
            $imprintLines[] = __('Phone') . ': ' . $manufacturer->address_manufacturer->phone;
        }
        $imprintLines[] = __('Email') . ': ' . ($outputType == 'html' ? StringComponent::hideEmail($manufacturer->address_manufacturer->email) : $manufacturer->address_manufacturer->email);

        if (!$addressOnly) {
            if ($manufacturer->homepage != '') {
                $imprintLines[] = __('Website') . ': ' . ($outputType == 'html' ? self::link($manufacturer->homepage, $manufacturer->homepage, ['options' => ['target' => '_blank']]) : $manufacturer->homepage);
            }
            $imprintLines[] = ''; // new line
            if ($manufacturer->uid_number != '') {
                $imprintLines[] = __('VAT_number') . ': ' . $manufacturer->uid_number;
            }

            if ($manufacturer->firmenbuchnummer != '') {
                $imprintLines[] = __('Commercial_register_number') . ': ' . $manufacturer->firmenbuchnummer;
            }
            if ($manufacturer->firmengericht != '') {
                $imprintLines[] = __('Company_court') . ': ' . $manufacturer->firmengericht;
            }
            if ($manufacturer->aufsichtsbehoerde != '') {
                $imprintLines[] = __('Supervisory_authority') .': ' . $manufacturer->aufsichtsbehoerde;
            }
            if ($manufacturer->kammer != '') {
                $imprintLines[] = __('Chamber') . ': ' . $manufacturer->kammer;
            }
        }
        return '<p>'.implode('<br />', $imprintLines).'</p>';
    }

    /**
     * @return string
     */
    public function getAddressFromAddressConfiguration()
    {
        return Configure::read('appDb.FCS_APP_ADDRESS');
    }

    /**
     * @return string
     */
    public function getEmailFromAddressConfiguration()
    {
        return Configure::read('appDb.FCS_APP_EMAIL');
    }

    public function prepareDbTextForPDF($string)
    {
        $string = self::br2nl($string);
        $string = html_entity_decode($string);
        return $string;
    }

    public function getMenuTypes()
    {
        return [
            'header' => 'Header (oben)',
            'footer' => 'Footer (unten)'
        ];
    }

    public function paymentIsCashless()
    {
        return in_array('cashless', Configure::read('app.paymentMethods'));
    }

    public function br2nl($input)
    {
        return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n", "", str_replace("\r", "", htmlspecialchars_decode($input))));
    }

    public function getMenuType($menuTypeId)
    {
        return $this->getMenuTypes()[$menuTypeId];
    }

    public function getAuthDependentGroups($loggedGroupId)
    {
        $groups = $this->getGroups();
        foreach ($groups as $groupId => $groupName) {
            if ($loggedGroupId < $groupId) {
                unset($groups[$groupId]);
            }
        }
        return $groups;
    }

    public function getGroups()
    {
        return [
            CUSTOMER_GROUP_MEMBER => 'Mitglied',
            CUSTOMER_GROUP_ADMIN => 'Admin',
            CUSTOMER_GROUP_SUPERADMIN => 'Superadmin'
        ];
    }

    /**
     * @param string $icon
     * @return string
     */
    public function getFamFamFamPath($icon)
    {
        return '/node_modules/famfamfam-silk/dist/png/'.$icon;
    }

    public function getGroupName($groupId)
    {
        return $this->getGroups()[$groupId];
    }

    public function getCustomerOrderBy()
    {
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            return [
                'Customers.lastname' => 'ASC',
                'Customers.firstname' => 'ASC'
            ];
        } else {
            return [
                'Customers.firstname' => 'ASC',
                'Customers.lastname' => 'ASC'
            ];
        }
    }

    public function getOrderIdFromCartFinishedUrl($url)
    {
        $orderId = explode('/', $url);
        return (int) $orderId[5];
    }

    public function getCustomerNameForSql()
    {
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            return "CONCAT(c.lastname, ' ', c.firstname)";
        } else {
            return "CONCAT(c.firstname, ' ', c.lastname)";
        }
    }

    public function getJqueryUiIcon($icon, $options, $url = '')
    {
        $options['escape'] = [
            true
        ];

        $return = '<ul class="jquery-ui-icon">';
        $return .= '<li class="ui-state-default ui-corner-all">';

        if ($url == '') {
            $return .= $icon;
        } else {
            $return .= self::link($icon, $url, $options);
        }

        $return .= '</li>';
        $return .= '</ul>';

        return $return;
    }

    public function getMemberFeeTextForFrontend($text)
    {
        $explodedText = explode(',', $text);
        $preparedText = [];
        foreach ($explodedText as $t) {
            $explodedDate = explode('-', $t);
            $preparedText[] = Configure::read('app.timeHelper')->getMonthName($explodedDate[1]) . ' ' . $explodedDate[0];
        }
        return implode(', ', $preparedText);
    }

    public function getReportTabs()
    {
        $tabs = [];
        foreach($this->getPaymentTexts() as $key => $paymentText) {
            $tabs[] = [
                'name' => $paymentText,
                'url' => Configure::read('app.slugHelper')->getReport($key),
                'key' => $key
            ];
        }
        $tabs[] = [
            'name' => 'Guthaben- und Pfand-Saldo',
            'url' => Configure::read('app.slugHelper')->getCreditBalanceSum(),
            'key' => 'credit_balance_sum'
        ];
        return $tabs;
    }
    
    public function getPaymentTexts()
    {
        $paymentTexts = [
            'product' => 'Guthaben-Aufladung',
            'payback' => 'Rückzahlung',
            'deposit' => 'Pfand-Rückgabe'
        ];
        if (Configure::read('app.memberFeeEnabled')) {
            $paymentTexts['member_fee'] = 'Mitgliedsbeitrag';
        }
        return $paymentTexts;
    }

    public function getPaymentText($paymentType)
    {
        return $this->getPaymentTexts()[$paymentType];
    }

    public function getSuperadminProductPaymentTexts($appAuth)
    {
        $paymentTexts = [
            'product' => self::getPaymentText('product'),
            'payback' => self::getPaymentText('payback')
        ];
        return $paymentTexts;
    }

    public function getManufacturerDepositPaymentTexts()
    {
        $paymentTexts = [
            'empty_glasses' => __('Empty_glasses'),
            'money' => __('Compensation_payment')
        ];
        return $paymentTexts;
    }

    public function getManufacturerDepositPaymentText($manufacturerDepositPaymentText)
    {
        if (isset($this->getManufacturerDepositPaymentTexts()[$manufacturerDepositPaymentText])) {
            return $this->getManufacturerDepositPaymentTexts()[$manufacturerDepositPaymentText];
        }
        return $manufacturerDepositPaymentText;
    }

    /**
     *
     * @param int $imageId
     * @return eg 4/1/2 for given id 421
     */
    public function getProductImageIdAsPath($imageId)
    {
        preg_match_all('/[0-9]/', $imageId, $imageIdAsArray);
        $imageIdAsPath = implode(DS, $imageIdAsArray[0]);
        return $imageIdAsPath;
    }

    public function getProductThumbsPath($imageIdAsPath)
    {
        return $this->getUploadImageDir() . DS . 'products' . DS . $imageIdAsPath;
    }

    public function getBlogPostThumbsPath()
    {
        return $this->getUploadImageDir() . DS . 'blog_posts';
    }

    public function getManufacturerThumbsPath()
    {
        return $this->getUploadImageDir() . DS . 'manufacturers';
    }

    public function getCategoryThumbsPath()
    {
        return $this->getUploadImageDir() . DS . 'categories';
    }

    public function getSliderThumbsPath()
    {
        return $this->getUploadImageDir() . DS . 'sliders';
    }

    public function getUploadImageDir()
    {
        return substr(WWW_ROOT, 0, - 1) . Configure::read('app.uploadedImagesDir');
    }

    public function getSliderImageSrc($sliderImage)
    {
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'sliders' . DS;
        return $this->prepareAsUrl($urlPrefix . $sliderImage);
    }

    public function getBlogPostImageSrc($blogPostId, $size)
    {
        $thumbsPath = $this->getBlogPostThumbsPath();
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'blog_posts' . DS;

        $imageFilename = $blogPostId . '-' . $size . '-default.jpg';
        if (! file_exists($thumbsPath . DS . $imageFilename)) {
            $imageFilenameAndPath = $urlPrefix . 'no-' . $size . '-default.jpg';
        } else {
            $imageFilenameAndPath = $urlPrefix . $imageFilename;
        }

        return $this->prepareAsUrl($imageFilenameAndPath);
    }

    public function getManufacturerImageSrc($manufacturerId, $size)
    {
        $thumbsPath = $this->getManufacturerThumbsPath();
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'manufacturers' . DS;

        $imageFilename = $manufacturerId . '-' . $size . '_default.jpg';
        if (! file_exists($thumbsPath . DS . $imageFilename)) {
            $imageFilenameAndPath = $urlPrefix . 'de-default-' . $size . '_default.jpg';
        } else {
            $imageFilenameAndPath = $urlPrefix . $imageFilename;
        }

        return $this->prepareAsUrl($imageFilenameAndPath);
    }

    public function getCategoryImageSrc($categoryId)
    {
        $thumbsPath = $this->getCategoryThumbsPath();
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'categories' . DS;

        $imageFilename = $categoryId . '-category_default.jpg';
        if (! file_exists($thumbsPath . DS . $imageFilename)) {
            return false; // do not show any image if image does not exist
        } else {
            $imageFilenameAndPath = $urlPrefix . $imageFilename;
        }

        return $this->prepareAsUrl($imageFilenameAndPath);
    }

    public function getProductImageSrc($imageId, $size)
    {
        $imageIdAsPath = $this->getProductImageIdAsPath($imageId);
        $thumbsPath = $this->getProductThumbsPath($imageIdAsPath);
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'products' . DS;

        $imageFilename = $imageId . '-' . $size . '_default.jpg';
        if (! file_exists($thumbsPath . DS . $imageFilename)) {
            $imageFilenameAndPath = $urlPrefix . 'de-default-' . $size . '_default.jpg';
        } else {
            $imageFilenameAndPath = $urlPrefix . $imageIdAsPath . DS . $imageFilename;
        }

        return $this->prepareAsUrl($imageFilenameAndPath);
    }

    public function prepareAsUrl($string)
    {
        $physicalFile = substr(WWW_ROOT, 0, - 1) . $string;
        if (file_exists($physicalFile)) {
            $string .= '?' . filemtime($physicalFile);
        }
        $string = str_replace(DS, '/', $string);
        return $string;
    }

    public function getOrderListLink($manufacturerName, $manufacturerId, $deliveryDay, $groupTypeLabel)
    {
        $url = Configure::read('app.folder_order_lists_with_current_year_and_month') . DS;
        $url .= $deliveryDay . '_' . StringComponent::slugifyAndKeepCase($manufacturerName) . '_' . $manufacturerId . __('_Order_list_filename_') . $groupTypeLabel . '_' . StringComponent::slugifyAndKeepCase(Configure::read('appDb.FCS_APP_NAME')) . '.pdf';
        return $url;
    }

    public function getInvoiceLink($manufacturerName, $manufacturerId, $invoiceDate, $invoiceNumber)
    {
        $url = Configure::read('app.folder_invoices_with_current_year_and_month') . DS;
        $url .= $invoiceDate . '_' . StringComponent::slugifyAndKeepCase($manufacturerName) . '_' . $manufacturerId . __('_Invoice_filename_') . $invoiceNumber . '_' . StringComponent::slugifyAndKeepCase(Configure::read('appDb.FCS_APP_NAME')) . '.pdf';
        return $url;
    }

    public function getApprovalStates()
    {
        return [
            1 => __('approval_state_ok'),
            0 => __('approval_state_open'),
            -1 => __('approval_state_not_ok'),
        ];
    }

    public function getActiveStates()
    {
        return [
            1 => __('active_state_active'),
            0 => __('active_state_inactive'),
            'all' => __('active_state_all')
        ];
    }

    public function getVisibleOrderStates()
    {
        return Configure::read('app.visibleOrderStates');
    }

    public function getOrderStates()
    {
        $orderStates = self::getVisibleOrderStates();
        $orderStates[ORDER_STATE_CANCELLED] = __('order_state_cancelled');
        return $orderStates;
    }

    public function getOrderStateIds()
    {
        return array_keys(self::getVisibleOrderStates());
    }
}
