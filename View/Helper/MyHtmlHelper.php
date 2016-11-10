<?php
App::uses('HtmlHelper', 'View/Helper');

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

    /**
     * converts eg. months with only one digit with leading zero
     * @param int $number
     * @param int $maxDigits
     * @return int eg. 1 => 01 / 10 => 10
     */
    public function addLeadingZero($number, $maxDigits=2)
    {
        return sprintf('%0'.$maxDigits.'d', $number);
    }

    public function getMenuTypes()
    {
        return array(
            'header' => 'Header (oben)',
            'footer' => 'Footer (unten)'
        );
    }

    public function paymentIsCashless()
    {
        return in_array('cashless', Configure::read('app.paymentMethods'));
    }

    public function getConfigurationDropdownOptions($name)
    {
        switch ($name) {
            case 'FCS_CART_ENABLED':
            case 'FCS_SHOW_PRODUCTS_FOR_GUESTS':
            case 'FCS_DEFAULT_NEW_MEMBER_ACTIVE':
            case 'FCS_SHOW_FOODCOOPSHOP_BACKLINK':
                return array(
                    APP_ON => 'ja',
                    APP_OFF => 'nein'
                );
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

    function getAuthDependentGroups($loggedGroupId)
    {
        $groups = $this->getGroups();
        foreach ($groups as $groupId => $groupName) {
            if ($loggedGroupId < $groupId) {
                unset($groups[$groupId]);
            }
        }
        return $groups;
    }

    function getGroups()
    {
        return array(
            CUSTOMER_GROUP_MEMBER => 'Mitglied',
            CUSTOMER_GROUP_ADMIN => 'Admin',
            CUSTOMER_GROUP_SUPERADMIN => 'Superadmin'
        );
    }

    /**
     * @param string $icon
     * @return string
     */
    public function getFamFamFamPath($icon)
    {
        return '/js/vendor/famfamfam-silk/dist/png/'.$icon;
    }
    
    function getGroupName($groupId)
    {
        return $this->getGroups()[$groupId];
    }

    function formatAsEuro($amount)
    {
        return '€&nbsp;' . self::formatAsDecimal($amount);
    }

    function formatAsPercent($amount)
    {
        return self::formatAsDecimal($amount) . '%';
    }

    /**
     * shows decimals only if necessary
     * 
     * @param decimal $amount            
     */
    function formatTaxRate($rate)
    {
        return $rate != intval($rate) ? self::formatAsDecimal($rate, 1) : self::formatAsDecimal($rate, 0);
    }

    function formatAsDecimal($amount, $decimals = 2)
    {
        return number_format($amount, $decimals, ',', '.');
    }

    function getCustomerOrderBy()
    {
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            return array(
                'Customer.lastname' => 'ASC',
                'Customer.firstname' => 'ASC'
            );
        } else {
            return array(
                'Customer.firstname' => 'ASC',
                'Customer.lastname' => 'ASC'
            );
        }
    }

    function getOrderIdFromCartFinishedUrl($url)
    {
        $orderId = explode('/', $url);
        return (int) $orderId[5];
    }

    function getCustomerNameForSql()
    {
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            return "CONCAT(c.lastname, ' ', c.firstname)";
        } else {
            return "CONCAT(c.firstname, ' ', c.lastname)";
        }
    }

    function getJqueryUiIcon($icon, $options, $url = '')
    {
        $options['escape'] = array(
            true
        );
        
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

    function getMemberFeeTextForFrontend($text)
    {
        $explodedText = explode(',', $text);
        $preparedText = array();
        foreach ($explodedText as $t) {
            $explodedDate = explode('-', $t);
            $preparedText[] = Configure::read('timeHelper')->getMonthName($explodedDate[1]) . ' ' . $explodedDate[0];
        }
        return implode(', ', $preparedText);
    }

    function getPaymentTexts()
    {
        $paymentTexts = array(
            'product' => 'Guthaben-Aufladung',
            'deposit' => 'Pfand-Rückgabe'
        );
        if (Configure::read('app.memberFeeEnabled')) {
            $paymentTexts['member_fee'] = 'Mitgliedsbeitrag';
        }
        if (Configure::read('app.memberFeeFlexibleEnabled')) {
            $paymentTexts['member_fee_flexible'] = 'Flexibler Mitgliedsbeitrag';
        }
        return $paymentTexts;
    }

    function getPaymentText($paymentType)
    {
        return $this->getPaymentTexts()[$paymentType];
    }
    
    public function getManufacturerDepositPaymentTexts() {
        $paymentTexts = array(
            'empty_glasses' => 'Leergebinde',
            'money' => 'Überweisung'
        );
        return $paymentTexts;
    }

    function getManufacturerDepositPaymentText($manufacturerDepositPaymentText)
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
        $imagePath = $this->getUploadImageDir();
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
        $imagePath = $this->getUploadImageDir();
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
        $imagePath = $this->getUploadImageDir();
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

    public function getProductImageSrc($imageId, $legend, $size)
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
        return str_replace(DS, '/', $string);
    }

    public function getOrderListLink($manufacturerName, $manufacturerId, $deliveryDay, $groupType_de)
    {
        $url = Configure::read('app.folder.order_lists_with_current_year_and_month') . DS;
        $url .= $deliveryDay . '_' . Inflector::slug($manufacturerName) . '_' . $manufacturerId . '_Bestellliste_' . $groupType_de . '_' . Inflector::slug(Configure::read('app.name')) . '.pdf';
        return $url;
    }

    public function getInvoiceLink($manufacturerName, $manufacturerId, $invoiceDate, $invoiceNumber)
    {
        $url = Configure::read('app.folder.invoices_with_current_year_and_month') . DS;
        $url .= $invoiceDate . '_' . Inflector::slug($manufacturerName) . '_' . $manufacturerId . '_Rechnung_' . $invoiceNumber . '_' . Inflector::slug(Configure::read('app.name')) . '.pdf';
        return $url;
    }
    
    // gehört in time helper
    public function convertToGermanDate($date)
    {
        return date('d.m.Y', strtotime(str_replace('/', '-', $date)));
    }

    public function getActiveStates()
    {
        return array(
            1 => 'aktiviert',
            0 => 'deaktiviert',
            'all' => 'alle'
        );
    }

    public function getVisibleOrderStates()
    {
        return Configure::read('app.visibleOrderStates');
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

    public function __construct(View $View, $settings = array())
    {
        
        // wrap js block with jquery document ready
        $this->_tags['javascriptblock'] = "<script%s>
              //<![CDATA[
              $(document).ready(function() {
                  %s
              });
              //]]>
          </script>";
        
        parent::__construct($View, $settings);
    }
}
