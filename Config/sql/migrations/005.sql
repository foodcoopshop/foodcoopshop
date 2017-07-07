CREATE TABLE `fcs_email_logs` (
  `id` int(11) NOT NULL,
  `from_address` text NULL,
  `to_address` text NULL,
  `cc_address` text NULL,
  `bcc_address` text NULL,
  `subject` text NULL,
  `headers` text NULL,
  `message` text NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `fcs_email_logs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `fcs_email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_EMAIL_LOG_ENABLED', 'Sollen alle ausgehenden E-Mails in der Datenbank gespeichert werden?<br /><div class="small">Für Unit Tests und Debugging gedacht.</div>', '0', 'readonly', '30', '2017-07-05 00:00:00', '2017-07-05 00:00:00');

UPDATE `fcs_configuration` SET `position` = '10' WHERE `fcs_configuration`.`name` = 'FCS_DB_VERSION';
UPDATE `fcs_configuration` SET `position` = '20' WHERE `fcs_configuration`.`name` = 'FCS_DB_UPDATE';