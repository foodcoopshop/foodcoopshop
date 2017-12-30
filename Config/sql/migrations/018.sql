DELETE FROM fcs_address WHERE deleted = 1;
DELETE FROM fcs_address WHERE active = 0;
DELETE FROM fcs_address WHERE email IS NULL;
ALTER TABLE `fcs_address` CHANGE `other` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `fcs_address`
  DROP `id_country`,
  DROP `id_state`,
  DROP `id_supplier`,
  DROP `id_warehouse`,
  DROP `company`,
  DROP `alias`,
  DROP `vat_number`,
  DROP `dni`,
  DROP `active`,
  DROP `deleted`;

ALTER TABLE `fcs_attribute`
  DROP `id_attribute_group`,
  DROP `color`,
  DROP `position`;
ALTER TABLE `fcs_attribute` ADD `name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `id_attribute`;
UPDATE fcs_attribute a JOIN fcs_attribute_lang al ON al.id_attribute = a.id_attribute SET a.name = al.name;
DROP TABLE fcs_attribute_lang;

RENAME TABLE `fcs_cake_action_logs` TO `fcs_action_logs`;
RENAME TABLE `fcs_cake_carts` TO `fcs_carts`;
RENAME TABLE `fcs_cake_cart_products` TO `fcs_cart_products`;
RENAME TABLE `fcs_cake_deposits` TO `fcs_deposits`;
RENAME TABLE `fcs_cake_invoices` TO `fcs_invoices`;
RENAME TABLE `fcs_cake_payments` TO `fcs_payments`;

ALTER TABLE `fcs_orders` CHANGE `id_cake_cart` `id_cart` INT(10) NOT NULL DEFAULT '0';

ALTER TABLE `fcs_category` DROP `id_shop_default`, DROP `position`, DROP `is_root_category`;
ALTER TABLE `fcs_category` ADD `name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `id_parent`, ADD `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name`;
UPDATE fcs_category c JOIN fcs_category_lang cl ON cl.id_category = c.id_category SET c.name = cl.name, c.description = cl.description;
DROP TABLE fcs_category_lang;

ALTER TABLE `fcs_category_product` DROP `position`;

ALTER TABLE `fcs_cms` ADD `title` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `id_cms`, ADD `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `title`;
UPDATE fcs_cms c JOIN fcs_cms_lang cl ON cl.id_cms= c.id_cms SET c.title = cl.meta_title, c.content = cl.content;
ALTER TABLE `fcs_cms` CHANGE `id_cms` `id_page` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `fcs_cms` CHANGE `url` `extern_url` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `fcs_cms`
  DROP `id_cms_category`,
  DROP `indexation`;
RENAME TABLE `fcs_cms` TO `fcs_pages`;
DROP TABLE fcs_cms_lang;

ALTER TABLE `fcs_configuration` DROP `id_shop_group`, DROP `id_shop`;

ALTER TABLE `fcs_customer`
  DROP `id_shop_group`,
  DROP `id_shop`,
  DROP `id_gender`,
  DROP `id_lang`,
  DROP `id_risk`,
  DROP `siret`,
  DROP `ape`,
  DROP `birthday`,
  DROP `ip_registration_newsletter`,
  DROP `newsletter_date_add`,
  DROP `optin`,
  DROP `website`,
  DROP `outstanding_allow_amount`,
  DROP `show_public_prices`,
  DROP `max_payment_days`,
  DROP `secure_key`,
  DROP `note`,
  DROP `is_guest`,
  DROP `deleted`;

RENAME TABLE `fcs_homeslider_slides` TO `fcs_sliders`;
ALTER TABLE `fcs_sliders` CHANGE `id_homeslider_slides` `id_slider` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `fcs_sliders` ADD `image` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `id_slider`;
UPDATE fcs_sliders s JOIN fcs_homeslider_slides_lang hsl ON s.id_slider = hsl.id_homeslider_slides SET s.image = hsl.image;
DROP TABLE fcs_homeslider_slides_lang;

RENAME TABLE `fcs_image_shop` TO `fcs_images`;
DELETE FROM fcs_images WHERE cover = 0;
ALTER TABLE `fcs_images` DROP `id_shop`, DROP `cover`;
DROP TABLE fcs_image;
DROP TABLE fcs_image_lang;

ALTER TABLE `fcs_smart_blog_post` ADD `title` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `id_smart_blog_post`, 
ADD `short_description` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `title`,
ADD `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `short_description`;
UPDATE fcs_smart_blog_post sbp JOIN fcs_smart_blog_post_lang l ON sbp.id_smart_blog_post= l.id_smart_blog_post SET sbp.title = l.meta_title, sbp.content = l.content, sbp.short_description = l.short_description;
ALTER TABLE `fcs_smart_blog_post` CHANGE `id_smart_blog_post` `id_blog_post` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `fcs_smart_blog_post`
  DROP `id_author`,
  DROP `available`,
  DROP `viewed`,
  DROP `comment_status`,
  DROP `id_category`,
  DROP `post_type`,
  DROP `image`,
  DROP `position`;
RENAME TABLE `fcs_smart_blog_post` TO `fcs_blog_posts`;
DROP TABLE fcs_smart_blog_post_lang;
DROP TABLE fcs_smart_blog_post_shop;
