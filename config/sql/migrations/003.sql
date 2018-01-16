ALTER TABLE `fcs_manufacturer` ADD `holiday_from` DATE NOT NULL AFTER `holiday`, ADD `holiday_to` DATE NOT NULL AFTER `holiday_from`;
UPDATE `fcs_manufacturer` SET `holiday_from` = IF(`holiday` = 1, NOW(), null);
ALTER TABLE `fcs_manufacturer` DROP `holiday`;