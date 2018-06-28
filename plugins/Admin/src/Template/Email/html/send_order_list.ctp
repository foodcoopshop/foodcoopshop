<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <tr>
        <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                Hallo <?php echo $manufacturer->address_manufacturer->firstname; ?>,
            </td>
    </tr>

    <tr>
        <td>

            <p>im Anhang findest du zwei Bestelllisten (gruppiert nach Produkt und Mitglied) für die nächste Lieferung.</p>

            <p>
                <b>Dein persönlicher Hersteller-Bereich: </b> <a href="<?php echo Configure::read('app.cakeServerName'); ?>/admin"><?php echo Configure::read('app.cakeServerName'); ?>/admin</a>
            </p>

            <ul style="padding-left: 10px;">
                <li>Bearbeiten deines Hersteller-Profils (Logo, Beschreibung)</li>
                <li>Neue Produkte erstellen (inkl. Varianten - z.B. Produkt mit 0,5 kg und 1 kg)</li>
                <li>Anzahl, Preis, Beschreibung, Lagerstand, Pfand und Kategorien deiner Produkte ändern</li>
                <li>Hochladen von Produkt-Fotos</li>
                <li>Aktivieren bzw. Deaktivieren deiner Produkte</li>
                <li>Produkte als neu markieren</li>
                <li>Anpassen des Preises, des Gewichts und Stornieren von bereits bestellten Produkten</li>
                <li>Pfandkonto (falls du Pfand verwendest)</li>
                <li>Lieferpause: Zeitraum angeben und deine Produkte werden automatisch angezeigt bzw. deaktiviert</li>
                <li>Benachrichtigungs-E-Mails und weitere Einstellungen ändern</li>
                <li>Passwort ändern</li>
            </ul>
            
            <p>
                Die Daten für dein <b>Impressum</b> (rechts unten auf deinem Hersteller-Profil) kannst du selbst ändern.
            </p>

            <?php if (!Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) { ?> 
            <p>
                Deine Produkte sind nur für Mitglieder sichtbar. Um die Darstellung
                deiner Produkte zu überprüfen, musst du angemeldet sein.
            </p>
            <?php } ?>

            <p>
                Bitte verwende zum Einloggen die E-Mail-Adresse dieser Nachricht. Falls du bei uns auch bestellen möchtest, registriere dich bitte auf
                unserer Seite und verwende dazu aber eine andere E-Mail-Adresse.
            </p>
            
            <?php if (!empty($manufacturer->customer)) { ?>
                <p><b>Deine Ansprechperson: </b><?php echo $manufacturer->customer->name . ', ' . $manufacturer->customer->email . ', ' . $manufacturer->customer->address_customer->phone_mobile; ?></p>
            <?php } ?>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
