TRUNCATE TABLE  `fcs_cart`;
TRUNCATE TABLE  `fcs_cake_invoices`;
TRUNCATE TABLE  `fcs_cake_deposits`;
TRUNCATE TABLE  `fcs_cake_action_logs`;

TRUNCATE TABLE  `fcs_employee`;
TRUNCATE TABLE  `fcs_employee_shop`;

TRUNCATE TABLE  `fcs_category_product`;

TRUNCATE TABLE  `fcs_cms`;
TRUNCATE TABLE  `fcs_cms_lang`;
TRUNCATE TABLE  `fcs_cms_shop`;

TRUNCATE TABLE  `fcs_customer`;
TRUNCATE TABLE  `fcs_customer_group`;

TRUNCATE TABLE  `fcs_image`;
TRUNCATE TABLE  `fcs_image_lang`;
TRUNCATE TABLE  `fcs_image_shop`;

TRUNCATE TABLE  `fcs_info`;
TRUNCATE TABLE  `fcs_info_lang`;

TRUNCATE TABLE  `fcs_manufacturer`;
TRUNCATE TABLE  `fcs_manufacturer_lang`;
TRUNCATE TABLE  `fcs_manufacturer_shop`;

TRUNCATE TABLE  `fcs_product`;
TRUNCATE TABLE  `fcs_product_attachment`;
TRUNCATE TABLE  `fcs_product_attribute`;
TRUNCATE TABLE  `fcs_product_attribute_combination`;
TRUNCATE TABLE  `fcs_product_attribute_image`;
TRUNCATE TABLE  `fcs_product_attribute_shop`;
TRUNCATE TABLE  `fcs_product_lang`;
TRUNCATE TABLE  `fcs_product_sale`;
TRUNCATE TABLE  `fcs_product_shop`;

TRUNCATE TABLE  `fcs_stock_available`;

TRUNCATE TABLE  `fcs_address`;
TRUNCATE TABLE  `fcs_attribute_impact`;

TRUNCATE TABLE  `fcs_orders`;
TRUNCATE TABLE  `fcs_order_carrier`;
TRUNCATE TABLE  `fcs_order_detail`;
TRUNCATE TABLE  `fcs_order_detail_tax`;
TRUNCATE TABLE  `fcs_order_history`;
TRUNCATE TABLE  `fcs_order_invoice`;
TRUNCATE TABLE  `fcs_order_invoice_payment`;
TRUNCATE TABLE  `fcs_order_invoice_tax`;
TRUNCATE TABLE  `fcs_smart_blog_post`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_shop`;

TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;
TRUNCATE TABLE  `fcs_smart_blog_post_lang`;

TRUNCATE TABLE  `fcs_specific_price`;
TRUNCATE TABLE  `fcs_specific_price_priority`;


INSERT INTO `fcs_employee` (`id_employee`, `id_profile`, `id_lang`, `lastname`, `firstname`, `email`, `passwd`, `last_passwd_gen`, `stats_date_from`, `stats_date_to`, `stats_compare_from`, `stats_compare_to`, `stats_compare_option`, `preselect_date_range`, `bo_color`, `bo_theme`, `bo_css`, `default_tab`, `bo_width`, `bo_menu`, `active`, `optin`, `id_last_order`, `id_last_customer_message`, `id_last_customer`) VALUES
(1, 1, 1, 'Super', 'Admin', 'office@foodcoopshop.com', '098b2a0c07772e6fca83d2caca6d554c', '2015-03-05 12:37:26', '2014-09-01', '2014-09-30', '0000-00-00', '0000-00-00', 1, 'month', '', 'default', 'admin-theme.css', 1, 0, 1, 1, 0, 10, 0, 96);

INSERT INTO `fcs_employee_shop` (`id_employee`, `id_shop`) VALUES (1, 1);

INSERT INTO `fcs_customer` (`id_customer`, `id_shop_group`, `id_shop`, `id_gender`, `id_default_group`, `id_lang`, `id_risk`, `company`, `siret`, `ape`, `firstname`, `lastname`, `email`, `passwd`, `last_passwd_gen`, `birthday`, `newsletter`, `ip_registration_newsletter`, `newsletter_date_add`, `optin`, `website`, `outstanding_allow_amount`, `show_public_prices`, `max_payment_days`, `secure_key`, `note`, `active`, `is_guest`, `deleted`, `date_add`, `date_upd`) VALUES
(1, 1, 1, 1, 4, 1, 0, NULL, NULL, NULL, 'Super', 'Admin', 'office@foodcoopshop.com', '098b2a0c07772e6fca83d2caca6d554c', '2015-03-05 06:40:32', '0000-00-00', 0, '188.22.232.163', '2015-03-05 13:40:32', 0, NULL, '0.000000', 0, 0, '405443148efa354f5bf6dab06fa76f6c', NULL, 1, 0, 0, '2015-03-05 13:40:32', '2015-03-05 13:41:34');

INSERT INTO `fcs_customer_group` (`id_customer`, `id_group`) VALUES (1, 3);


#google analytics
UPDATE  `fcs_configuration` SET  `value` =  '' WHERE  `fcs_configuration`.`id_configuration` =458;
UPDATE  `fcs_configuration` SET  `value` =  '' WHERE  `fcs_configuration`.`id_configuration` =482;
UPDATE  `fcs_configuration` SET  `value` =  '' WHERE  `fcs_configuration`.`id_configuration` =455;
UPDATE  `fcs_configuration` SET  `value` =  '' WHERE  `fcs_configuration`.`id_configuration` =453;
UPDATE  `fcs_configuration` SET  `value` =  '' WHERE  `fcs_configuration`.`id_configuration` =218;
UPDATE  `fcs_configuration` SET  `value` =  '' WHERE  `fcs_configuration`.`id_configuration` =210;

TRUNCATE TABLE `fcs_cake_customer_credits`;
