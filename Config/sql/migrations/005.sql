CREATE TABLE `fcs_email_logs` (
  `id` int(11) NOT NULL,
  `from_address` text NOT NULL,
  `to_address` text NOT NULL,
  `cc_address` text NOT NULL,
  `bcc_address` text NOT NULL,
  `subject` text NOT NULL,
  `headers` text NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `fcs_email_logs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `fcs_email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;