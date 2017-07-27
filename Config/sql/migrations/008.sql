--
-- Prepare for MySQL 5.7
--
-- More strict default values require "NOT NULL" columns to always be
-- given on INSERT or have default values defined. Before 5.7 not giving
-- values for "NOT NULL" columns resulted in automatic default values
-- being inserted (zero-ish values).
--
-- TIMESTAMP now is more bound to its purpose and is "TIMESTAMP NOT NULL
-- DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" only. Before
-- 5.7 it was only an alias for DATETIME defaulting to CURRENT_TIMESTAMP
-- when inserting NULL (instead of zero-ish value on DATETIME).
--
-- Also in 5.7 DATE / DATETIME / TIMESTAMP no longer accept zero-ish
-- values. It's suggested to use NULL instead or '1000-01-01' (the lowest
-- valid date value in MySQL).
--
-- Instead of scanning source code and adding missing INSERT values it's
-- easier to set zero-ish default values to the columns. DATETIME now
-- defaults to CURRENT_TIMESTAMP, DATE to '1000-01-01'. The only column
-- using TIMESTAMP is now DATETIME, as it is not OK to have ON UPDATE
-- CURRENT_TIMESTAMP for it.
--
-- Already stored zero-ish DATETIME / DATE values are converted to columns
-- default value. To allow this in MySQL 5.7+ strict SQL mode must be
-- overwritten before using zero-ish date values in the query. BEWARE!
-- There can be "safe update mode" enabled, which does not allow UPDATEs
-- without a KEY column in WHERE.
--
-- id_* columns still are protected by foreign keys against DB corruption.

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER' */;
ALTER TABLE `fcs_address`
  ALTER `id_country` SET DEFAULT '0',
  ALTER `alias` SET DEFAULT '',
  ALTER `lastname` SET DEFAULT '',
  ALTER `firstname` SET DEFAULT '',
  ALTER `address1` SET DEFAULT '',
  ALTER `city` SET DEFAULT '',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_address` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_address` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_attribute`
  ALTER `id_attribute_group` SET DEFAULT '0'
;
UPDATE `fcs_attribute` SET `created` = DEFAULT WHERE `created` = '0000-00-00 00:00:00';
UPDATE `fcs_attribute` SET `modified` = DEFAULT WHERE `modified` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_attribute_lang`
  ALTER `id_attribute` SET DEFAULT '0',
  ALTER `id_lang` SET DEFAULT '0',
  ALTER `name` SET DEFAULT ''
;

ALTER TABLE `fcs_cake_action_logs`
  ALTER `type` SET DEFAULT '',
  ALTER `customer_id` SET DEFAULT '0',
  ALTER `object_id` SET DEFAULT '0',
  ALTER `object_type` SET DEFAULT '',
  MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_cake_action_logs` SET `date` = DEFAULT WHERE `date` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_cake_cart_products`
  ALTER `id_cart` SET DEFAULT '0',
  ALTER `id_product` SET DEFAULT '0',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_cake_cart_products` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_cake_cart_products` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_cake_carts`
  ALTER `id_customer` SET DEFAULT '0',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_cake_carts` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_cake_carts` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_cake_deposits`
  ALTER `deposit` SET DEFAULT '0.0'
;

ALTER TABLE `fcs_cake_invoices`
  ALTER `id_manufacturer` SET DEFAULT '0',
  ALTER `invoice_number` SET DEFAULT '0',
  MODIFY `send_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  ALTER `user_id` SET DEFAULT '0'
;
UPDATE `fcs_cake_invoices` SET `send_date` = DEFAULT WHERE `send_date` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_cake_payments`
  ALTER `amount` SET DEFAULT '0.00',
  ALTER `text` SET DEFAULT '',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  ALTER `approval` SET DEFAULT '0',
  ALTER `changed_by` SET DEFAULT '0',
  ALTER `created_by` SET DEFAULT '0'
