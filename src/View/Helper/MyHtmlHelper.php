<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\View;
use Cake\View\Helper\HtmlHelper;

/**
 * MyHtmlHelper
 *
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
        // wrap js block with jquery document ready
        $this->_defaultConfig['templates']['javascriptblock'] =
        "<script{{attrs}}>
            //<![CDATA[
                $(document).ready(function() {
                    {{content}}
                });
            //]]>
        </script>";
        parent::__construct($View, $config);
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
        $result = '';

        // both from and to date not set
        if (Configure::read('AppConfig.timeHelper')->isDatabaseDateNotSet($dateTo) && Configure::read('AppConfig.timeHelper')->isDatabaseDateNotSet($dateFrom)) {
            return $result;
        }

        // holiday over?
        if (!Configure::read('AppConfig.timeHelper')->isDatabaseDateNotSet($dateTo) && $dateTo < date('Y-m-d')) {
            return $result;
        }

        if ($long) {
            $result .= 'Der Hersteller <b>' . $name . '</b> hat ';
        }
        if (!Configure::read('AppConfig.timeHelper')->isDatabaseDateNotSet($dateFrom)) {
            if ($isHolidayActive) {
                $result .= 'seit';
            } else {
                $result .= 'von';
            }
            $result .= ' ' . Configure::read('AppConfig.timeHelper')->formatToDateShort($dateFrom);
        }
        if (!Configure::read('AppConfig.timeHelper')->isDatabaseDateNotSet($dateTo)) {
            $result .= ' bis ' . Configure::read('AppConfig.timeHelper')->formatToDateShort($dateTo);
        }
        if ($long && $result != '') {
            $result .= ' Lieferpause.';
        }

        $result = str_replace('  ', ' ', $result);

        return $result;
    }

    /**
     * @param array $manufacturer
     * @param string $outputType "pdf" of "html"
     * @return string
     */
    public function getManufacturerImprint($manufacturer, $outputType, $addressOnly)
    {
        if (!isset($manufacturer['Manufacturers'])) {
            $manufacturer['Manufacturers'] = $manufacturer;
        }
        $imprintLines = [];
        $imprintLines[] = '<b>'.$manufacturer['Manufacturers']['name'].'</b>';
        if ($manufacturer['Manufacturers']['name'] != $manufacturer['Addresses']['firstname'] . ' ' . $manufacturer['Addresses']['lastname']) {
            $imprintLines[] = $manufacturer['Addresses']['firstname'] . ' ' . $manufacturer['Addresses']['lastname'];
        }
        $address = $manufacturer['Addresses']['address1'];
        if ($manufacturer['Addresses']['address2'] != '') {
            $address .= ' / ' . $manufacturer['Addresses']['address2'];
        }
        $imprintLines[] = $address;
        if (!($manufacturer['Addresses']['postcode'] == '' || $manufacturer['Addresses']['city'] == '')) {
            $imprintLines[] = @$manufacturer['Addresses']['postcode'] . ' ' . @$manufacturer['Addresses']['city'];
        }
        if ($manufacturer['Addresses']['phone_mobile'] != '') {
            $imprintLines[] = 'Mobil: ' . $manufacturer['Addresses']['phone_mobile'];
        }
        if ($manufacturer['Addresses']['phone'] != '') {
            $imprintLines[] = 'Telefon: ' . $manufacturer['Addresses']['phone'];
        }
        $imprintLines[] = 'E-Mail: ' . ($outputType == 'html' ? StringComponent::hideEmail($manufacturer['Addresses']['email']) : $manufacturer['Addresses']['email']);

        if (!$addressOnly) {
            if ($manufacturer['Manufacturers']['homepage'] != '') {
                $imprintLines[] = 'Homepage: ' . ($outputType == 'html' ? self::link($manufacturer['Manufacturers']['homepage'], $manufacturer['Manufacturers']['homepage'], ['options' => ['target' => '_blank']]) : $manufacturer['Manufacturers']['homepage']);
            }
            $imprintLines[] = ''; // new line
            if ($manufacturer['Manufacturers']['uid_number'] != '') {
                $imprintLines[] = 'UID-Nummer: ' . $manufacturer['Manufacturers']['uid_number'];
            }

            if ($manufacturer['Manufacturers']['firmenbuchnummer'] != '') {
                $imprintLines[] = 'Firmenbuchnummer: ' . $manufacturer['Manufacturers']['firmenbuchnummer'];
            }
            if ($manufacturer['Manufacturers']['firmengericht'] != '') {
                $imprintLines[] = 'Firmengericht: ' . $manufacturer['Manufacturers']['firmengericht'];
            }
            if ($manufacturer['Manufacturers']['aufsichtsbehoerde'] != '') {
                $imprintLines[] = 'Aufsichtsbehörde: ' . $manufacturer['Manufacturers']['aufsichtsbehoerde'];
            }
            if ($manufacturer['Manufacturers']['kammer'] != '') {
                $imprintLines[] = 'Kammer: ' . $manufacturer['Manufacturers']['kammer'];
            }
        }
        return '<p>'.implode('<br />', $imprintLines).'</p>';
    }

    /**
     * @return string
     */
    public function getAddressFromAddressConfiguration()
    {
        return Configure::read('AppConfig.db_config_FCS_APP_ADDRESS');
    }

    /**
     * @return string
     */
    public function getEmailFromAddressConfiguration()
    {
        return Configure::read('AppConfig.db_config_FCS_APP_EMAIL');
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
        return in_array('cashless', Configure::read('AppConfig.paymentMethods'));
    }

    public function br2nl($input)
    {
        return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n", "", str_replace("\r", "", htmlspecialchars_decode($input))));
    }

    public function getConfigurationDropdownOptions($name)
    {
        switch ($name) {
            case 'FCS_CART_ENABLED':
            case 'FCS_SHOW_PRODUCTS_FOR_GUESTS':
            case 'FCS_DEFAULT_NEW_MEMBER_ACTIVE':
            case 'FCS_SHOW_FOODCOOPSHOP_BACKLINK':
            case 'FCS_ORDER_COMMENT_ENABLED':
                return [
                    APP_ON => 'ja',
                    APP_OFF => 'nein'
                ];
                break;
            case 'FCS_SHOP_ORDER_DEFAULT_STATE':
                return self::getVisibleOrderStates();
                break;
            case 'FCS_CUSTOMER_GROUP':
                return array_slice($this->getGroups(), 0, 2, true); // true: preserveKeys
                break;
        }
    }

    public function getConfigurationDropdownOption($name, $value)
    {
        return self::getConfigurationDropdownOptions($name)[$value];
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

    public function formatAsEuro($amount)
    {
        return '€&nbsp;' . self::formatAsDecimal($amount);
    }

    public function formatAsPercent($amount)
    {
        return self::formatAsDecimal($amount) . '%';
    }

    /**
     * shows decimals only if necessary
     *
     * @param decimal $amount
     */
    public function formatTaxRate($rate)
    {
        return $rate != intval($rate) ? self::formatAsDecimal($rate, 1) : self::formatAsDecimal($rate, 0);
    }

    public function formatAsDecimal($amount, $decimals = 2)
    {
        return number_format($amount, $decimals, ',', '.');
    }

    public function getCustomerOrderBy()
    {
        if (Configure::read('AppConfig.customerMainNamePart') == 'lastname') {
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
        if (Configure::read('AppConfig.customerMainNamePart') == 'lastname') {
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
            $preparedText[] = Configure::read('AppConfig.timeHelper')->getMonthName($explodedDate[1]) . ' ' . $explodedDate[0];
        }
        return implode(', ', $preparedText);
    }

    public function getPaymentTexts()
    {
        $paymentTexts = [
            'product' => 'Guthaben-Aufladung',
            'payback' => 'Rückzahlung',
            'deposit' => 'Pfand-Rückgabe'
        ];
        if (Configure::read('AppConfig.memberFeeEnabled')) {
            $paymentTexts['member_fee'] = 'Mitgliedsbeitrag';
        }
        if (Configure::read('AppConfig.memberFeeFlexibleEnabled')) {
            $paymentTexts['member_fee_flexible'] = 'Flexibler Mitgliedsbeitrag';
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
            'empty_glasses' => 'Leergebinde',
            'money' => 'Ausgleichszahlung'
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
        return substr(WWW_ROOT, 0, - 1) . Configure::read('AppConfig.uploadedImagesDir');
    }

    public function getSliderImageSrc($sliderImage)
    {
        $urlPrefix = Configure::read('AppConfig.uploadedImagesDir') . DS . 'sliders' . DS;
        return $this->prepareAsUrl($urlPrefix . $sliderImage);
    }

    public function getBlogPostImageSrc($blogPostId, $size)
    {
        $thumbsPath = $this->getBlogPostThumbsPath();
        $urlPrefix = Configure::read('AppConfig.uploadedImagesDir') . DS . 'blog_posts' . DS;

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
        $urlPrefix = Configure::read('AppConfig.uploadedImagesDir') . DS . 'manufacturers' . DS;

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
        $urlPrefix = Configure::read('AppConfig.uploadedImagesDir') . DS . 'categories' . DS;

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
        $urlPrefix = Configure::read('AppConfig.uploadedImagesDir') . DS . 'products' . DS;

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

    public function getOrderListLink($manufacturerName, $manufacturerId, $deliveryDay, $groupType_de)
    {
        $url = Configure::read('AppConfig.folder.order_lists_with_current_year_and_month') . DS;
        $url .= $deliveryDay . '_' . Inflector::slug($manufacturerName) . '_' . $manufacturerId . '_Bestellliste_' . $groupType_de . '_' . Inflector::slug(Configure::read('AppConfig.db_config_FCS_APP_NAME')) . '.pdf';
        return $url;
    }

    public function getInvoiceLink($manufacturerName, $manufacturerId, $invoiceDate, $invoiceNumber)
    {
        $url = Configure::read('AppConfig.folder.invoices_with_current_year_and_month') . DS;
        $url .= $invoiceDate . '_' . Inflector::slug($manufacturerName) . '_' . $manufacturerId . '_Rechnung_' . $invoiceNumber . '_' . Inflector::slug(Configure::read('AppConfig.db_config_FCS_APP_NAME')) . '.pdf';
        return $url;
    }

    // gehört in time helper
    public function convertToGermanDate($date)
    {
        return date('d.m.Y', strtotime(str_replace('/', '-', $date)));
    }

    public function getApprovalStates()
    {
        return [
            1 => 'bestätigt',
            0 => 'offen',
            -1 => 'da stimmt was nicht...'
        ];
    }

    public function getActiveStates()
    {
        return [
            1 => 'aktiviert',
            0 => 'deaktiviert',
            'all' => 'alle'
        ];
    }

    public function getVisibleOrderStates()
    {
        return Configure::read('AppConfig.visibleOrderStates');
    }

    public function getOrderStates()
    {
        $orderStates = self::getVisibleOrderStates();
        $orderStates[ORDER_STATE_CANCELLED] = 'storniert';
        return $orderStates;
    }

    public function getOrderStateIds()
    {
        return array_keys(self::getVisibleOrderStates());
    }

    public function getOrderStateIdsAsCsv()
    {
        return join(',', self::getOrderStateIds());
    }

    /*
    public function __construct(View $View, array $config = [])
    {

        // wrap js block with jquery document ready
        $this->_tags['javascriptblock'] = "<script%s>
              //<![CDATA[
              $(document).ready(function() {
                  %s
              });
              //]]>
          </script>";

        parent::__construct($View, $config);
    }
    */
}
