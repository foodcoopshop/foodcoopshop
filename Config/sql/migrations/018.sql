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