;
UPDATE `fcs_cake_payments` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_cake_payments` SET `date_changed` = DEFAULT WHERE `date_changed` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_category`
  ALTER `id_parent` SET DEFAULT '0',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_category` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_category` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_category_lang`
  ALTER `id_category` SET DEFAULT '0',
  ALTER `id_lang` SET DEFAULT '0',
  ALTER `name` SET DEFAULT '',
  ALTER `link_rewrite` SET DEFAULT ''
;

ALTER TABLE `fcs_category_product`
  ALTER `id_category` SET DEFAULT '0',
  ALTER `id_product` SET DEFAULT '0'
;

ALTER TABLE `fcs_cms`
  ALTER `id_cms_category` SET DEFAULT '0',
  ALTER `url` SET DEFAULT '',
  ALTER `id_customer` SET DEFAULT '0',
  ALTER `is_private` SET DEFAULT '0',
  MODIFY `modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  ALTER `lft` SET DEFAULT '0',
  ALTER `rght` SET DEFAULT '0'
;
UPDATE `fcs_cms` SET `modified` = DEFAULT WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `fcs_cms` SET `created` = DEFAULT WHERE `created` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_cms_lang`
  ALTER `id_cms` SET DEFAULT '0',
  ALTER `id_lang` SET DEFAULT '0',
  ALTER `meta_title` SET DEFAULT '',
  ALTER `link_rewrite` SET DEFAULT ''
;

ALTER TABLE `fcs_configuration`
  ALTER `name` SET DEFAULT '',
  ALTER `type` SET DEFAULT '',
  ALTER `position` SET DEFAULT '0',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_configuration` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_configuration` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_customer`
  ALTER `id_gender` SET DEFAULT '0',
  ALTER `firstname` SET DEFAULT '',
  ALTER `lastname` SET DEFAULT '',
  ALTER `email` SET DEFAULT '',
  ALTER `passwd` SET DEFAULT '',
  MODIFY `last_passwd_gen` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
  MODIFY `terms_of_use_accepted_date` DATE NOT NULL DEFAULT '1000-01-01',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_customer` SET `last_passwd_gen` = DEFAULT WHERE `last_passwd_gen` = '0000-00-00 00:00:00';
UPDATE `fcs_customer` SET `birthday` = DEFAULT WHERE `birthday` = '0000-00-00';
UPDATE `fcs_customer` SET `newsletter_date_add` = DEFAULT WHERE `newsletter_date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_customer` SET `terms_of_use_accepted_date` = DEFAULT WHERE `terms_of_use_accepted_date` = '0000-00-00';
UPDATE `fcs_customer` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_customer` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_homeslider_slides_lang`
  ALTER `id_homeslider_slides` SET DEFAULT '0',
  ALTER `id_lang` SET DEFAULT '0',
  ALTER `title` SET DEFAULT '',
  ALTER `legend` SET DEFAULT '',
  ALTER `url` SET DEFAULT '',
  ALTER `image` SET DEFAULT ''
;

ALTER TABLE `fcs_image`
  ALTER `id_product` SET DEFAULT '0'
;

ALTER TABLE `fcs_image_lang`
  ALTER `id_image` SET DEFAULT '0',
  ALTER `id_lang` SET DEFAULT '0'
;

ALTER TABLE `fcs_image_shop`
  ALTER `id_image` SET DEFAULT '0',
  ALTER `id_shop` SET DEFAULT '0',
  ALTER `id_product` SET DEFAULT '0'
;

ALTER TABLE `fcs_manufacturer`
  ALTER `name` SET DEFAULT '',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `holiday_from` DATE NULL DEFAULT NULL,
  MODIFY `holiday_to` DATE NULL DEFAULT NULL,
  ALTER `is_private` SET DEFAULT '0',
  ALTER `uid_number` SET DEFAULT '',
  ALTER `iban` SET DEFAULT '',
  ALTER `bic` SET DEFAULT '',
  ALTER `bank_name` SET DEFAULT '',
  ALTER `firmenbuchnummer` SET DEFAULT '',
  ALTER `firmengericht` SET DEFAULT '',
  ALTER `aufsichtsbehoerde` SET DEFAULT '',
  ALTER `kammer` SET DEFAULT '',
  ALTER `homepage` SET DEFAULT ''
;
UPDATE `fcs_manufacturer` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_manufacturer` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';
UPDATE `fcs_manufacturer` SET `holiday_from` = DEFAULT WHERE `holiday_from` = '0000-00-00';
UPDATE `fcs_manufacturer` SET `holiday_to` = DEFAULT WHERE `holiday_to` = '0000-00-00';

ALTER TABLE `fcs_manufacturer_lang`
  ALTER `id_manufacturer` SET DEFAULT '0',
  ALTER `id_lang` SET DEFAULT '0'
;

