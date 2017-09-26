UPDATE fcs_configuration SET name = 'FCS_BACKUP_EMAIL_ADDRESS_BCC' WHERE name = 'FCS_ORDER_CONFIRMATION_MAIL_BCC';
UPDATE fcs_configuration SET text = 'E-Mail-Adresse, an die sämtliche vom System generierten E-Mails als BCC verschickt werden (Backup).<br /><div class="small">Kann leer gelassen werden.</div>' WHERE name = 'FCS_BACKUP_EMAIL_ADDRESS_BCC';
