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
?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

	<tr>
		<td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
				Hallo <?php echo $manufacturer['Address']['firstname']; ?>,
			</td>
	</tr>

	<tr>
		<td>

			<p>im Anhang findest du zwei Bestelllisten (gruppiert nach Artikel
				und Mitglied) für die nächste Lieferung.</p>

			<p>
				<b>Dein persönlicher Hersteller-Bereich:</b><br /> <a
					href="<?php echo Configure::read('app.cakeServerName'); ?>/admin"><?php echo Configure::read('app.cakeServerName'); ?>/admin</a>
			</p>

            <ul style="padding-left: 10px;">
				<li><b>Neue Produkte erstellen</b> (inkl. Varianten - z.B. Artikel mit 0,5 kg und 1 kg)</li>
				<li><b>Ändern des Preises</b> und <b>Stornieren</b> von	bereits bestellten Produkten</li>
				<li>Änderung von Anzahl, Preis, Beschreibung, Lagerstand, Pfand und Kategorien deiner
					Produkte</li>
				<li>Hochladen von Bildern</li>
				<li>Aktivieren bzw. Deaktivieren deiner Produkte</li>
				<li>Änderung deines Hersteller-Profils</li>
				<li>Produkte als "neu" markieren</li>
				<li>Pfandkonto (falls du Pfand verwendest)</li>
				<li>Änderung deines Passwortes</li>
			</ul>
			
            <?php if (!Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS')) { ?> 
            <p>
				Deine Produkte sind nur für Mitglieder sichtbar. Um die Darstellung
				deiner Produkte zu überprüfen, musst du angemeldet sein.
            </p>
            <?php } ?>

			<p>
				Bitte verwende zum Einloggen die E-Mail-Adresse dieser Nachricht. Falls du bei uns auch bestellen möchtest, registriere dich bitte auf
				unserer Seite und verwende dazu aber eine andere E-Mail-Adresse.
			</p>

		</td>

	</tr>

</tbody>
</table>
