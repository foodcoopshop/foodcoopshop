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
				<b>Hersteller-Login</b><br /> <a
					href="<?php echo Configure::read('app.cakeServerName'); ?>" /admin><?php echo Configure::read('app.cakeServerName'); ?>/admin</a>
			</p>

			<p>
				<b>Das Bearbeiten deines Hersteller-Profils ist verbessert!</b><br />
				Logo-Upload, Urlaubsmodus, Bank- und Rechnungsdaten...<br /> <a
					href="<?php echo Configure::read('app.cakeServerName'); ?><?php echo $this->Slug->getManufacturerProfile(); ?>"><?php echo Configure::read('app.cakeServerName'); ?><?php echo $this->Slug->getManufacturerProfile(); ?></a>
			</p>

			<p>
				Du kannst auch Blog-Artikel selbst erstellen.<br /> <a
					href="<?php echo Configure::read('app.cakeServerName'); ?><?php echo $this->Slug->getBlogPostListAdmin(); ?>"><?php echo Configure::read('app.cakeServerName'); ?><?php echo $this->Slug->getBlogPostListAdmin(); ?></a>
				<br />
				<br />Deine Blog-Artikel findest du dann auf deiner Hersteller-Seite
				unterhalb deiner Beschreibung und auf dieser Seite:<br /> <a
					href="<?php echo Configure::read('app.cakeServerName'); ?><?php echo $this->Slug->getManufacturerBlogList($manufacturer['Manufacturer']['id_manufacturer'], $manufacturer['Manufacturer']['name']); ?>"><?php echo Configure::read('app.cakeServerName'); ?><?php echo $this->Slug->getManufacturerBlogList($manufacturer['Manufacturer']['id_manufacturer'], $manufacturer['Manufacturer']['name']); ?></a>
			</p>


                <?php if (!Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS')) { ?> 
                    <p>
				Deine Produkte sind nur für Mitglieder sichtbar. Um die Darstellung
				deiner Produkte im Shop zu überprüfen, melde dich bitte hier rechts
				oben unter "Anmelden" an:<br />
                        <?php echo Configure::read('app.cakeServerName'); ?>
                    </p>
                <?php } ?>
                
                <ul style="padding-left: 10px;">
				<li><b>Neue Produkte</b> und Varianten (z.B. Artikel mit 0,5 kg und
					1 kg) <b>erstellen</b></li>
				<li>Ändern der Produkt-Daten (Preis, Beschreibung, Pfand, Lagerstand
					usw.)</li>
				<li>Das <b>Ändern des Preises</b> und das <b>Stornieren</b> von
					bereits bestellten Produkten
				</li>
				<li>Änderung von Anzahl, Preis, Beschreibung und Kategorien deiner
					Produkte</li>
				<li>Hochladen von Bildern</li>
				<li>Aktivieren bzw. Deaktivieren deiner Produkte</li>
				<li>Änderung deiner Hersteller-Beschreibung</li>
				<li>Produkte als "neu" markieren</li>
				<li>Änderung deines Passwortes</li>
			</ul>

			<p>
				Bitte verwende zum Einloggen die E-Mail-Adresse dieser Nachricht.<br />
				Falls du bei uns auch bestellen möchtest, registriere dich bitte auf
				unserer Seite und verwende dazu aber eine andere E-Mail-Adresse.<br />
				Bei Fragen antworte bitte einfach auf diese E-Mail.
			</p>

		</td>

	</tr>

</tbody>
</table>
