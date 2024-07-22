<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitDataSeed extends AbstractSeed
{
    public function run(): void
    {
        $query = "
INSERT INTO `fcs_configuration` (name, value, type, position) VALUES
('FCS_PRODUCT_AVAILABILITY_LOW','10','number',600),
('FCS_DAYS_SHOW_PRODUCT_AS_NEW','7','number',700),
('FCS_FOOTER_CMS_TEXT',NULL,'textarea_big',920),
('FCS_FACEBOOK_URL','https://www.facebook.com/FoodCoopShop/','text',910),
('FCS_REGISTRATION_EMAIL_TEXT','','textarea_big',1700),
('FCS_RIGHT_INFO_BOX_HTML','<h3>Abholzeiten</h3>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und die Produkte am Freitag abholen.</p>\r\n','textarea_big',1500),
('FCS_NO_DELIVERY_DAYS_GLOBAL','','multiple_dropdown',100),
('FCS_ACCOUNTING_EMAIL','','text',1100),
('FCS_REGISTRATION_INFO_TEXT','Um bei uns zu bestellen musst du Vereinsmitglied sein.','textarea_big',1600),
('FCS_SHOW_PRODUCTS_FOR_GUESTS','0','boolean',200),
('FCS_DEFAULT_NEW_MEMBER_ACTIVE','0','boolean',500),
('FCS_MINIMAL_CREDIT_BALANCE','0','number',1250),
('FCS_BANK_ACCOUNT_DATA','Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',1300),
('FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS',', 15:00 bis 17:00 Uhr','text',1200),
('FCS_BACKUP_EMAIL_ADDRESS_BCC','','text',1900),
('FCS_SHOW_FOODCOOPSHOP_BACKLINK','1','boolean',930),
('FCS_APP_NAME','','text',50),
('FCS_APP_ADDRESS','','textarea',60),
('FCS_APP_EMAIL','','text',900),
('FCS_PLATFORM_OWNER','','textarea',90),
('FCS_ORDER_COMMENT_ENABLED','1','boolean',130),
('FCS_USE_VARIABLE_MEMBER_FEE','0','readonly',400),
('FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','0','readonly',500),
('FCS_NETWORK_PLUGIN_ENABLED','0','readonly',500),
('FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS','0','boolean',210),
('FCS_CURRENCY_SYMBOL','€','readonly',520),
('FCS_DEFAULT_LOCALE','de_DE','readonly',550),
('FCS_FOODCOOPS_MAP_ENABLED','1','boolean',1280),
('FCS_WEEKLY_PICKUP_DAY','5','readonly',600),
('FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA','2','readonly',650),
('FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM','1','boolean',750),
('FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS','0','boolean',760),
('FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES','1','readonly',600),
('FCS_REGISTRATION_NOTIFICATION_EMAILS','','text',550),
('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED','0','boolean',3000),
('FCS_APP_ADDITIONAL_DATA','','textarea',80),
('FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED','0','boolean',3100),
('FCS_CASHLESS_PAYMENT_ADD_TYPE','list-upload','dropdown',1450),
('FCS_FEEDBACK_TO_PRODUCTS_ENABLED','0','boolean',3200),
('FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY','0','readonly',590),
('FCS_SEND_INVOICES_TO_CUSTOMERS','0','readonly',580),
('FCS_DEPOSIT_TAX_RATE','20,00','readonly',580),
('FCS_INVOICE_HEADER_TEXT','','readonly',582),
('FCS_MEMBER_FEE_PRODUCTS','','multiple_dropdown',3300),
('FCS_CHECK_CREDIT_BALANCE_LIMIT','50','number',1450),
('FCS_PURCHASE_PRICE_ENABLED','0','readonly',587),
('FCS_HELLO_CASH_API_ENABLED','0','readonly',583),
('FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS','1','boolean',3210),
('FCS_INSTAGRAM_URL','','text',920),
('FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY','0','boolean',3210),
('FCS_INVOICE_NUMBER_PREFIX','','readonly',586),
('FCS_TAX_BASED_ON_NET_INVOICE_SUM','0','readonly',585),
('FCS_NEWSLETTER_ENABLED','0','boolean',3400),
('FCS_USER_FEEDBACK_ENABLED','0','boolean',3500);
";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_storage_locations` VALUES
            (1,'Keine Kühlung',10),
            (2,'Kühlschrank',20),
            (3,'Tiefkühler',30);
        ";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_category` VALUES
            (20,2,'Alle Produkte','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
        ";
        $this->execute($query);

    }
}
