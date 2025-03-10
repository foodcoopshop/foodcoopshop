<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitDataSeed extends AbstractSeed
{
    public function run(): void
    {

        $query = "
            INSERT INTO `fcs_cronjobs` VALUES
            (1,'BackupDatabase','day',NULL,NULL,'04:00:00',1),
            (2,'CheckCreditBalance','week',NULL,'Friday','22:50:00',1),
            (3,'EmailOrderReminder','week',NULL,'Monday','18:10:00',1),
            (4,'PickupReminder','week',NULL,'Monday','09:10:00',1),
            (5,'SendInvoicesToManufacturers','month',5,NULL,'10:30:00',1),
            (6,'SendOrderLists','day',NULL,NULL,'04:20:00',1),
            (7,'SendInvoicesToCustomers','week',NULL,'Saturday','10:00:00',0),
            (8,'SendDeliveryNotes','month',1,NULL,'18:00:00',0);
        ";
        $this->execute($query);

        $query = "INSERT INTO `fcs_sliders` VALUES (1,'demo-slider.jpg',NULL,0,0,1);";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_configuration` (active, name, value, type, position) VALUES
            (1,'FCS_PRODUCT_AVAILABILITY_LOW','10','number',600),
            (1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','7','number',700),
            (1,'FCS_FOOTER_CMS_TEXT',NULL,'textarea_big',920),
            (1,'FCS_FACEBOOK_URL','','text',910),
            (1,'FCS_REGISTRATION_EMAIL_TEXT','','textarea_big',1700),
            (1,'FCS_RIGHT_INFO_BOX_HTML','<h3>Abholzeiten</h3>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und die Produkte am Freitag abholen.</p>\r\n','textarea_big',1500),
            (1,'FCS_NO_DELIVERY_DAYS_GLOBAL','','multiple_dropdown',100),
            (1,'FCS_ACCOUNTING_EMAIL','','text',1100),
            (1,'FCS_REGISTRATION_INFO_TEXT','Um bei uns zu bestellen musst du Vereinsmitglied sein.','textarea_big',1600),
            (1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','0','boolean',200),
            (1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','0','boolean',500),
            (1,'FCS_MINIMAL_CREDIT_BALANCE','0','number',1250),
            (1,'FCS_BANK_ACCOUNT_DATA','Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',1300),
            (1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS',', 15:00 bis 17:00 Uhr','text',1200),
            (1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','','text',1900),
            (1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','1','boolean',930),
            (1,'FCS_APP_NAME','','text',50),
            (1,'FCS_APP_ADDRESS','','textarea',60),
            (1,'FCS_APP_EMAIL','','text',900),
            (1,'FCS_PLATFORM_OWNER','','textarea',90),
            (1,'FCS_ORDER_COMMENT_ENABLED','1','boolean',130),
            (1,'FCS_USE_VARIABLE_MEMBER_FEE','0','readonly',400),
            (1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','0','readonly',500),
            (1,'FCS_NETWORK_PLUGIN_ENABLED','0','readonly',500),
            (1,'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS','0','boolean',210),
            (1,'FCS_CURRENCY_SYMBOL','€','readonly',520),
            (1,'FCS_DEFAULT_LOCALE','de_DE','readonly',550),
            (1,'FCS_FOODCOOPS_MAP_ENABLED','1','boolean',1280),
            (1,'FCS_WEEKLY_PICKUP_DAY','5','readonly',600),
            (1,'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA','2','readonly',650),
            (1,'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM','1','boolean',750),
            (1,'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS','0','boolean',760),
            (1,'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES','1','readonly',600),
            (1,'FCS_REGISTRATION_NOTIFICATION_EMAILS','','text',550),
            (1,'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED','0','boolean',3000),
            (1,'FCS_APP_ADDITIONAL_DATA','','textarea',80),
            (1,'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED','0','boolean',3100),
            (1,'FCS_CASHLESS_PAYMENT_ADD_TYPE','list-upload','dropdown',1450),
            (1,'FCS_FEEDBACK_TO_PRODUCTS_ENABLED','0','boolean',3200),
            (1,'FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY','0','readonly',590),
            (1,'FCS_SEND_INVOICES_TO_CUSTOMERS','0','readonly',580),
            (1,'FCS_DEPOSIT_TAX_RATE','20,00','readonly',580),
            (1,'FCS_INVOICE_HEADER_TEXT','','readonly',582),
            (1,'FCS_MEMBER_FEE_PRODUCTS','','multiple_dropdown',3300),
            (1,'FCS_CHECK_CREDIT_BALANCE_LIMIT','50','number',1450),
            (1,'FCS_PURCHASE_PRICE_ENABLED','0','readonly',587),
            (1,'FCS_HELLO_CASH_API_ENABLED','0','readonly',583),
            (1,'FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS','1','boolean',3210),
            (1,'FCS_INSTAGRAM_URL','','text',920),
            (1,'FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY','0','boolean',3210),
            (1,'FCS_INVOICE_NUMBER_PREFIX','','readonly',586),
            (1,'FCS_TAX_BASED_ON_NET_INVOICE_SUM','0','readonly',585),
            (1,'FCS_NEWSLETTER_ENABLED','0','boolean',3400),
            (1,'FCS_USER_FEEDBACK_ENABLED','0','boolean',3500);
            ";
            // FCS_HOME_TEXT added in Migration AddConfigurationTextForHome
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
