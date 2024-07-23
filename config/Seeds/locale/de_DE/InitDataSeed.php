<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitDataSeed extends AbstractSeed
{
    public function run(): void
    {
        $query = "
INSERT INTO `fcs_configuration` VALUES
(11,1,'FCS_PRODUCT_AVAILABILITY_LOW','Geringe Verfügbarkeit<br /><div class=\"small\">Ab welcher verfügbaren Produkt-Menge soll beim Bestellen der Hinweis \"(x verfügbar\") angezeigt werden?</div>','10','number',600,'de_DE','2017-07-26 13:24:47','2014-06-01 01:40:34'),
(31,1,'FCS_DAYS_SHOW_PRODUCT_AS_NEW','Wie viele Tage sollen Produkte \"als neu markiert\" bleiben?','7','number',700,'de_DE','2017-07-26 13:24:47','2014-05-14 21:15:45'),
(456,1,'FCS_FOOTER_CMS_TEXT','Zusätzlicher Text für den Footer',NULL,'textarea_big',920,'de_DE','2014-06-11 17:50:55','2016-07-01 21:47:47'),
(508,1,'FCS_FACEBOOK_URL','Facebook-Url für die Einbindung im Footer','https://www.facebook.com/FoodCoopShop/','text',910,'de_DE','2015-07-08 13:23:54','2015-07-08 13:23:54'),
(538,1,'FCS_REGISTRATION_EMAIL_TEXT','Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><i class=\"fas fa-info-circle\"></i> E-Mail-Vorschau anzeigen</a>','','textarea_big',1700,'de_DE','2016-06-26 00:00:00','2016-06-26 00:00:00'),
(543,1,'FCS_RIGHT_INFO_BOX_HTML','Inhalt der Box in der rechten Spalte unterhalb des Warenkorbes. <br /><div class=\"small\">Um eine Zeile grün zu hinterlegen (Überschrift) bitte als \"Überschrift 3\" formatieren.</div>','<h3>Abholzeiten</h3>\r\n\r\n<p>Du kannst jede Woche bis sp&auml;testens Dienstag Mitternacht bestellen und die Produkte am Freitag abholen.</p>\r\n','textarea_big',1500,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(544,1,'FCS_NO_DELIVERY_DAYS_GLOBAL','Lieferpause für alle Hersteller?<br /><div class=\"small\">Hier können lieferfreie Tage (z.B. Feiertage) für die gesamte Foodcoop festgelegt werden.</div>','','multiple_dropdown',100,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(545,1,'FCS_ACCOUNTING_EMAIL','E-Mail-Adresse des Finanzverantwortlichen<br /><div class=\"small\">Wer bekommt die Benachrichtigung über den erfolgten Rechnungsversand?</div>','','text',1100,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(546,1,'FCS_REGISTRATION_INFO_TEXT','Info-Text beim Registrierungsformular<br /><div class=\"small\">Beim Registrierungsformlar wird unterhalb der E-Mail-Adresse dieser Text angezeigt.</div>','Um bei uns zu bestellen musst du Vereinsmitglied sein.','textarea_big',1600,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(547,1,'FCS_SHOW_PRODUCTS_FOR_GUESTS','Produkte für nicht eingeloggte Mitglieder sichtbar?','0','boolean',200,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(548,1,'FCS_DEFAULT_NEW_MEMBER_ACTIVE','Neue Mitglieder automatisch aktivieren?','0','boolean',500,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(549,1,'FCS_MINIMAL_CREDIT_BALANCE','Bis zu welchem Guthaben-Betrag sollen Bestellungen möglich sein?','0','number',1250,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(550,1,'FCS_BANK_ACCOUNT_DATA','Bankverbindung für die Guthaben-Einzahlungen\".','Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878','text',1300,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(552,1,'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS','Zusätzliche Liefer-Informationen für die Hersteller<br /><div class=\"small\">wird in den Bestell-Listen nach dem Lieferdatum angezeigt.</div>',', 15:00 bis 17:00 Uhr','text',1200,'de_DE','2017-07-26 13:24:47','2017-07-26 13:24:47'),
(553,1,'FCS_BACKUP_EMAIL_ADDRESS_BCC','E-Mail-Adresse, an die sämtliche vom System generierten E-Mails als BCC verschickt werden (Backup).<br /><div class=\"small\">Kann leer gelassen werden.</div>','','text',1900,'de_DE','2016-10-06 00:00:00','2016-10-06 00:00:00'),
(554,1,'FCS_SHOW_FOODCOOPSHOP_BACKLINK','Auf Homepage Link auf www.foodcoopshop.com anzeigen?<br /><div class=\"small\">Der Link wird im Footer angezeigt.</div>','1','boolean',930,'de_DE','2016-11-27 00:00:00','2016-11-27 00:00:00'),
(556,1,'FCS_APP_NAME','Name der Foodcoop','','text',50,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(557,1,'FCS_APP_ADDRESS','Adresse der Foodcoop<br /><div class=\"small\">Wird im Footer von Homepage und E-Mails, Datenschutzerklärung, Nutzungsbedingungen usw. verwendet.</div>','','textarea',60,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(558,1,'FCS_APP_EMAIL','E-Mail-Adresse der Foodcoop<br /><div class=\"small\"></div>','','text',900,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(559,1,'FCS_PLATFORM_OWNER','Betreiber der Plattform<br /><div class=\"small\">Für Datenschutzerklärung und Nutzungsbedingungen, bitte auch Adresse angeben. Kann leer gelassen werden, wenn die Foodcoop selbst die Plattform betreibt.</div>','','textarea',90,'de_DE','2017-01-12 00:00:00','2017-01-12 00:00:00'),
(564,1,'FCS_ORDER_COMMENT_ENABLED','Kommentarfeld bei Bestell-Abschluss anzeigen?<br /><div class=\"small\">Wird im Admin-Bereich unter \"Bestellungen\" angezeigt.</div>','1','boolean',130,'de_DE','2017-07-09 00:00:00','2017-07-09 00:00:00'),
(565,1,'FCS_USE_VARIABLE_MEMBER_FEE','Variablen Mitgliedsbeitrag verwenden?<br /><div class=\"small\">Den variablen Mitgliedsbeitrag bei den Hersteller-Rechnungen abziehen? Die Produkt-Preise müssen entsprechend höher eingegeben werden.</div>','0','readonly',400,'de_DE','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(566,1,'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE','Standardwert für variablen Mitgliedsbeitrag<br /><div class=\"small\">Der Prozentsatz kann in den Hersteller-Einstellungen auch individuell angepasst werden.</div>','0','readonly',500,'de_DE','2017-08-02 00:00:00','2017-08-02 00:00:00'),
(567,1,'FCS_NETWORK_PLUGIN_ENABLED','Netzwerk-Modul aktiviert?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/netzwerk-modul\" target=\"_blank\">Infos zum Netzwerk-Modul</a></div>','0','readonly',500,'de_DE','2017-09-14 00:00:00','2017-09-14 00:00:00'),
(574,1,'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS','Produktpreis für nicht eingeloggte Mitglieder anzeigen?','0','boolean',210,'de_DE','2018-05-28 18:05:54','2018-05-28 18:05:54'),
(575,1,'FCS_CURRENCY_SYMBOL','Währungssymbol','€','readonly',520,'de_DE','2018-06-13 19:53:14','2018-06-13 19:53:14'),
(576,1,'FCS_DEFAULT_LOCALE','Sprache','de_DE','readonly',550,'de_DE','2018-06-26 10:18:55','2018-06-26 10:18:55'),
(577,1,'FCS_FOODCOOPS_MAP_ENABLED','Auf Home Karte mit anderen Foodcoops anzeigen?','1','boolean',1280,'de_DE','2019-02-11 22:25:36','2019-02-11 22:25:36'),
(578,1,'FCS_WEEKLY_PICKUP_DAY','Wöchentlicher Abholtag','5','readonly',600,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(579,1,'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA','Bestelllisten-Versand: x Tage vor dem Abholtag','2','readonly',650,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(580,1,'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM','Sollen Lagerprodukte mit der wöchentlichen Bestellung bestellt werden können?','1','boolean',750,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(581,1,'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS','In der Sofort-Bestellung ausschließlich Lagerprodukte anzeigen?','0','boolean',760,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(582,1,'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES','Lagerprodukte in Rechnungen miteinbeziehen?','1','readonly',600,'de_DE','2019-02-18 12:38:00','2019-02-18 12:38:00'),
(583,1,'FCS_REGISTRATION_NOTIFICATION_EMAILS','Wer soll bei neuen Registrierungen informiert werden?<br /><div class=\"small\">Mehrere E-Mail-Adressen mit , (ohne Leerzeichen) trennen.</div>','','text',550,'de_DE','2019-03-05 20:01:59','2019-03-05 20:01:59'),
(584,1,'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED','Selbstbedienungs-Modus für Lagerprodukte aktiv?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/selbstbedienungs-modus\" target=\"_blank\">Zur Online-Doku</a></div>','0','boolean',3000,'de_DE','2019-04-17 20:01:59','2019-04-17 20:01:59'),
(585,1,'FCS_APP_ADDITIONAL_DATA','Zusätzliche Infos zur Foodcoop<br /><div class=\"small\">Z.B. ZVR-Zahl</div>','','textarea',80,'de_DE','2019-08-03 20:07:04','2019-08-03 20:07:04'),
(586,1,'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED','Selbstbedienungs-Modus im Test-Modus ausführen?<br /><div class=\"small\">Keine Verlinkung im Haupt-Menü und bei Lagerprodukten.</div>','0','boolean',3100,'de_DE','2019-12-09 13:46:27','2019-12-09 13:46:27'),
(587,1,'FCS_CASHLESS_PAYMENT_ADD_TYPE','Art der Eintragung der Guthaben-Aufladungen<br /><div class=\"small\">Wie gelangen die Guthaben-Aufladungen vom Bankkonto in den FoodCoopShop?</div>','list-upload','dropdown',1450,'de_DE','2020-02-11 10:12:57','2020-02-11 10:12:57'),
(589,1,'FCS_FEEDBACK_TO_PRODUCTS_ENABLED','Feedback-Funktion für Produkte aktiviert?<br /><div class=\"small\">Mitglieder können Feedback zu bestellten Produkte verfassen.</div>','0','boolean',3200,'de_DE','2020-06-19 09:02:46','2020-06-19 09:02:46'),
(590,1,'FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY','Mitglied kann Abholtag beim Bestellen selbst auswählen.','0','readonly',590,'de_DE','2020-07-06 10:34:35','2020-07-06 10:34:35'),
(591,1,'FCS_SEND_INVOICES_TO_CUSTOMERS','Einzelhandels-Modus aktiviert?<br /><div class=\"small\"><a href=\"https://foodcoopshop.github.io/de/dorfladen-online\" target=\"_blank\">Infos zur Verwendung im Einzelhandel</a></div>','0','readonly',580,'de_DE','2020-10-29 10:06:34','2020-10-29 10:06:34'),
(592,1,'FCS_DEPOSIT_TAX_RATE','Umsatzsteuersatz für Pfand','20,00','readonly',581,'de_DE','2020-11-03 15:23:55','2020-11-03 15:23:55'),
(593,1,'FCS_INVOICE_HEADER_TEXT','Header-Text für Rechnungen an Mitglieder','','readonly',582,'de_DE','2020-11-03 15:23:55','2020-11-03 15:23:55'),
(594,1,'FCS_MEMBER_FEE_PRODUCTS','Welche Produkte werden als Mitgliedsbeitrag verwendet?<div class=\"small\">Die ausgewählten Produkte sind Datengrundlage der Spalte Mitgliedsbeitrag in der Mitgliederverwaltung und werden nicht in der Umsatzstatistik angezeigt.</div>','','multiple_dropdown',3300,'de_DE','2020-12-20 19:26:10','2020-12-20 19:26:10'),
(595,1,'FCS_CHECK_CREDIT_BALANCE_LIMIT','Ab welchem Guthaben-Stand soll die Erinnerungsmail versendet werden?','50','number',1450,'de_DE','2021-01-19 11:23:34','2021-01-19 11:23:34'),
(596,1,'FCS_PURCHASE_PRICE_ENABLED','Einkaufspreis für Produkte erfassen?<div class=\"small\">Der Einkaufspreis ist die Datengrundlage für die Gewinn-Statistik und für Lieferscheine an die Hersteller.</div>','0','readonly',587,'de_DE','2021-05-10 11:27:38','2021-05-10 11:27:38'),
(597,1,'FCS_HELLO_CASH_API_ENABLED','Schnittstelle (API) zu Registrierkasse HelloCash (hellocash.at) aktivieren?<div class=\"small\">Alle Rechnungen (bar und unbar) über die Registrierkasse erstellen.</div>','0','readonly',583,'de_DE','2021-07-07 10:55:03','2021-07-07 10:55:03'),
(598,1,'FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS','Lagerort für Produkte erfassen und in Bestelllisten anzeigen?<div class=\"small\">Lagerorte: Keine Kühlung / Kühlschrank / Tiefkühler. Es erscheint ein zusätzlicher Button neben \"Bestellungen - Bestellungen als PDF generieren\"</div>','1','boolean',3210,'de_DE','2021-08-02 11:28:29','2021-08-02 11:28:29'),
(599,1,'FCS_INSTAGRAM_URL','Instagram-Url für die Einbindung im Footer','','text',920,'de_DE','2021-09-10 21:23:08','2021-09-10 21:23:08'),
(600,1,'FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY','Bestellungen beim ein- und zweiwöchigen Lieferhythmus sind nur in der Woche vor der Lieferung möglich.','0','boolean',3210,'de_DE','2022-02-01 17:48:35','2022-02-01 17:48:35'),
(601,1,'FCS_INVOICE_NUMBER_PREFIX','Präfix für Rechnungs-Nummernkreis<br /><div class=\"small\">Max. 6 Zeichen inkl. Trennzeichen.</div>','','readonly',586,'de_DE','2022-03-21 12:02:48','2022-03-21 12:02:48'),
(602,1,'FCS_TAX_BASED_ON_NET_INVOICE_SUM','Rechnungslegung für pauschalierte Betriebe<br /><div class=\"small\">Die Berechnung der Umsatzsteuer erfolgt auf Basis der Netto-Rechnungsumme und ist <b>nicht</b> die Summe der Umsatzsteuerbeträge pro Stück.</div>','0','readonly',585,'de_DE','2022-03-23 09:12:23','2022-03-23 09:12:23'),
(603,1,'FCS_NEWSLETTER_ENABLED','Newsletter-Funktion aktiv?<br /><div class=\"small\">Mitglieder können sich bei der Registrierung für den Newsletter anmelden. <a href=\"https://foodcoopshop.github.io/de/mitglieder.html#newsletter-funktion\" target=\"_blank\">Mehr Infos</a></div>','0','boolean',3400,'de_DE','2022-04-12 15:28:47','2022-04-12 15:28:47'),
(604,1,'FCS_USER_FEEDBACK_ENABLED','Mitglieder- und Hersteller-Feedback aktiv?<br /><div class=\"small\">Ermöglicht das Erfassen und Anzeigen von Feedback. <a href=\"https://foodcoopshop.github.io/de/user-feedback.html\" target=\"_blank\">Mehr Infos</a></div>','0','boolean',3500,'de_DE','2022-07-19 14:39:27','2022-07-19 14:39:27');
";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_storage_locations` VALUES
            (1,'Keine Kühlung',10),
            (2,'Kühlschrank',20),
            (3,'Tiefkühler',30);
        ";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_category` VALUES
            (20,2,'Alle Produkte','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
        ";
        $this->execute($query);

    }
}
