<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitDataSeed extends AbstractSeed
{
    public function run(): void
    {
        $query = "
INSERT INTO `fcs_configuration` VALUES
(11,1,'FCS_PRODUCT_AVAILABILITY_LOW','Low availability<br /><div class=\"small\">From which amount on there should be an information text visible \"(x available\")?</div>','10','number',600,'ru_RU','2017-07-26 13:24:47','2014-06-01 01:40:34'),
(31,1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','How many days products should be \"marked as new\"?','7','number',700,'ru_RU','2017-07-26 13:24:47','2014-05-14 21:15:45'),
(456,1,'FCS_FOOTER_CMS_TEXT','Additional text for footer',NULL,'textarea_big',920,'ru_RU','2014-06-11 17:50:55','2016-07-01 21:47:47'),
(508,1,'FCS_FACEBOOK_URL','Facebook url for embedding in footer','https://www.facebook.com/FoodCoopShop/','text',910,'ru_RU','2015-07-08 13:23:54','2015-07-08 13:23:54'),
(538,1,'FCS_REGISTRATION_EMAIL_TEXT','Additional text that is sent in the registration e-mail after a successful registration. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><i class=\"fas fa-info-circle\"></i> E-mail preview</a>','','textarea_big',1700,'ru_RU','2016-06-26 00:00:00','2016-06-26 00:00:00'),
(543,1,'FCS_RIGHT_INFO_BOX_HTML','Content of the box in the right column below the shopping cart. <br /><div class=\"small\">To make the background of a row green, please format as \"Heading 3\".</div>','<h3>Delivery time</h3><p>You can order every week until Tuesday midnight and pick the products up the following Friday.</p>','textarea_big',1500,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(544,1,'FCS_NO_DELIVERY_DAYS_GLOBAL','Delivery break for all manufacturers?<br /><div class=\"small\">Here you can define delivery-free days for the whole food-coop.</div>','','multiple_dropdown',100,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(545,1,'FCS_ACCOUNTING_EMAIL','E-mail address for the financial manager<br /><div class=\"small\">Who receives the notification that invoices have been sent?</div>','','text',1100,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(546,1,'FCS_REGISTRATION_INFO_TEXT','Info text in registration form<br /><div class=\"small\">This info text is shown in the registration form below the e-mail address.</div>','You need to be a member if you want to order here.','textarea_big',1600,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(547,1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','Products visible for guests?','0','boolean',200,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(548,1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','Automatically activate new members?','0','boolean',500,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(549,1,'FCS_MINIMAL_CREDIT_BALANCE','Up to which credit amount orders should be possible?','0','number',1250,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(550,1,'FCS_BANK_ACCOUNT_DATA','Bank account for credit uploads.','Credit account Example Bank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',1300,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(552,1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS','Additional deliverey details for manufacturers<br /><div class=\"small\">will be shown in the order lists after the delivery date.</div>',', 3pm to 5pm','text',1200,'ru_RU','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(553,1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','E-mail adress to which all automatically generated e-mail are sent to as BCC (Backup).<br /><div class=\"small\">Can be left empty.</div>','','text',1900,'ru_RU','2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Show link to www.foodcoopshop.com?<br /><div class=\"small\">The link is shown in the footer.</div>','1','boolean',930,'ru_RU','2016-11-27 00:00:00','2016-11-27 00:00:00'),
(556,1,'FCS_APP_NAME','Name of the food-coop','','text',50,'ru_RU','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,1,'FCS_APP_ADDRESS','Adress of the food-coop<br /><div class=\"small\">Used in footer of homepage and e-mails, privacy policy and terms of use.</div>','','textarea',60,'ru_RU','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,1,'FCS_APP_EMAIL','E-mail adress of the food-coop<br /><div class=\"small\"></div>','','text',900,'ru_RU','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,1,'FCS_PLATFORM_OWNER','Operator of the platform<br /><div class=\"small\">For privacy policy and terms of use, please also add adrress. Can be left empty if the food-coop itself is operator.</div>','','textarea',90,'ru_RU','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(564,1,'FCS_ORDER_COMMENT_ENABLED','Show comment field when placing an order?<br /><div class=\"small\">Shown in admin area under \"Orders\".</div>','1','boolean',130,'ru_RU','2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,1,'FCS_USE_VARIABLE_MEMBER_FEE','Use variable member fee?<br /><div class=\"small\">Reduce the variable member fee in the manufacturer\'s invoices? Therefore the prices need to be increased.</div>','0','readonly',400,'ru_RU','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Default value for variable member fee<br /><div class=\"small\">The percentage can be changed in the manufacturer\'s settings.</div>','0','readonly',500,'ru_RU','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(567,1,'FCS_NETWORK_PLUGIN_ENABLED','Network module activated?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/en/network-module\" target=\"_blank\">Infos to the network module</a></div>','0','readonly',500,'ru_RU','2017-09-14 00:00:00','2017-09-14 00:00:00'),
(574,1,'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS','Shop product price for guests?','0','boolean',210,'ru_RU','2018-05-28 18:05:54','2018-05-28 18:05:54'),
(575,1,'FCS_CURRENCY_SYMBOL','Currency symbol','$','readonly',520,'ru_RU','2018-06-13 19:53:14','2018-06-13 19:53:14'),
(576,1,'FCS_DEFAULT_LOCALE','Language','ru_RU','readonly',550,'ru_RU','2018-06-26 10:18:55','2018-06-26 10:18:55'),
(577,1,'FCS_FOODCOOPS_MAP_ENABLED','Show map with other foodcoops on home?','1','boolean',1280,'ru_RU','2019-02-11 22:25:36','2019-02-11 22:25:36'),
(578,1,'FCS_WEEKLY_PICKUP_DAY','Weekly pickup day','5','readonly',600,'ru_RU','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(579,1,'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA','Sending of order lists: x days before pickup day','2','readonly',650,'ru_RU','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(580,1,'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM','Allow weekly orders for stock products?','1','boolean',750,'ru_RU','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(581,1,'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS','Only show stock products in instant orders?','0','boolean',760,'ru_RU','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(582,1,'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES','Include stock products in invoices?','1','readonly',600,'ru_RU','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(583,1,'FCS_REGISTRATION_NOTIFICATION_EMAILS','Who should be notified on new registrations?<br /><div class=\"small\">Please separate multiple e-mail addresses with , (no space).</div>','','text',550,'ru_RU','2019-03-05 20:01:59','2019-03-05 20:01:59'),
(584,1,'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED','Use self-service mode for stock products?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/en/self-service-mode\" target=\"_blank\">Online documentation</a></div>','0','boolean',3000,'ru_RU','2019-04-17 20:01:59','2019-04-17 20:01:59'),
(585,1,'FCS_APP_ADDITIONAL_DATA','Additional food-coop infos','','textarea',80,'ru_RU','2019-08-03 20:07:08','2019-08-03 20:07:08'),
(586,1,'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED','Run self-service mode in test mode?<br /><div class=\"small\">Does not add links to main menu and to stock products.</div>','0','boolean',3100,'ru_RU','2019-12-09 13:46:32','2019-12-09 13:46:32'),
(587,1,'FCS_CASHLESS_PAYMENT_ADD_TYPE','Type of adding the payments<br /><div class=\"small\">How do the payment addings get into FoodCoopShop?</div>','list-upload','dropdown',1450,'ru_RU','2020-02-11 10:13:01','2020-02-11 10:13:01'),
(589,1,'FCS_FEEDBACK_TO_PRODUCTS_ENABLED','Are members allowed to write feedback to products?','0','boolean',3200,'ru_RU','2020-06-19 09:02:50','2020-06-19 09:02:50'),
(590,1,'FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY','Pickup day can be selected by member on order confirmation.','0','readonly',590,'ru_RU','2020-07-06 10:34:39','2020-07-06 10:34:39'),
(591,1,'FCS_SEND_INVOICES_TO_CUSTOMERS','Retail mode activated?.','0','readonly',580,'ru_RU','2020-10-29 10:06:39','2020-10-29 10:06:39'),
(592,1,'FCS_DEPOSIT_TAX_RATE','VAT for deposit','20.00','readonly',581,'ru_RU','2020-11-03 15:24:01','2020-11-03 15:24:01'),
(593,1,'FCS_INVOICE_HEADER_TEXT','Header text for invoices to members','','readonly',582,'ru_RU','2020-11-03 15:24:01','2020-11-03 15:24:01'),
(594,1,'FCS_MEMBER_FEE_PRODUCTS','Which products are used as member fee product?<div class=\"small\">The selected products are the basis for the column Member Fee in the members adminstration and are not shown in the turnover statistics.</div>','','multiple_dropdown',3300,'ru_RU','2020-12-20 19:26:16','2020-12-20 19:26:16'),
(595,1,'FCS_CHECK_CREDIT_BALANCE_LIMIT','Height of credit saldo when the reminder email is sent.','50','number',1450,'ru_RU','2021-01-19 11:23:39','2021-01-19 11:23:39'),
(596,1,'FCS_PURCHASE_PRICE_ENABLED','Enable input of purchase price?<div class=\"small\">The purchase price is the base for profit statistics and bill of delivery to manufacturers.</div>','0','readonly',587,'ru_RU','2021-05-10 11:27:43','2021-05-10 11:27:43'),
(597,1,'FCS_HELLO_CASH_API_ENABLED','Enable API to hellocash.at?<div class=\"small\">Invoices (cash and cashless) are generated by hellocash.at.</div>','0','readonly',583,'ru_RU','2021-07-07 10:55:08','2021-07-07 10:55:08'),
(598,1,'FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS','Save storage location for products?<div class=\"small\">New button next to \"Orders - show order as pdf\"</div>','1','boolean',3210,'ru_RU','2021-08-02 11:28:34','2021-08-02 11:28:34'),
(599,1,'FCS_INSTAGRAM_URL','Instagram url for embedding in footer','','text',920,'ru_RU','2021-09-10 21:23:13','2021-09-10 21:23:13'),
(600,1,'FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY','Ordering products with delivery rhythm one or two weeks is only possible in the week before delivery.','0','boolean',3210,'ru_RU','2022-02-01 17:48:40','2022-02-01 17:48:40'),
(601,1,'FCS_INVOICE_NUMBER_PREFIX','Prefix for invoice numbers<br /><div class=\"small\">Max. 6 chars incl. separator.</div>','','readonly',586,'ru_RU','2022-03-21 12:02:57','2022-03-21 12:02:57'),
(602,1,'FCS_TAX_BASED_ON_NET_INVOICE_SUM','Invoices for companies with fixed tax rate<br /><div class=\"small\">Vat is calculated based on the sum of net price of the invoice.</div>','0','readonly',585,'ru_RU','2022-03-23 09:12:33','2022-03-23 09:12:33'),
(603,1,'FCS_NEWSLETTER_ENABLED','Newsletter enabled?<br /><div class=\"small\">Shows newsletter checkbox on registration.</div>','0','boolean',3400,'ru_RU','2022-04-12 15:28:56','2022-04-12 15:28:56'),
(604,1,'FCS_USER_FEEDBACK_ENABLED','Member and manufacturer feedback enabled?<br /><div class=\"small\">Members and manufacturers can write feedback.</div>','0','boolean',3500,'ru_RU','2022-07-19 14:39:36','2022-07-19 14:39:36');";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_storage_locations` VALUES
            (1,'No cooling',10),
            (2,'Refrigerator',20),
            (3,'Freezer',30);
        ";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_category` VALUES
            (20,2,'All Products','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
        ";
        $this->execute($query);

    }
}
