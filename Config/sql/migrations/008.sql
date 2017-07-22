-- Prepare for MySQL 5.7
-- More strict default values require "NOT NULL" columns to always be
-- given on INSERT or have default values defined. Before 5.7 not giving
-- values for "NOT NULL" columns resulted in automatic default values
-- being inserted (zero-ish values). Instead of scanning source code
-- and adding missing INSERT values it's easier to set zero-ish default
-- values to the columns (except DATETIME/DATE where no zero-ish value is
-- allowed by default, DATETIME defaults to CURRENT_TIMESTAMP, DATE to the
-- date literal of the day before default was added).
--
-- id_* columns still are protected by foreign keys against DB corruption.

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

ALTER TABLE `fcs_attribute`
  ALTER `id_attribute_group` SET DEFAULT '0'
;

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

ALTER TABLE `fcs_cake_cart_products`
  ALTER `id_cart` SET DEFAULT '0',
  ALTER `id_product` SET DEFAULT '0',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;

ALTER TABLE `fcs_cake_carts`
  ALTER `id_customer` SET DEFAULT '0',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;

ALTER TABLE `fcs_cake_deposits`
  ALTER `deposit` SET DEFAULT '0.0'
;

ALTER TABLE `fcs_cake_invoices`
  ALTER `id_manufacturer` SET DEFAULT '0',
  ALTER `invoice_number` SET DEFAULT '0',
  MODIFY `send_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  ALTER `user_id` SET DEFAULT '0'
;

ALTER TABLE `fcs_cake_payments`
  ALTER `amount` SET DEFAULT '0.00',
  ALTER `text` SET DEFAULT '',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_changed` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  ALTER `approval` SET DEFAULT '0',
  ALTER `changed_by` SET DEFAULT '0',
  ALTER `created_by` SET DEFAULT '0'
;

ALTER TABLE `fcs_category`
  ALTER `id_parent` SET DEFAULT '0',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;

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

ALTER TABLE `fcs_customer`
  ALTER `id_gender` SET DEFAULT '0',
  ALTER `firstname` SET DEFAULT '',
  ALTER `lastname` SET DEFAULT '',
  ALTER `email` SET DEFAULT '',
  ALTER `passwd` SET DEFAULT '',
  MODIFY `terms_of_use_accepted_date` DATE NOT NULL DEFAULT '2017-07-21',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;

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
  MODIFY `holiday_from` DATE NOT NULL DEFAULT '2017-07-21',
  MODIFY `holiday_to` DATE NOT NULL DEFAULT '2017-07-21',
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

ALTER TABLE `fcs_product`
  ALTER `id_tax_rules_group` SET DEFAULT '0',
  ALTER `id_tax` SET DEFAULT '0',
  MODIFY `available_date` DATE NOT NULL DEFAULT '2017-07-21',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;

ALTER TABLE `fcs_product_attribute`
  ALTER `id_product` SET DEFAULT '0',
  MODIFY `available_date` DATE NOT NULL DEFAULT '2017-07-21'
;

ALTER TABLE `fcs_product_attribute_combination`
  ALTER `id_attribute` SET DEFAULT '0',
  ALTER `id_product_attribute` SET DEFAULT '0'
;

ALTER TABLE `fcs_product_attribute_shop`
  ALTER `id_product_attribute` SET DEFAULT '0',
  ALTER `id_shop` SET DEFAULT '0',
  ALTER `id_product` SET DEFAULT '0',
  MODIFY `available_date` DATE NOT NULL DEFAULT '2017-07-21'
;

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
  MODIFY `available_date` DATE NOT NULL DEFAULT '2017-07-21',
  MODIFY `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  MODIFY `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;

ALTER TABLE `fcs_smart_blog_post`
  ALTER `id_customer` SET DEFAULT '0',
  ALTER `id_manufacturer` SET DEFAULT '0',
  ALTER `is_private` SET DEFAULT '0',
  MODIFY `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()
;

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