ALTER TABLE `fcs_order_detail`
  ALTER `id_order` SET DEFAULT '0',
  ALTER `id_shop` SET DEFAULT '0',
  ALTER `product_id` SET DEFAULT '0',
  ALTER `product_name` SET DEFAULT '',
  ALTER `deposit` SET DEFAULT '0.00'
;

ALTER TABLE `fcs_order_detail_tax`
  ALTER `id_order_detail` SET DEFAULT '0'
;

ALTER TABLE `fcs_orders`
  ALTER `id_customer` SET DEFAULT '0',
  ALTER `id_cake_cart` SET DEFAULT '0',
  ALTER `current_state` SET DEFAULT '0',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  ALTER `total_deposit` SET DEFAULT '0.00',
  ALTER `general_terms_and_conditions_accepted` SET DEFAULT '0',
  ALTER `cancellation_terms_accepted` SET DEFAULT '0'
;
UPDATE `fcs_orders` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_orders` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_product`
  ALTER `id_tax_rules_group` SET DEFAULT '0',
  ALTER `id_tax` SET DEFAULT '0',
  MODIFY `available_date` DATE NOT NULL DEFAULT '1000-01-01',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_product` SET `available_date` = DEFAULT WHERE `available_date` = '0000-00-00';
UPDATE `fcs_product` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_product` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_product_attribute`
  ALTER `id_product` SET DEFAULT '0',
  MODIFY `available_date` DATE NOT NULL DEFAULT '1000-01-01'
;
UPDATE `fcs_product_attribute` SET `available_date` = DEFAULT WHERE `available_date` = '0000-00-00';

ALTER TABLE `fcs_product_attribute_combination`
  ALTER `id_attribute` SET DEFAULT '0',
  ALTER `id_product_attribute` SET DEFAULT '0'
;

ALTER TABLE `fcs_product_attribute_shop`
  ALTER `id_product_attribute` SET DEFAULT '0',
  ALTER `id_shop` SET DEFAULT '0',
  ALTER `id_product` SET DEFAULT '0',
  MODIFY `available_date` DATE NOT NULL DEFAULT '1000-01-01'
;
UPDATE `fcs_product_attribute_shop` SET `available_date` = DEFAULT WHERE `available_date` = '0000-00-00';

ALTER TABLE `fcs_product_lang`
  ALTER `id_product` SET DEFAULT '0',
  ALTER `id_lang` SET DEFAULT '0',
  ALTER `link_rewrite` SET DEFAULT '',
  ALTER `name` SET DEFAULT ''
;

ALTER TABLE `fcs_product_shop`
  ALTER `id_product` SET DEFAULT '0',
  ALTER `id_shop` SET DEFAULT '0',
  ALTER `id_tax_rules_group` SET DEFAULT '0',
  MODIFY `available_date` DATE NOT NULL DEFAULT '1000-01-01',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_product_shop` SET `available_date` = DEFAULT WHERE `available_date` = '0000-00-00';
UPDATE `fcs_product_shop` SET `date_add` = DEFAULT WHERE `date_add` = '0000-00-00 00:00:00';
UPDATE `fcs_product_shop` SET `date_upd` = DEFAULT WHERE `date_upd` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_smart_blog_post`
  ALTER `id_customer` SET DEFAULT '0',
  ALTER `id_manufacturer` SET DEFAULT '0',
  ALTER `is_private` SET DEFAULT '0',
  MODIFY `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;
UPDATE `fcs_smart_blog_post` SET `created` = DEFAULT WHERE `created` = '0000-00-00 00:00:00';
UPDATE `fcs_smart_blog_post` SET `modified` = DEFAULT WHERE `modified` = '0000-00-00 00:00:00';

ALTER TABLE `fcs_smart_blog_post_lang`
  ALTER `id_smart_blog_post` SET DEFAULT '0'
;

ALTER TABLE `fcs_smart_blog_post_shop`
  ALTER `id_smart_blog_post` SET DEFAULT '0',
  ALTER `id_shop` SET DEFAULT '0'
;

ALTER TABLE `fcs_stock_available`
  ALTER `id_product` SET DEFAULT '0',
  ALTER `id_product_attribute` SET DEFAULT '0',
  ALTER `id_shop` SET DEFAULT '0',
  ALTER `id_shop_group` SET DEFAULT '0'
;

ALTER TABLE `fcs_tax`
  ALTER `rate` SET DEFAULT '0.000'
;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

