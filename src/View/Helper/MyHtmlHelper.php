<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\Utility\Text;
use Cake\View\View;
use Cake\View\Helper\HtmlHelper;
use App\Controller\Component\StringComponent;
use App\Services\DeliveryRhythmService;
use App\Services\OutputFilter\OutputFilterService;
use App\Services\OrderCustomerService;
use App\Model\Entity\Customer;
use App\Model\Entity\Cart;
use App\Model\Entity\OrderDetail;
use App\Model\Entity\Payment;
use Cake\I18n\I18n;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since FoodCoopShop 1.0.0
 * @license https://opensource.org/licenses/AGPL-3.0
 * @author Mario Rothauer <office@foodcoopshop.com>
 * @copyright Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link https://www.foodcoopshop.com
 */
class MyHtmlHelper extends HtmlHelper
{

    public function __construct(View $View, array $config = [])
    {
        $this->_defaultConfig['templates']['javascriptblock'] = "{{content}}";
        $this->helpers[] = 'MyNumber';
        $this->helpers[] = 'MyTime';
        parent::__construct($View, $config);
    }

    public function getCartTypes(): array
    {
        $cartTypes = [
            Cart::TYPE_WEEKLY_RHYTHM => __('cart_type_weekly_rhythm'),
            Cart::TYPE_INSTANT_ORDER => __('cart_type_instant_order'),
        ];
        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $cartTypes[Cart::TYPE_SELF_SERVICE] = __('cart_type_self_service');
        }
        return $cartTypes;
    }

    public function getHostWithoutProtocol($hostnameWithProtocol): string|false
    {
        $parsedHostnameWithProtocol = (parse_url($hostnameWithProtocol));
        if (!empty($parsedHostnameWithProtocol['host'])) {
            return $parsedHostnameWithProtocol['host'];
        }
        return false;
    }

    public function buildElementProductCacheKey($product, $identity): string
    {
        $orderCustomerService = new OrderCustomerService();
        $elementCacheKey = join('_', [
            'product',
            'productId' => $product['id_product'],
            'isLoggedIn-' . ($identity !== null ? 0 : 1),
            'isManufacturer-' . ($identity !== null && $identity->isManufacturer() ? 1 : 0),
            'isSuperadmin-' . ($identity !== null && $identity->isSuperadmin() ? 1 : 0),
            'isSelfServiceModeByUrl-' . ($orderCustomerService->isSelfServiceModeByUrl() ? 1 : 0),
            'isOrderForDifferentCustomerMode-' . ($orderCustomerService->isOrderForDifferentCustomerMode() ? 1 : 0),
            ($identity != null ? $identity->shopping_price : Customer::SELLING_PRICE),
            'date-' . date('Y-m-d'),
        ]);
        return $elementCacheKey;
    }

    public function getShoppingPricesForDropdown(): array
    {
        $options = [];
        $options[Customer::SELLING_PRICE] = __('Shopping_with_selling_price');
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $options[Customer::PURCHASE_PRICE] = __('Shopping_with_purchase_price');
        }
        $options[Customer::ZERO_PRICE] = __('Shopping_with_zero_price');
        return $options;
    }

    public function getLegalTextsSubfolder(): string
    {
        $subfolder = 'directSelling';
        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            $subfolder = 'retail';
        }
        return $subfolder;
    }

    public function getPlatformOwnerForLegalTexts(): string
    {
        $result = '';
        if (Configure::read('appDb.FCS_PLATFORM_OWNER') != '') {
            $result .= Configure::read('appDb.FCS_PLATFORM_OWNER');
        } else {
            $result .= Configure::read('appDb.FCS_APP_NAME');
            $result .= '<br />'.$this->getAddressFromAddressConfiguration();
            if (Configure::read('appDb.FCS_APP_ADDITIONAL_DATA') != '') {
                $result .= '<br />' . Configure::read('appDb.FCS_APP_ADDITIONAL_DATA');
            }
        }
        return $result;
    }

    public function removeTimestampFromFile($file): string
    {
        $file = explode('?', $file);
        return $file[0];
    }

    public function privateImage($imageSrc): string
    {
        return '/photos/' . $imageSrc;
    }

    public function isStockProductOrderPossible($instantOrderMode, $isSelfServiceMode, $includeStockProductsInOrdersWithDeliveryRhythm, $stockManagementEnabled, $isStockProduct): bool
    {
        return (!$instantOrderMode && !$includeStockProductsInOrdersWithDeliveryRhythm && $stockManagementEnabled && $isStockProduct) && !$isSelfServiceMode;
    }

    public function getDeliveryRhythmString($isStockProduct, $deliveryRhythmType, $deliveryRhythmCount): string
    {

        $deliveryRhythmCount = (int) $deliveryRhythmCount;
        $deliveryRhythmString = '';

        if ($isStockProduct) {
            $deliveryRhythmType = 'week';
            $deliveryRhythmCount = 1;
        }

        if ($deliveryRhythmType == 'week') {
            if ($deliveryRhythmCount == 1) {
                $deliveryRhythmString = __('weekly');
            }
            if ($deliveryRhythmCount > 1) {
                $deliveryRhythmString = __('every_{0}_week', [$this->MyNumber->ordinal($deliveryRhythmCount)]);
            }
        }

        if ($deliveryRhythmType == 'month') {
            $deliveryDayAsWeekday = $this->MyTime->getWeekdayName((new DeliveryRhythmService())->getDeliveryWeekday());
            if ($deliveryRhythmCount > 0) {
                $deliveryRhythmString = __('every_{0}_{1}_of_a_month', [
                    $this->MyNumber->ordinal($deliveryRhythmCount),
                    $deliveryDayAsWeekday
                ]);
            } else {
                $deliveryRhythmString = __('every_last_{0}_of_a_month', [
                    $deliveryDayAsWeekday
                ]);
            }
        }

        if ($deliveryRhythmType == 'individual') {
            $deliveryRhythmString = __('bulk_order');
        }

        return $deliveryRhythmString;
    }

    public function getSendOrderListsWeekdayOptions(): array
    {
        $defaultSendOrderListsWeekday = (new DeliveryRhythmService())->getSendOrderListsWeekday();
        $weekday3 = $this->MyTime->getNthWeekdayBeforeWeekday(3, $defaultSendOrderListsWeekday);
        $weekday2 = $this->MyTime->getNthWeekdayBeforeWeekday(2, $defaultSendOrderListsWeekday);
        $weekday1 = $this->MyTime->getNthWeekdayBeforeWeekday(1, $defaultSendOrderListsWeekday);
        return [
            $weekday3 => $this->MyTime->getWeekdayName($weekday3) . ' ' . __('midnight'),
            $weekday2 => $this->MyTime->getWeekdayName($weekday2) . ' ' . __('midnight'),
            $weekday1 => $this->MyTime->getWeekdayName($weekday1) . ' ' . __('midnight') . ' (' . __('default_value') . ')'
        ];
    }

    public function getDeliveryRhythmTypesForDropdown(): array
    {
        return [
            '1-week' => $this->getDeliveryRhythmString(false, 'week', 1),
            '2-week' => $this->getDeliveryRhythmString(false, 'week', 2),
            '3-week' => $this->getDeliveryRhythmString(false, 'week', 3),
            '4-week' => $this->getDeliveryRhythmString(false, 'week', 4),
            '1-month' => $this->getDeliveryRhythmString(false, 'month', 1),
            '2-month' => $this->getDeliveryRhythmString(false, 'month', 2),
            '3-month' => $this->getDeliveryRhythmString(false, 'month', 3),
            '4-month' => $this->getDeliveryRhythmString(false, 'month', 4),
            '0-month' => $this->getDeliveryRhythmString(false, 'month', 0),
            '0-individual' => $this->getDeliveryRhythmString(false, 'individual', 0)
        ];
    }

    public function getOrderStateFontawesomeIcon($orderState): string
    {
        return match($orderState) {
            OrderDetail::STATE_OPEN => 'fas fa-cart-arrow-down ok',
            OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER => 'far fa-envelope ok',
            OrderDetail::STATE_BILLED_CASHLESS, OrderDetail::STATE_BILLED_CASH => 'fa fa-lock not-ok',
            default => '',
        };
    }

    public function wrapJavascriptBlock($content): string
    {
        return "<script>
            //<![CDATA[
                $(document).ready(function() {
                    ".$content."
                });
            //]]>
        </script>";
    }

    public function getYesNo($value): string
    {
        return $this->getYesNoArray()[$value];
    }

    public function getYesNoArray(): array
    {
        return [
            APP_ON => __('yes'),
            APP_OFF => __('no')
        ];
    }

    public function getCurrencyName($currencySymbol): string
    {
        return match($currencySymbol) {
            '€' => 'Euro',
            '$' => 'Dollar',
            default => '',
        };
    }

    public function getCurrencyIsoCode($currencySymbol): string
    {
        return match($currencySymbol) {
            '€' => 'EUR',
            '$' => 'USD',
            default => '',
        };
    }

    public function getFontAwesomeIconForCurrencyName(): string
    {
        $currencyIcon = 'fas fa-fw ok fa-'.strtolower(Configure::read('app.currencyName')).'-sign';
        if (Configure::read('app.currencyName') == '') {
            $currencyIcon = 'fa-fw ok far fa-money-bill-alt';
        }
        return $currencyIcon;
    }

    public function getDocsUrl(string $page): string
    {
        $docsUrl = 'https://foodcoopshop.github.io/';
        if (I18n::getLocale() == 'de_DE') {
            return $docsUrl . $page;
        }
        return $docsUrl;
    }

    public function getNameRespectingIsDeleted($customer): string
    {
        if (empty($customer)) {
            return self::getDeletedCustomerName();
        }
        return $customer->name;
    }

    public function getDeletedCustomerName(): string
    {
        return __('Deleted_Member');
    }

    public function getDeletedCustomerEmail(): string
    {
        return __('Deleted_Email_Address');
    }

    public function anonymizeCustomerName(string $name, int $id): string
    {
        $words = explode(' ', $name);
        $pieces = [];
        foreach ($words as $w) {
            $pieces[] = mb_substr($w, 0, 1);
        }
        $anonymizedCustomerName = join('.', $pieces) . '. - ID ' . $id;
        return $anonymizedCustomerName;
    }

    public function addLeadingZero($number, $maxDigits = 2): string
    {
        return sprintf('%0'.$maxDigits.'d', $number);
    }

    public function getManufacturerNoDeliveryDaysString($manufacturer, bool $long = false, int $maxCount = null): string
    {

        $result = '';
        if ($manufacturer->no_delivery_days == '') {
            return $result;
        }

        $formattedAndCleanedDeliveryDays = $this->getFormattedAndCleanedDeliveryDays($manufacturer->no_delivery_days);

        $formattedAndCleanedDeliveryDaysCount = count($formattedAndCleanedDeliveryDays);
        if ($maxCount !== null) {
            if ($formattedAndCleanedDeliveryDaysCount > $maxCount) {
                $furtherDeliveryBreaksCount = $formattedAndCleanedDeliveryDaysCount - $maxCount;
                // show extra info if there are more than 2 further delivery breaks (result would be even longer)
                if ($furtherDeliveryBreaksCount > 2) {
                    $formattedAndCleanedDeliveryDays = array_slice($formattedAndCleanedDeliveryDays, 0, $maxCount);
                    $formattedAndCleanedDeliveryDays[] = __('{0}_further_delivery_breaks', [
                        $furtherDeliveryBreaksCount
                    ]);
                }
            }
        }

        if (empty($formattedAndCleanedDeliveryDays)) {
            return $result;
        }

        $csvNoDeliveryDays = Text::toList($formattedAndCleanedDeliveryDays);

        if (!$long) {
            return $csvNoDeliveryDays;
        }

        $result = __('The_manufacturer_{0}_takes_a_break_on_{1}.', [
            '<b>' . $manufacturer->name . '</b>',
            '<b>' . $csvNoDeliveryDays . '</b>'
        ]);

        return $result;

    }

    public function getGlobalNoDeliveryDaysString(): string
    {

        $result = '';
        if (Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL') == '') {
            return $result;
        }

        $formattedAndCleanedDeliveryDays = $this->getFormattedAndCleanedDeliveryDays(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'));
        if (empty($formattedAndCleanedDeliveryDays)) {
            return $result;
        }

        $result = __('{0}_takes_a_break_on_{1}.', [
            Configure::read('appDb.FCS_APP_NAME'),
            '<b>' . Text::toList($formattedAndCleanedDeliveryDays) . '</b>'
        ]);

        return $result;
    }

    public function getFormattedAndCleanedDeliveryDays($deliveryDays): array
    {
        $explodedNoDeliveryDays = explode(',', $deliveryDays);
        $formattedAndCleanedDeliveryDays = [];
        foreach($explodedNoDeliveryDays as $noDeliveryDay) {
            if (date('Y-m-d') <= $noDeliveryDay) {
                $formattedAndCleanedDeliveryDays[] = $this->MyTime->formatToDateShort($noDeliveryDay);
            }
        }
        return $formattedAndCleanedDeliveryDays;
    }

    public function getGlobalNoDeliveryDaysAsArray(): array
    {
        $result = [];
        if (Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL') != '') {
            $result = explode(',', Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'));
        }
        return $result;
    }

    public function getCustomerAddress($customer): string
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
            $details .= '<br />Tel.: <a href="tel:'.$customer->address_customer->phone_mobile.'">' . $customer->address_customer->phone_mobile . '</a>';
        }
        if ($customer->address_customer->phone != '') {
            $details .= '<br />Tel.: <a href="tel:'.$customer->address_customer->phone.'">' . $customer->address_customer->phone . '</a>';
        }
        return $details;
    }

    public function getManufacturerImprint($manufacturer, $outputType, $addressOnly): string
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
            $imprintLines[] = $manufacturer->address_manufacturer->postcode . ' ' . $manufacturer->address_manufacturer->city;
        }
        if ($manufacturer->address_manufacturer->phone_mobile != '') {
            if ($outputType == 'html') {
                $imprintLines[] = __('Mobile') . ': <a href="tel:' . $manufacturer->address_manufacturer->phone_mobile . '">' . $manufacturer->address_manufacturer->phone_mobile . '</a>';
            } else {
                $imprintLines[] = __('Mobile') . ': ' . $manufacturer->address_manufacturer->phone_mobile;
            }
        }
        if ($manufacturer->address_manufacturer->phone != '') {
            if ($outputType == 'html') {
                $imprintLines[] = __('Phone') . ': <a href="tel:' . $manufacturer->address_manufacturer->phone . '">' . $manufacturer->address_manufacturer->phone . '</a>';
            } else {
                $imprintLines[] = __('Phone') . ': ' . $manufacturer->address_manufacturer->phone;
            }
        }
        $imprintLines[] = __('Email') . ': ' . $manufacturer->address_manufacturer->email;

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

    public function getAddressFromAddressConfiguration(): string
    {
        return Configure::read('appDb.FCS_APP_ADDRESS');
    }

    public function getMenuTypes(): array
    {
        return [
            'header' => __('Header_(top)'),
            'footer' => __('Footer_(bottom)'),
        ];
    }

    public function getOrderStateBilled(): int
    {
        $billedOrderState = OrderDetail::STATE_BILLED_CASH;
        if ($this->paymentIsCashless()) {
            $billedOrderState = OrderDetail::STATE_BILLED_CASHLESS;
        }
        return $billedOrderState;
    }

    public function paymentIsCashless(): bool
    {
        return in_array('cashless', Configure::read('app.paymentMethods'));
    }

    public function br2nl($input): string
    {
        return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n", "", str_replace("\r", "", htmlspecialchars_decode($input))));
    }

    public function getMenuType($menuTypeId): string
    {
        return $this->getMenuTypes()[$menuTypeId];
    }

    public function getAuthDependentGroups($loggedGroupId): array
    {
        $groups = $this->getGroups();
        foreach ($groups as $groupId => $groupName) {
            if ($loggedGroupId < $groupId) {
                unset($groups[$groupId]);
            }
        }
        return $groups;
    }

    public function getGroups(): array
    {
        $groups = [];
        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            $groups[Customer::GROUP_SELF_SERVICE_CUSTOMER] = __('Self_service_customer');
        }
        $groups[Customer::GROUP_MEMBER] = __('Member');
        $groups[Customer::GROUP_ADMIN] = __('Admin');
        $groups[Customer::GROUP_SUPERADMIN] = __('Superadmin');
        return $groups;
    }

    public function getGroupName($groupId): string
    {
        return $this->getGroups()[$groupId];
    }

    public function getCartIdFromCartFinishedUrl($url): int
    {
        $cartId = explode('/', $url);
        return (int) $cartId[5];
    }

    public function getConfigurationTabs(): array
    {
        $tabs = [];
        $tabs[] = [
            'name' => '<i class="fas fa-fw ok fa-cogs"></i> ' . __('Configurations'),
            'url' => Configure::read('app.slugHelper')->getConfigurationsList(),
            'key' => 'configurations',
        ];
        $tabs[] = [
            'name' => '<i class="fas fa-fw ok fa-clock"></i> ' . __('Cronjobs'),
            'url' => Configure::read('app.slugHelper')->getCronjobsList(),
            'key' => 'cronjobs',
        ];
        $tabs[] = [
            'name' => '<i class="fas fa-fw ok fa-percent"></i> ' . __('Tax_rates'),
            'url' => Configure::read('app.slugHelper')->getTaxesList(),
            'key' => 'tax_rates',
        ];
        return $tabs;
    }

    public function getReportTabs(): array
    {
        $tabs = [];
        foreach($this->getPaymentTexts() as $key => $paymentText) {
            if ($key == 'deposit' && (!Configure::read('app.isDepositEnabled') || !$this->paymentIsCashless())) {
                continue;
            }
            $tabs[] = [
                'name' => $paymentText,
                'url' => Configure::read('app.slugHelper')->getReport($key),
                'key' => $key,
            ];
        }
        $tabs[] = [
            'name' => __('Credit_overview'),
            'url' => Configure::read('app.slugHelper')->getCreditBalanceSum(),
            'key' => 'credit_balance_sum',
        ];
        if (Configure::read('app.isDepositEnabled') && $this->paymentIsCashless()) {
            $tabs[] = [
                'name' => __('Deposit_overview'),
                'url' => Configure::read('app.slugHelper')->getDepositOverviewDiagram(),
                'key' => 'deposit_overview',
            ];
        }
        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            $tabs[] = [
                'name' => __('Journal'),
                'url' => Configure::read('app.slugHelper')->getInvoices(),
                'key' => 'invoices',
            ];
        }
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $tabs[] = [
                'name' => __('Profit'),
                'url' => Configure::read('app.slugHelper')->getProfit(),
                'key' => 'profit',
            ];
        }
        return $tabs;
    }

    public function getPaymentTexts(): array
    {
        $paymentTexts = [
            Payment::TYPE_PRODUCT => __('Payment_type_credit_upload'),
            Payment::TYPE_PAYBACK => __('Payment_type_payback'),
            Payment::TYPE_DEPOSIT => __('Payment_type_deposit_return'),
        ];
        return $paymentTexts;
    }

    public function getPaymentText($paymentType): string
    {
        return $this->getPaymentTexts()[$paymentType];
    }

    public function getSuperadminProductPaymentTexts(): array
    {
        return [
            Payment::TYPE_PRODUCT => self::getPaymentText(Payment::TYPE_PRODUCT),
            Payment::TYPE_PAYBACK => self::getPaymentText(Payment::TYPE_PAYBACK),
        ];
    }

    public function getManufacturerDepositPaymentTexts(): array
    {
        return [
            Payment::TEXT_EMPTY_GLASSES => __('Empty_glasses'),
            Payment::TEXT_MONEY => __('Compensation_payment'),
        ];
    }

    public function getManufacturerDepositPaymentText($manufacturerDepositPaymentText): string
    {
        if (isset($this->getManufacturerDepositPaymentTexts()[$manufacturerDepositPaymentText])) {
            return $this->getManufacturerDepositPaymentTexts()[$manufacturerDepositPaymentText];
        }
        return $manufacturerDepositPaymentText;
    }

    public function getProductImageIdAsPath($imageId): string
    {
        preg_match_all('/[0-9]/', (string) $imageId, $imageIdAsArray);
        $imageIdAsPath = implode(DS, $imageIdAsArray[0]);
        return $imageIdAsPath;
    }

    public function getProductThumbsPath($imageIdAsPath): string
    {
        return $this->getUploadImageDir() . DS . 'products' . DS . $imageIdAsPath;
    }

    public function getBlogPostThumbsPath(): string
    {
        return $this->getUploadImageDir() . DS . 'blog_posts';
    }

    public function getManufacturerThumbsPath(): string
    {
        return $this->getUploadImageDir() . DS . 'manufacturers';
    }

    public function getCustomerThumbsPath(): string
    {
        return Configure::read('app.customerImagesDir');
    }

    public function getCategoryThumbsPath(): string
    {
        return $this->getUploadImageDir() . DS . 'categories';
    }

    public function getSliderThumbsPath(): string
    {
        return $this->getUploadImageDir() . DS . 'sliders';
    }

    public function getUploadImageDir(): string
    {
        return substr(WWW_ROOT, 0, - 1) . Configure::read('app.uploadedImagesDir');
    }

    public function getSliderImageSrc($sliderImage): string
    {
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'sliders' . DS;
        return $this->prepareAsUrl($urlPrefix . $sliderImage);
    }

    public function getImageFile($thumbsPath, $filenameWithoutExtension): string|null
    {
        $imageFilename = null;
        foreach(Configure::read('app.allowedImageMimeTypes') as $allowedImageExtension => $allowedImageMimeType) {
            $imageFilenameWithExtension = $filenameWithoutExtension . '.' . strtolower($allowedImageExtension);
            $imageFilename = $thumbsPath . DS . $imageFilenameWithExtension;
            if (file_exists($imageFilename)) {
                return $imageFilenameWithExtension;
            }
        }
        return $imageFilename;
    }

    /**
     * Returns a blogpost's image with desired size
     * If the blogpost has no image, but a manufacturer was specified, the manufacturer's image will be returned
     *
     * @param $blogPost
     * @param string $size
     */
    public function getBlogPostImageSrc($blogPost, $size): string
    {
        $thumbsPath = $this->getBlogPostThumbsPath();
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'blog_posts' . DS;

        $imageFilename = $this->getImageFile($thumbsPath, $blogPost->id_blog_post . '-' . $size . '-default');
        if (is_null($imageFilename) || !file_exists($thumbsPath . DS . $imageFilename)) {

            $manufacturerSize = 'medium';
            if($size == 'single') {
                $manufacturerSize = 'large';
            }

            $imageFilenameAndPath = $urlPrefix . 'no-' . $size . '-default.jpg';

            if ($blogPost->id_manufacturer != 0) {
                $imageFilenameAndPath = $this->getManufacturerImageSrc($blogPost->id_manufacturer, $manufacturerSize);
            }
        } else {
            $imageFilenameAndPath = $urlPrefix . $imageFilename;
        }

        return $this->prepareAsUrl($imageFilenameAndPath);
    }

    public function getManufacturerTermsOfUseSrcTemplate($manufacturerId): string
    {
        return Configure::read('app.uploadedFilesDir') . DS . 'manufacturers' . DS . $manufacturerId . DS . __('Filename_General-terms-and-conditions') . '.pdf';
    }

    public function getManufacturerTermsOfUseSrc($manufacturerId): string|false
    {
        $src = $this->getManufacturerTermsOfUseSrcTemplate($manufacturerId);
        if (file_exists(WWW_ROOT . $src)) {
            return $this->prepareAsUrl($src);
        }
        return false;
    }

    public function getManufacturerImageSrc($manufacturerId, $size): string
    {
        $thumbsPath = $this->getManufacturerThumbsPath();
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'manufacturers' . DS;


        $imageFilename = $this->getImageFile($thumbsPath, $manufacturerId . '-' . $size . '_default');
        if (is_null($imageFilename) || !file_exists($thumbsPath . DS . $imageFilename)) {
            $imageFilename = 'de-default-' . $size . '_default.jpg';
        }
        $imageFilenameAndPath = $urlPrefix . $imageFilename;

        return $this->prepareAsUrl($imageFilenameAndPath);
    }

    public function getCustomerImageSrc($customerId, $size): string
    {
        $thumbsPath = $this->getCustomerThumbsPath();
        $urlPrefix = 'profile-images/customers/';

        $imageFilename = $this->getImageFile($thumbsPath, $customerId . '-' . $size);
        if (is_null($imageFilename) || !file_exists($thumbsPath . DS . $imageFilename)) {
            $imageFilename = 'de-default-' . $size . '_default.jpg';
        }

        $imageFilenameAndPath = $urlPrefix . $imageFilename;

        $physicalFile = $thumbsPath . DS . $imageFilename;
        if (file_exists($physicalFile)) {
            $imageFilenameAndPath .= '?' . filemtime($physicalFile);
        }

        return $imageFilenameAndPath;
    }

    public function getCategoryImageSrc($categoryId): string|false
    {
        $thumbsPath = $this->getCategoryThumbsPath();
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'categories' . DS;

        $imageFilename = $this->getImageFile($thumbsPath, $categoryId .'-category_default');
        if (is_null($imageFilename) || !file_exists($thumbsPath . DS . $imageFilename)) {
            return false; // do not show any image if image does not exist
        } else {
            $imageFilenameAndPath = $urlPrefix . $imageFilename;
        }

        return $this->prepareAsUrl($imageFilenameAndPath);
    }

    public function getProductImageSrc($imageId, $size): string
    {
        $imageIdAsPath = $this->getProductImageIdAsPath($imageId);
        $thumbsPath = $this->getProductThumbsPath($imageIdAsPath);
        $urlPrefix = Configure::read('app.uploadedImagesDir') . DS . 'products' . DS;

        $imageFilename = $this->getImageFile($thumbsPath, $imageId . '-' . $size . '_default');
        if (is_null($imageFilename) || !file_exists($thumbsPath . DS . $imageFilename)) {
            $imageFilenameAndPath = $urlPrefix . 'de-default-' . $size . '_default.jpg';
        } else {
            $imageFilenameAndPath = $urlPrefix . $imageIdAsPath . DS . $imageFilename;
        }

        return $this->prepareAsUrl($imageFilenameAndPath);
    }


    public function getProductImageSrcWithManufacturerImageFallback($productImageId, $manufacturerId): array
    {

        $productImageLargeSrc = $this->getProductImageSrc($productImageId, 'thickbox');
        $productImageLargeExists = $this->largeImageExists($productImageLargeSrc);
        $productImageSrc = $this->getProductImageSrc($productImageId, 'home');
        if (!$productImageLargeExists) {
            $productImageLargeSrc = $this->getManufacturerImageSrc($manufacturerId, 'large');
            $productImageLargeExists = $this->largeImageExists($productImageLargeSrc);
            $productImageSrc = $this->getManufacturerImageSrc($manufacturerId, 'medium');
            if (!$productImageLargeExists) {
                $productImageSrc = $this->getProductImageSrc($productImageId, 'home');
            }
        }

        $result = [
            'productImageLargeSrc' => $productImageLargeSrc,
            'productImageLargeExists' => $productImageLargeExists,
            'productImageSrc' => $productImageSrc,
        ];

        return $result;
    }

    public function largeImageExists($imgSrc): bool
    {
        return !preg_match('/de-default/', $imgSrc);
    }

    public function prepareAsUrl($string): string
    {
        $physicalFile = substr(WWW_ROOT, 0, - 1) . $string;
        if (file_exists($physicalFile)) {
            $string .= '?' . filemtime($physicalFile);
        }
        $string = str_replace(DS, '/', $string);
        return $string;
    }

    public function getOrderListLink($manufacturerName, $manufacturerId, $deliveryDay, $groupTypeLabel, $currentDate, $isAnonymized): string
    {
        $url = Configure::read('app.folder_order_lists');
        $url .= DS . date('Y', strtotime($deliveryDay)) . DS . date('m', strtotime($deliveryDay)) . DS;
        if ($isAnonymized) {
            $url .= 'anonymized' . DS;
        }
        $url .= $deliveryDay . '_' . StringComponent::slugify($manufacturerName) . '_' . $manufacturerId . __('_Order_list_filename_') . $groupTypeLabel . '_' . StringComponent::slugify(Configure::read('appDb.FCS_APP_NAME')) . '-' . $currentDate . '.pdf';
        if (Configure::check('app.outputStringReplacements')) {
            $url = OutputFilterService::replace($url, Configure::read('app.outputStringReplacements'));
        }
        return $url;
    }

    public function getInvoiceLink($name, $id, $invoiceDate, $invoiceNumber): string
    {
        $url = Configure::read('app.folder_invoices') . DS . date('Y', strtotime($invoiceDate)) . DS . date('m', strtotime($invoiceDate)) . DS;
        $url .= $invoiceDate . '_' . StringComponent::slugify($name) . '_' . $id . __('_Invoice_filename_') . $invoiceNumber . '_' . StringComponent::slugify(Configure::read('appDb.FCS_APP_NAME')) . '.pdf';
        return $url;
    }

    public function getApprovalStates(): array
    {
        return [
            1 => __('approval_state_ok'),
            0 => __('approval_state_open'),
            -1 => __('approval_state_not_ok'),
        ];
    }

    public function getActiveStatesOnOff(): array
    {
        return [
            1 => __('active_state_active'),
            0 => __('active_state_inactive')
        ];
    }

    public function getActiveStates(): array
    {
        return [
            1 => __('active_state_active'),
            0 => __('active_state_inactive'),
            'all' => __('active_state_all')
        ];
    }

    public function getOrderStates(): array
    {
        return Configure::read('app.orderStates');
    }

    public function getOrderStatesCashless(): array
    {
        return [
            OrderDetail::STATE_OPEN,
            OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER,
            OrderDetail::STATE_BILLED_CASHLESS,
        ];
    }

    public function getOrderStateIds(): array
    {
        return array_keys(self::getOrderStates());
    }
}
